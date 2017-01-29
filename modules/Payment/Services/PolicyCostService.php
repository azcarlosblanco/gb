<?php namespace Modules\Payment\Services;
   
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Plan\Entities\NumberPayments;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Payment\Entities\PolicyCost;
use Modules\Payment\Entities\PolicyCostDetail;
use Modules\Payment\Entities\PolicyCostTaxFees;
use Modules\Payment\Entities\CreditCardPaymentDetail;
use Modules\Payment\Entities\TransferPaymentDetail;
use Modules\Payment\Entities\DepositPaymentPaymentDetail;
use Modules\Payment\Entities\ChequePaymentDetail;
use JWTAuth;
use Modules\Policy\Entities\Policy;
use Modules\Policy\Entities\PolicyCalculator;

class PolicyCostService {
	protected $policy;

  	function __construct(Policy $policy ){
    	$this->policy = $policy;
	}

    /**
     * This function is used to register the cost of the policy, when the policy is created
     */
	public function registerPolicyCosts(){
        $user = JWTAuth::parseToken()->authenticate();

        $policyCalculator = new PolicyCalculator($this->policy);
        $quotes = (array)$policyCalculator->calculatePremiums();
        if(count($quotes)==0){
            throw new \Exception("Error al obtener valor de los precios de las póliza");
        }

        foreach ($quotes as $key => $quote) {
            $quote = (array)$quote;
            $quote["quote_number"] = $key+1;
            $quote["user_regiter_cost"] = $user->id;
            $quote["custom_cost"] = 0;
            $this->registerQuote($quote);
        }
    }

    //TODO: VALIDATE THE REGISTER
    //QUOTE_NUMBER AND POLICY MUST BE UNIQUE
    private function registerQuote(array $data){
        $pc = new PolicyCost();
        $pc->state = 1;
        $pc->policy_id = $this->policy->id;
        $pc->quote_number = $data["quote_number"];
        $pc->emision_number = $this->policy->emision_number;
        $pc->renewal_number = $this->policy->renewal_number;
        $pc->user_regiter_cost = $data["user_regiter_cost"];
        $pc->custom_cost = $data["custom_cost"];
        $pc->save();

        //register the payment_details
        $total = 0;
        foreach ($data['items'] as $value) {
            $total = $total + $value["amount"];
            PolicyCostDetail::create([
                                    "concept"           => $value["name"],
                                    "value"             => $value["amount"],
                                    "compute_value"     => $value["amount"],
                                    "policy_cost_id"    => $pc->id,
                                    "commissionable"    => $value["commissionable"],
                                    "isdiscount"        => $value["isdiscount"],
                                        ]);
        }
        $pc->total = $total;
        $pc->save();
    }


    /**
     * This function is used to register the costs of the policy register for employee
     * in the process of confimr policy data. 
     */
    public function confirmPolicyCosts(array $quotes){
        //check number quotes == number of registers
        $policy_num_quotes = NumberPayments::find($this->policy->payments_number_id)
                                                ->number;

        if(count($quotes['quotes'])!=$policy_num_quotes){
            throw new \Exception("El número de registro no coinde con el número de cuotas de la póliza");
        }

        foreach ($quotes['quotes'] as $quote) {
            $this->updateQuoteValues($quote["quote_number"],$quote);
        }
    }

    private function updateQuoteValues($quoteNumber, array $data){
        $quote = PolicyCost::where("policy_id",$this->policy->id)
                                ->where("emision_number",$this->policy->emision_number)
                                ->where("renewal_number",$this->policy->renewal_number)
                                ->where("quote_number",$quoteNumber)
                                ->where("state",PolicyCost::S_PENDING)
                                ->first();

        $custom_values = false;
        $total = 0;
        //detail items
        foreach ($data['itemPrimes'] as $value) {
            if($value['new']!=1){
                $pcd = PolicyCostDetail::where("id",$value["id"])
                                            ->where('policy_cost_id',$quote->id)
                                            ->first();

                $pcd->value = $value['value'];
                if($value['value'] != $pcd->compute_value){
                    $custom_values = true;
                }
                $pcd->save();    
            }else{
                PolicyCostDetail::create([
                        "concept" => $value["concept"],
                        "value" => $value["value"],
                        "policy_cost_id" => $quote->id,
                        "compute_value" => "0.00",
                        "isdiscount" => 0,
                        "commissionable" => 1,
                    ]);
            } 
            $total = $total + $value["value"];
        }

        //taxes
        foreach ($data['taxes'] as $value) {
            if($value['new']!=1){
                $pcd = PolicyCostDetail::where("id",$value["id"])
                                            ->where('policy_cost_id',$quote->id)
                                            ->first();

                $pcd->value = $value['value'];
                if($value['value'] != $pcd->compute_value){
                    $custom_values = true;
                }
                $pcd->save();    
            }else{
                PolicyCostDetail::create([
                        "concept" => $value["concept"],
                        "value" => $value["value"],
                        "policy_cost_id" => $quote->id,
                        "compute_value" => "0.00",
                        "isdiscount" => 0,
                        "commissionable" => 0,
                    ]);
            } 
            $total = $total + $value["value"];
        }

        //discounts
        foreach ($data['discounts'] as $value) {
            PolicyCostDetail::create([
                    "concept" => $value["concept"],
                    "value" => $value["value"],
                    "policy_cost_id" => $quote->id,
                    "compute_value" => "0.00",
                    "isdiscount" => 1,
                    "commissionable" => 0,
                ]);
            $total = $total - $value["value"];
        }

        $quote->total = $total;
        if($custom_values){
            $quote->custom_cost = 1;
        }
        $quote->user_regiter_cost = $data["user_regiter_cost"];
        $quote->save();
    }

    /*public static function calculatePolicyCost($policy,$apply_dicsount=false){
        //get information about affiliates
        //get information about the taxes and fees to pay for the policy
        $role = AffiliateRole::pluck("name","id");
        $affiliatePolicy = $policy->affiliates;

        $nQuotes = NumberPayments::pluck("number","id");
        $nQuotesId = $policy->payments_number_id;

        $numQuotes = $nQuotes[$nQuotesId];
        $data =  array();
        $dataItem = array();
        for($i=0;$i<$numQuotes;$i++) {
            $sumAffiliateQuote = 0;
            foreach ($affiliatePolicy as $afpol) {
                $numdependent = 0;
                //el valor de la cuota del afiliado se obtine de la opcion del plan de la poliza
                //que concureda con los datos del afiliado, 
                //ese valor lo divido para el numero de quotas en que se vaya a pagar la poliza
                $affquote = self::getAffQuote($afpol,$numQuotes);
                if($role[$afpol->role] == "titular"){
                    $dataItem['titular']['id'] = "titular";
                    $dataItem['titular']['dname'] = "Titular";
                    $dataItem['titular']['cost'] = $affquote;
                }elseif($role[$afpol->role] == "esposo(a)"){
                    $dataItem['spouse']['id'] = "spouse";
                    $dataItem['spouse']['dname'] = "Esposa";
                    $dataItem['spouse']['cost'] = $affquote;
                }else{
                    $numdependent++;
                    $dataItem['dependent']['id'] = "dependent";
                    $dataItem['dependent']['dname'] = $numdependent. "Niños";
                    $dataItem['dependent']['cost'] = $affquote;
                }
                $sumAffiliateQuote = $sumAffiliateQuote + $affquote;
            }
            $data[$i]['items']       = $dataItem;
            $data[$i]['items'] = self::getTaxesPolicy($sumAffiliateQuote,$numQuotes,$i+1);
        }
        return $data;
    }

    private static function getAffQuote($afpol,$numQuotes){
        $valor = 100;
        return round($valor/$numQuotes,2);
    }

    private static function getTaxesPolicy($sumAffiliateQuote,$numQuotes,$quoteNumber){        
        $dataTaxes = array();
        
        $insFee = 0;
        if($numQuotes>1){
            $dataTaxes['installament_fee']['id'] = "installament_fee";
            $dataTaxes["installament_fee"]['dname'] = "Installment Fee";
            $insFee = self::calculateInstallentFee($sumAffiliateQuote,$numQuotes);
            $dataTaxes["installament_fee"]['cost']  = $insFee;
        }

        if($quoteNumber == 1){
            $dataTaxes['admin_fee']['id'] = "admin_fee";
            $dataTaxes['admin_fee']['dname'] = "Admin Fee"; 
            $dataTaxes['admin_fee']['cost'] = 75.00;
            $dataTaxes['iva']['id'] = "iva";
            $dataTaxes['iva']['dname'] = "IVA (Admin Fee)";
            $dataTaxes['iva']['cost'] = 0.14 * $dataTaxes['admin_fee']['cost'];
        }
        
        $sumAffQuoteAndInsFee = $sumAffiliateQuote + $insFee;
        $dataTaxes["ssc"]['id'] = "ssc";
        $dataTaxes["ssc"]['dname'] = "SSC";
        $dataTaxes["ssc"]['cost']  = self::calculateInstallMentFee($sumAffQuoteAndInsFee);
        return $dataTaxes;
    }

    private static function calculateInstallentFee($sumAffiliateQuote,$numQuotes){
        if($numQuotes == 1){
            return 0;
        }else{
            if($numQuotes == 2){
                return $numQuotes*0.06;
            }else{
                return $numQuotes*0.10;
            }
        }
    }

    //prima: the sum of quote of the affiates + installament fee
    private static function calculateInstallMentFee($sumAffQuoteAndInsFee){
        $ssc =  ($sumAffQuoteAndInsFee * 0.005);
        return $ssc;
    }*/

}