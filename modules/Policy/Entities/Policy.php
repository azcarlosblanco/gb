<?php namespace Modules\Policy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\Plan;
use Modules\Affiliate\Entities\AffiliatePolicyDeducible;
use Modules\Plan\Entities\PlanDeducibleType;
use Modules\Payment\Entities\PolicyCost;
use Carbon\Carbon;

class Policy extends Model
{
	protected $table='policy';

	//type determine if the agent is a 'agent' or a 'subagent'
	protected $fillable=[
                         'policy_number',
                         'plan_deducible_id', //de la tabla
                         'plan_type_id', //invividual,familiar,2 members
                         'payments_number_id',
                         'agente_id',
                         'emision_number',
                         'endoso_number',
                         'renewal_number',
                         //fecha en que la cobertura inicia
                         'start_date',
                         //fecha en que la cobertura termina
                         'end_date',
                         //fecha en que se hizo el endose
                         'endoso_date',
                         //fecha en que se crea la poliza en el sistema
                         'emision_date',
                         //1 personal, 2 empresarial
                         'ptype',
                         'customer_id',
                         'extend_prev_insurance' //1 yes, 0 no
                        ];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function planDeducible(){
        return $this->belongsTo('Modules\Plan\Entities\Deducible'
                                    ,'plan_deducible_id','id');
    }

    public function customer(){
        return $this->belongsTo(
                            'Modules\Customer\Entities\Customer',
                            'customer_id','id');
    }

	public function affiliates(){
        return $this->hasMany('Modules\Affiliate\Entities\AffiliatePolicy',
        				'policy_id')
        				->whereNull("dismiss_date");
    }

    public function affiliatesAll(){
        return $this->hasMany('Modules\Affiliate\Entities\AffiliatePolicy',
        				'policy_id');
    }

	public function deducibles(){
        return $this->hasMany('Modules\Policy\Entities\PolicyDeducible','policy_id');
    }

	public function planType(){
        return $this->belongsTo('Modules\Plan\Entities\PlanType','plan_type_id','id');
    }

    public function getPlan(){
        return Plan::find($this->planDeducible->plan_id);
    }

    public function numQuotes(){
    	return $this->belongsTo("Modules\Plan\Entities\NumberPayments","payments_number_id");
    }

    public function procedureEntry(){
        return $this->hasOne('App\ProcedureEntry','policy_id', 'id');
    }

    public function agente(){
        return $this->belongsTo('Modules\Agente\Entities\Agente','agente_id','id');
    }

    public function prevInsurance(){
    	return $this->hasOne("Modules\Policy\Entities\PrevInsurancePolicy",
    						            "policy_id");
    }

    public function policyCost(){
    	return $this->hasMany("Modules\Payment\Entities\PolicyCost",
    						            "policy_id");
    }

    public function ticket(){
      return $this->hasMany("Modules\ClientService\Entities\Ticket",
                            "policy_id", "id");
    }
    public function emergency(){
      return $this->hasMany('Modules\ClientService\Entities\Emergency',
                             'customer_policy_id','id');
    }

    public function hospitalization(){
      return $this->hasMany('Modules\ClientService\Entities\Hospitalization',
                             'policy_id','id');
    }

    public function discounts(){
      return $this->hasMany("Modules\Policy\Entities\PolicyDiscount",
                            "policy_id");
    }

    public function readableSummary(){
      $customer = $this->customer;

      $agente = $this->agente;
      $deducible = \Modules\Plan\Entities\Deducible::with('plan')
                  ->select('name','plan_id')
                  ->find($this->plan_deducible_id);

      $data['agente_email'] = trim($agente->email);
      $data['agente_name'] = $agente->fullname;

      $data['policy_number'] = $this->policy_number;
      $data['customer_name'] = $customer->fullname;
      $data['customer_email'] = $customer->email;
      $data['plan_name'] = $deducible->plan->name;
      $data['deducible'] = $deducible->name;

      return $data;
  	}

	public function getDeductiblesTotalsBD(){
		$totals = null;

		if( !isset($this->id) ){
			return $totals;
		}

		$aff_array = $this->affiliates;

		foreach( $aff_array as $ap ){
			$deducibles = $ap->deducibles;
			foreach( $deducibles as $apd ){
				if( !isset($totals[$apd->plan_deducible_type_id]) ){
					$totals[$apd->plan_deducible_type_id] = 0;
				}
				$totals[$apd->plan_deducible_type_id] += $apd->amount;
			}
		}

		return $totals;
	}

	public function recalculateAffiliatesDeductibles($pivot_settlement=0){
		$resp = false;
		$totals = array();

		try{
			//\DB::beginTransaction();

			if( !isset($this->id) ){
				throw new \Exception('Invalid Policy');
			}

			$aff_array = $this->affiliates;

			//reset all affiliate_policy_deducible values
			/*foreach( $aff_array as $ap ){
				$deducibles = $ap->deducibles;
				foreach( $deducibles as $apd ){
					$apd->amount = 0;
					$apd->save();
				}
			}*/

			//set some conf values
			$multiply = ( $this->planType->name == 'Individual' ) ? 1 : 2;

			//policy ref deducibles
			$ref_deducibles = $total_deduc = $single_deductibles = array();
			$policy_deducibles = $this->deducibles;
			foreach( $policy_deducibles as $pd ){
				$ref_deducibles[$pd->plan_deducible_type_id] = $multiply * $pd->amount;
				$single_deductibles[$pd->plan_deducible_type_id] = $pd->amount;
				$total_deduc[$pd->plan_deducible_type_id] = 0;
			}

			$local_type = PlanDeducibleType::where('name', 'local')->firstOrFail();
			$inter_type = PlanDeducibleType::where('name', 'usa')->firstOrFail();


      $settlements = array();

      //get all settlements together
			foreach( $aff_array as $ap ){
				$claims = $ap->claims;
				foreach( $claims as $claim ){
					$files = $claim->files;
					foreach( $files as $file ){
						$curr_deduct = 0;
						$settlement = $file->settlement;

						if( empty($settlement) ){
							continue;
						}

            $tmp_id = $settlement->id;
            /*if($tmp_id < $pivot_settlement){
              continue;
            }*/

            $settlements[$tmp_id.''] = array('sett'=>$settlement, 'ap'=>$ap, 'usa'=>$file->usa);
					}//end foreach files
				}//end foreach claims
			}//end foreach aff

      ksort($settlements);
      //iterate over settlements and calculate amounts
      foreach( $settlements as $i=>$s ){
        $settlement = $s['sett'];
        $ap = $s['ap'];
        $usa = $s['usa'];

        //calculate amounts
        $amount = $settlement->amount;
        $uncovered = $settlement->uncovered_value;
        $dscto = $settlement->descuento;
        $coaseguro = $settlement->coaseguro;
        $effective_amount = $amount - ($uncovered + $dscto + $coaseguro);

        //check claim file is local or inter
        if( $usa ){
          //based on inter deduct - we sum all deductibles
          foreach( $total_deduc as $key=>$val ){
            $curr_deduct += $val;
          }
          $res = $effective_amount + $curr_deduct - $ref_deducibles[$inter_type->id];
          $curr_diff = $ref_deducibles[$inter_type->id] - $curr_deduct;
          $pd_type = $inter_type->id;
        }
        else{
          //based on local deduct
          if( isset($total_deduc[$local_type->id]) ){
            $curr_deduct = $total_deduc[$local_type->id];
          }
          $res = $effective_amount + $curr_deduct - $ref_deducibles[$local_type->id];
          $curr_diff = $ref_deducibles[$local_type->id] - $curr_deduct;
          $pd_type = $local_type->id;
        }

        if($res <= 0){
          //deductible not complete or just completed, send all amount to affiliate deductible
          $to_deduct = $effective_amount;
          $to_refund = 0;
        }
        else{
          //deductible completed, let's split the amount
          $to_deduct = $curr_diff;
          $to_refund = $res;
        }

        //save expected values for settlement
        $settlement->expected_deduct = $to_deduct;
        $settlement->expected_refund = $to_refund;
        $settlement->save();

        if( $to_deduct > 0 ){
          $apd_obj = $ap->deducibles()->where('plan_deducible_type_id', $pd_type)->first();
          if( !empty($apd_obj) ){
            //update affiliate policy deductible
            $apd_obj->amount = $apd_obj->amount + $to_deduct;
            $apd_obj->save();
          }
          //update total array for our iteration calculations
          $total_deduc[$pd_type] += $to_deduct;
        }

      }//end foreach settlements

			//\DB::commit();
		}catch(\Exception $e){
			//\DB::rollback();
			throw $e;
		}
	}//end recalculateAffiliatesDeductibles

	public function getPolicyCosts(){
    $quotes = PolicyCost::with("policyCostDetails")
                            ->where("policy_id",$this->id)
                            ->where("emision_number",$this->emision_number)
                            ->where("renewal_number",$this->renewal_number)
                            ->orderBy("quote_number",'asc')
                            ->get();

    $policyCost = array();
    $total = 0;
    foreach ($quotes as $key => $quote) {
      $policyCost[$key] = array();
      $total += $quote["total"];
      $policyCost[$key]["quote_number"] = $quote["quote_number"];
      $policyCost[$key]["total"] = $quote["total"];
      $policyCost[$key]["state"] = $quote["state"];
      $policyCost[$key]['items'] = array();
      $items = $quote->policyCostDetails;
      $index = 0;
      foreach ($items as $item) {
          $policyCost[$key]['items'][$index]['id'] = $item['id'];
          $policyCost[$key]['items'][$index]['concept'] = $item['concept'];
          $policyCost[$key]['items'][$index]['value']   = $item['compute_value'];
          $policyCost[$key]['items'][$index]['value']   = $item['compute_value'];
          $policyCost[$key]['items'][$index]['isdiscount']     = $item['isdiscount'];
          $policyCost[$key]['items'][$index]['commissionable'] = $item['commissionable'];
          $index ++;
      }
    }
    $policyCost["total_cost"] = $total;
    return $policyCost;
  }

  public function getPolicyQuote($numberQuote){
    $quote = PolicyCost::where("quote_number",$numberQuote)
                          ->where("policy_id",$this->id)
                          ->where("emision_number",$this->emision_number)
                          ->where("renewal_number",$this->renewal_number)
                          ->first();
    return $quote;
  }

  //TODO: CHECK FOR INSURANCE_COMPANY
  public static function checkPolicyNumberExist(Plan $plan,$policy_number){
    //$icid = $plan->insurance_company_id;
    $policies = Policy::where("policy_number",$policy_number)
                        ->get();

    if(count($policies)==0){
      return false;
    }else{
      return true;
    }
  }

  public function getStatePolicyDesc(){
    switch ($this->state) {
      case 'cancel_late_payment':
        return "Cancelada pago tarde";
        break;
      case 'wait_ic_response':
        return "Espera respuesta CS";
        break;
      case 'decline':
        return "Rechazada por cliente";
        break;
      case 'wait_client_payment':
        return "Espera de Pago";
        break;
      case 'wait_signed_policy':
        return "Espera póliza firmada";
        break;
      case 'wait_receive_bd':
        return "Espera acuse recibo";
        break;
      case 'send_docs_client':
        return "Enviar póliza cliente";
        break;
      case 'wait_cu_response':
        return "Espera Respuesta del cliente";
        break;
      case 'active':
        return "Activa";
        break;
      default:
        return $this->state;
        break;
    }
  }

  public static function getListStatesPolicyDesc(){
    $states = array();
    $states['cancel_late_payment'] = "Cancelada pago tarde";
    $states['wait_ic_response'] = "Espera respuesta CS";
    $states['decline'] = "Rechazada por cliente";
    $states['wait_client_payment'] = "Espera de Pago";
    $states['wait_signed_policy'] = "Espera póliza firmada";
    $states['wait_receive_bd'] = "Espera acuse recibo";
    $states['send_docs_client'] = "Enviar póliza cliente";
    $states['wait_cu_response'] = 'Espera Respuest del clienet';
    $states['active'] = "Activa";
    return $states;
  }

  //find is this is this is teh first year of the policy
	public function getYearsActive(){

		$dob = new Carbon($this->start_date);
		return $dob->age;
	}

  public function scopeActive(){
    return $this->where('state','active');
  }

  public function isActive(){
    if($this->state=="active"){
      return true;
    }else{
      return false;
    }
  }

}
