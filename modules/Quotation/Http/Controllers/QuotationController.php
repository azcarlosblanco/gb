<?php namespace Modules\Quotation\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use App\Http\Controllers\Nova\NovaController;
use Illuminate\Http\Request;
use App\NovaMessage;
use DB;
use JWTAuth;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\PlanDeducibleType;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\InsuranceType;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\Quotation\Entities\QuotationCalculator;
use Modules\Quotation\Entities\Quotation;
use Modules\Email\Entities\EmailUtils;
use Modules\Agente\Entities\Agente;

class QuotationController extends NovaController {
	
	public function getDataForm(){
		$code = null;
		try{
			//$typeInsurance = InsuranceType::pluck("display_name","id");

			//deductibles types
			$decuctiblesTypes=PlanDeducibleType::pluck("name","id");
			foreach ($decuctiblesTypes as $key => $value) {
				$decuctiblesTypes[$key] = ucfirst($value);
			}
			//payment methods
			$pmethod=NumberPayments::pluck("description","id");
			//affiliates rols
			//$arole = AffiliateRole::pluck("name","id");
			//insurance_company
			//$ics = InsuranceCompany::pluck("company_name","id");
			//affiliate gender
			//$gender = ["male"=>'Masculino',"female"=>'Femenino'];

			$agents = Agente::select("name","lastname","id")
								->get();
			$agentList = array();
			foreach ($agents as $agent) {
				$agentList[$agent['id']]=$agent->getFullNameAttribute();
			}

			$data['decuctiblesTypes']=$decuctiblesTypes;
			$data['pmethod']=$pmethod;
			//$data['arole']=$arole;
			//$data['ics']=$ics;
			//$data['gender']=$gender;
			//$data['typeInsurance']=$typeInsurance;
			$data['agentList']=$agentList;


			$insCompID = 1; //best doctors id
			$type = InsuranceType::where('name','health')->first();

			$plan = Plan::typeInsurance($type['id'])
							->where("insurance_company_id",$insCompID)
							->with("deducibles.deducibleOptions")
							->orderBy("insurance_type_id")
							->get();

			$listPlans = array();
			foreach ($plan as $key1 => $value) {
				$ic = $value['insurance_company_id'];
				$plandeduct = array();
				$plandeduct['plan_id'] = $value['id'];
				$plandeduct['plan_name'] = $value['name'];
				$deductibles = array();
				foreach ($value['deducibles'] as $deducible) {
					$deductibles[$deducible->id]['deduct_id'] = $deducible['id'];
					$deductibles[$deducible->id]['deduct_name'] = $deducible['name'];
					foreach ($deducible['deducibleOptions'] as $options) {
						$deductype = $options['plan_deducible_type_id'];
						$deductibles[$deducible->id][$deductype]=$options['value'];
					}

					//information about additional covers
					$addiotnal_covers=array();
					foreach($deducible->additionalCover as $key => $addCover){
						$addiotnal_covers[$key]['name'] = $addCover->name;
						$addiotnal_covers[$key]['id'] = $addCover->id;
						$addiotnal_covers[$key]['options'] = [];
						$addiotnal_covers[$key]['IsCriticalSelect']=0;
						$addiotnal_covers[$key]['IsMaternity']=0;
						if($addCover->name=="Critical Select"){
							$addiotnal_covers[$key]['IsCriticalSelect']=1;
						}elseif($addCover->name=="Maternity Complications Rider"){
							$addiotnal_covers[$key]['IsMaternity']=1;
						}
						foreach ($addCover->addCoverValue as $key2 => $acv) {
							$addiotnal_covers[$key]['options'][$key2]['name'] = $acv->name;
							$addiotnal_covers[$key]['options'][$key2]['value'] = $acv->value;
							$addiotnal_covers[$key]['options'][$key2]['id'] = $acv->id;
						}
					}
					$deductibles[$deducible->id]['add_covers'] = $addiotnal_covers;
				}
				$plandeduct['deductibles'] = $deductibles;
				$plandeduct["insurance_company_id"] = $ic;
				$listPlans[$key1] = $plandeduct;
			}

			$data['listPlans']=$listPlans;

			$code = 200;
			$this->novaMessage->setData($data);

		}catch(\Exception $e){
			if($code==null){
				$code = 500;
			}
			$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}
		return $this->returnJSONMessage($code);  

	}
	
	//this function is not longer used, bue let him for furture use, maybe!!!!
	public function getPlansQuotation(Request $request){
		$code = null;
		try{
			if( !( isset($request["type_ins"]) && $request["type_ins"]!="") ){
				$code = 400;
				throw new \Exception("El tipo de seguro no es válido", 400);
			}

			if( !( isset($request["ins_comp_ids"]) && is_array($request["ins_comp_ids"]) ) ){
				$code = 400;
				throw new \Exception("Debe seleccionar al menos unca compania de seguros", 400);
			}

			$insCompID = $request["ins_comp_ids"];
			$type = $request["type_ins"];
			
			$plan = Plan::typeInsurance($type)
							->whereIn("insurance_company_id",$insCompID)
							->with("deducibles.deducibleOptions")
							->orderBy("insurance_type_id")
							->get();

			$listPlans = array();
			foreach ($plan as $key => $value) {
				$ic = $value['insurance_company_id'];
				$plandeduct = array();
				$plandeduct['plan_id'] = $value['id'];
				$plandeduct['plan_name'] = $value['name'];
				$deductibles = array();
				foreach ($value['deducibles'] as $deducible) {
					$deductibles[$deducible->id]['deduct_id'] = $deducible['id'];
					$deductibles[$deducible->id]['deduct_name'] = $deducible['name'];
					foreach ($deducible['deducibleOptions'] as $options) {
						$deductype = $options['plan_deducible_type_id'];
						$deductibles[$deducible->id][$deductype]=$options['value'];
					}
				}
				$plandeduct['deductibles'] = $deductibles;
				$listPlans[$ic]["insurance_company_id"] = $ic;
				$listPlans[$ic]["plans"][] = $plandeduct;
			}
			$code=200;
			$this->novaMessage->setData($listPlans);
		}catch(\Exception $e){
			if($code==null){
				$code = 500;
			}
			$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}	
    	return $this->returnJSONMessage($code);  
	}

	public function calculatePremium(Request $request){
		$code = null;
		try{
			$input = $request->all();
			
			//for quotation the gender of affiliates is not important
			//convert affiliate info to format required by teh quotation
			$arole = AffiliateRole::pluck("id","name");
			$affiliates = array();

			$index = 0;
			//owner
			$affiliates[$index]['role'] = $arole['titular'];
			$affiliates[$index]['age'] = $input['owner_age'];
			$affiliates[$index]['gender'] = 'male';

			if(isset($input['spouse_age'])){
				if($input['spouse_age']>0){
					$index++;
					//spouse
					$affiliates[$index]['role'] = $arole['esposo(a)'];
					$affiliates[$index]['age'] = $input['spouse_age'];
					$affiliates[$index]['gender'] = 'female';
				}
			}

			if(isset($input['number_kid'])){
				if($input['number_kid']>0){
					for($i = 0; $i<$input['number_kid']; $i++){
						$index++;
						//depended
						$affiliates[$index]['role'] = $arole['dependiente'];
						$affiliates[$index]['age'] = 10; 
						$affiliates[$index]['gender'] = 'male';
					}
				}
			}

			$effective_date = date("Y-m-d",strtotime($input['effective_date']));
			$numPayment = NumberPayments::find($input['number_payment_id']);
			$qc = new QuotationCalculator($affiliates,$numPayment,$effective_date);

			//deductiobles and additional covers
			$deductibles = (array)json_decode($input['deductibles']);
			
			//best doctors id
			$ic = 1;
			$premiums = $qc->calculatePremiums($ic,$deductibles);

			$this->novaMessage->setData($premiums);
			$code = 200;
		}catch(\Exception $e){
			if($code==null){
				$code = 500;
			}
			$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}	
    	return $this->returnJSONMessage($code);  
	}

	private function validateQuotation(array $data){
		$rules = array(
				"owner_age"    => "required|integer",
				"spouse_age"   => "required|integer",
				"number_kid"   => "required|integer",
				"number_payment_id"   => "required|exists:number_payments,id",
				"agent_id"            => "required|exists:agente,id",
				"client_name"         => "required",
				"client_email"        => "email", 
				"client_phone"        => "required",
			);

		//use Laravel's Validator and validate the data
		$validation = \Validator::make( $data, $rules, array());

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages(), 422 );
		}

		//all good and shiny
		return true;
	}

	private function validateAffiliates(array $data){
		$rules = array(
				"owner_age"    => "required|integer",
				"spouse_age"   => "required|integer",
				"number_kid"  => "required|integer",
			);

		//use Laravel's Validator and validate the data
		$validation = \Validator::make( $data, $rules, array());

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages(), 422 );
		}

		//all good and shiny
		return true;
	}

	public function saveQuotation(Request $request){
		\DB::beginTransaction();
  		$code = null;
		try{
			$input = $request->all();

			$data = (array)json_decode($input['obj_quotation']);
			
			//affiliate data
			$data_quotation = array();
			$data_quotation['owner_age'] = $data['owner_age'];
			$data_quotation['spouse_age'] = isset($data['spouse_age'])?$data['spouse_age']:0;
			$data_quotation['number_kid'] = isset($data['number_kid'])?$data['number_kid']:0;

			try{
				$this->validateAffiliates($data_quotation);
			}catch(\Exception $e){
				$code = 422;
				throw new \Exception( $e->getMessage(), 422 );
			}

			$number_payments_id = $data['pmethod'];
			$numPayments = NumberPayments::find($number_payments_id);
			if($numPayments==null){
				throw new \Exception(["number_payments_id","Número de Pagos es inválido"], 422 );
			}

			$deductibles = isset($data['premiuns'])?$data['premiuns']:array();
			if(count($deductibles)==0){
				throw new \Exception("Debe seleccionar al menos un deducible", 400 );
			} 

			$data_quotation['number_payments_id'] = $number_payments_id;
			$data_quotation['deductibles'] = $deductibles;

			$quotation = Quotation::create(
							[
								"date_quotation" => date("Y-m-d"),
								"agent_id"       => $data['agent_id'],
								"client_name"    => $data['client_name'],
								"client_email"   => $data['client_email'],
								"client_phone"   => "",//$input['client_phone'],
								"obj_quotation"  => json_encode($data_quotation)
							]
						);

			//send quotation by email
			$this->sendQuotationByEmail($quotation);
			$code = 200;
			\DB::commit();
		}catch(\Exception $e){
			\DB::rollback();
			if($code==null){
				$code = 500;
			}
			$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}	
    	return $this->returnJSONMessage($code);
  	}

  	public function sendQuotationByEmail(Quotation $quotation){
        $data['client_name'] = $quotation['client_name'];
        $agent = Agente::find($quotation['agent_id']);
        if($agent==null){
        	throw new \Exception("Agente no existe", 404);
        }
        $data['agent_name']    = $agent->full_name;
        $data['agent_email']   = $agent->email;
        $data['agent_mobile']  = $agent->mobile;
        $data['client_name']   = $quotation['client_name'];
        $data['client_email']  = $quotation['client_email'];
        
        //affiliates
        $obj_quotation = (array)json_decode($quotation['obj_quotation']);
        $data['afi']['owner_age'] = $obj_quotation['owner_age'];
		$data['afi']['spouse_age'] = isset($obj_quotation['spouse_age'])?
											$obj_quotation['spouse_age']:0;
		$data['afi']['number_kid'] = isset($obj_quotation['number_kid'])?
											$obj_quotation['number_kid']:0;
  
        //number payment     
        $data['number_payments'] = NumberPayments::find($obj_quotation['number_payments_id'])
        											->description;
        //requestes plans
        $data['plans']=array();
        foreach ($obj_quotation['deductibles'] as $key => $deducibleObj) {
        	$deductible = \Modules\Plan\Entities\Deducible::with('plan.insuranceCompany')
                                ->find($deducibleObj->deducible_id);
            $plan = $deductible->plan;
            $data['plans'][$key]['insurance_company'] = $plan->insuranceCompany->company_name;
            $data['plans'][$key]['plan_name'] = $plan->name;
			$data['plans'][$key]['deductible_name'] = $deductible->name;
			$data['plans'][$key]['total'] = $deducibleObj->total;
			$data['plans'][$key]['quotes'] = $deducibleObj->quotes;		           
        }


        $template_data['params'] = $data; 
        $template_data['template_file'] = 'quotation::email_quotation';
        //end data needed to create the email content
        
        //params that are part of email subject, sender and reciever
        if(isset($data['client_email']) && $data['client_email']!=""){
        	$to['address'] = $data['client_email'];
        	$to['name']    = $data['client_name'];
        }else{
        	$to['address']=trim($agente->email);
        	$to['name']=$agent->getFullNameAttribute();
        }

        $param['cc'][]=$agent->email;
        $param["variables"]['CUSTOMER']=$data['client_name'];

        //return view('quotation::email_quotation',$template_data['params']);
        $emailUtils=new EmailUtils();
        //the reason 'quotation' is a generic template that is used to send the email from quotations
        $emailUtils->sendEmilProcess(
                                "quotation",
                                $to,
                                $param,
                                true,
                                $template_data
                            );
  	}

  	public function listQuotation(Request $request){
  		$code = null;
  		try{
  			$pagenum = $request->input('pagenum', 0);
  			$result = Quotation::with('agent');

	        if( $pagenum > 0 ){
	            $result = $result->orderBy("date_quotation")
	            						->paginate($pagenum);
	        }else{
	            $result = $result->orderBy("date_quotation")
	            						->get();
	        }

	        $listQuotation = array(); 
	        foreach ($result as $key => $value) {
	        	$listQuotation[$key]['id'] = $value['id'];
	        	$listQuotation[$key]['agente'] = $value['agent']['full_name'];
	        	$listQuotation[$key]['client_name'] = $value['client_name'];
	        	$listQuotation[$key]['client_email'] = $value['client_email'];
	        	$listQuotation[$key]['date_quotation'] = 
	        											date("d/m/Y",
	        												strtotime($value['date_quotation']));
	        	$listQuotation[$key]['buttons'] = array(
                           	array(
                                 'class' => 'available',
                                 'active' => true,
                                 'link'  => 'root.seguros.view-quotation',
                                 'params' => [
                                 		'id'   => $value['id'],
                                 			],
                                 'icon' => 'glyphicon glyphicon-eye-open',
                                 'description' => 'Ver',
                            ),
                       );
	        }

	        if($request->has('withView')){
                $this->novaMessage->setData(
                                        $this->renderIndex($listQuotation));
	        }else{
	            $this->novaMessage->setData($listQuotation);
	        }
	        $code=200;
  		}catch(\Exception $e){
 			if($code==null){
 				$code = 500;
 			}
  			$this->novaMessage->addErrorMessage($code,$e->getMessage());
  		}
  		return $this->returnJSONMessage($code);

  	}

  	private function renderIndex($content){
  		$index['display']['title']="Cotizaciones";
		$index['display']['header'][]=array('label' =>'# Cotización',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'id');
		$index['display']['header'][]=array('label' =>'Agente',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'agente');
		$index['display']['header'][]=array('label' =>'Nombre del Cliente',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'client_name');
		$index['display']['header'][]=array('label' =>'Email del Cliente',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'client_email');
		$index['display']['header'][]=array('label' =>'Fecha Cotización',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'date_quotation');
        $index['display']['header'][]= array('label' =>'Botenes de Acción',
                                  			'fieldName' =>'buttons');
        $index['list']=$content;
        return $index;
  	}

  	public function viewQuotation($id, Request $request){
  		$code = null;
  		try{
  			$quotation = Quotation::find($id); 
  			$data['quotation_id'] = $id;
	  		$data['client_name'] = $quotation['client_name'];
	        $agent = Agente::find($quotation['agent_id']);
	        if($agent==null){
	        	throw new \Exception("Agente no existe", 404);
	        }
	        $data['agent_name']    = $agent->full_name;
	        $data['agent_email']   = $agent->email;
	        $data['agent_mobile']  = $agent->mobile;
	        $data['client_name']   = $quotation['client_name'];
	        $data['client_email']  = $quotation['client_email'];
	        $data['date_quotation']  = date("d/m/Y",strtotime($quotation['date_quotation']));
	        
	        //affiliates
	        //affiliates
	        $obj_quotation = (array)json_decode($quotation['obj_quotation']);
	        $data['affiliates'] = array();
	        $data['affiliates']['owner_age'] = $obj_quotation['owner_age'];
			$data['affiliates']['spouse_age'] = isset($obj_quotation['spouse_age'])?
												$obj_quotation['spouse_age']:"";
			$data['affiliates']['number_kid'] = isset($obj_quotation['number_kid'])?
												$obj_quotation['number_kid']:0;  
	        
	        //number payment     
	        $data['number_payments'] = NumberPayments::find($obj_quotation['number_payments_id'])
	        											->description;
	        //requestes plans
	        $data['plans']=array();
	        foreach ($obj_quotation['deductibles'] as $key => $deducibleObj) {
	        	$deductible = \Modules\Plan\Entities\Deducible::with('plan.insuranceCompany')
	                                ->find($deducibleObj->deducible_id);
	            $plan = $deductible->plan;
	            $data['plans'][$key]['insurance_company'] = $plan->insuranceCompany->company_name;
	            $data['plans'][$key]['plan_name'] = $plan->name;
				$data['plans'][$key]['deductible_name'] = $deductible->name;
				$data['plans'][$key]['total'] = $deducibleObj->total;
				$data['plans'][$key]['quotes'] = $deducibleObj->quotes;		           
	        }
	        $code = 200;
	        $this->novaMessage->setData($data);
        }catch(\Exception $e){
 			if($code==null){
 				$code = 500;
 			}
  			$this->novaMessage->addErrorMessage($code,$e->getMessage());
  		}
  		return $this->returnJSONMessage($code);
  	}

  	public function convertQuotationIntoEmission(){

  	}



	
}