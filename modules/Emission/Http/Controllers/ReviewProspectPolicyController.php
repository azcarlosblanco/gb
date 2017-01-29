<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Entities\ProcessReviewProspectPolicy;
use App\ProcessEntry;
use Modules\Policy\Entities\Policy;
use Modules\Plan\Entities\PlanDeducible;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Plan\Entities\NumberPayments;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\Payment\Entities\PolicyCost;
use Validator;
use Modules\Affiliate\Entities\AffiliatePolicy;
use Modules\Affiliate\Entities\AffiliatePolicyExtra;
use Modules\Affiliate\Entities\AffiliatePolicyAnnex;
use Modules\Payment\Services\PolicyCostService;
use JWTAuth;
use Modules\Email\Entities\EmailUtils;
use Modules\Agente\Entities\Agente;

class ReviewProspectPolicyController extends NovaController {
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID){
        try{
            $pup=ProcessReviewProspectPolicy::
                        findProcess($process_ID);
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }

            $policy_id = ProcessEntry::find($process_ID)
                                        ->procedureEntryRel()
                                        ->first()
                                        ->policy_id;
            $policy = Policy::with("affiliates.affiliate")
                                ->with("planDeducible.plan")
                                ->find($policy_id);

            $catalog = array();
            
            $affRole = AffiliateRole::pluck("name","id");

            $catalog['plan'] = $policy->planDeducible->plan->name;
            $catalog['deductibles'] = $policy->planDeducible->name;
            $catalog['type_extra'] = AffiliatePolicyExtra::getTypeOptions();
            $catalog['effective_date'] = date("m/d/Y",strtotime($policy->start_date));
            $catalog['client_name'] = $policy->customer->full_name;
            $catalog['number_payments'] = NumberPayments::find($policy->payments_number_id)->description;
            $catalog['affiliates'] = array();
            $affiliatesPolicy = $policy->affiliates;
            
            foreach ($affiliatesPolicy as $afpolicy) {
                if(is_null($afpolicy->dismiss_date)){
                    $affiliate = $afpolicy->affiliate;
                    $afid = $affiliate->id;
                    $catalog['affiliates'][$afid] = $affiliate->full_name;
                    $catalog['affiliatesdob'][$afid] = date("d/m/Y",strtotime($affiliate->dob));

                    $catalog['affiliates_obj'][$afid]['name'] = $affiliate->full_name;
                    $catalog['affiliates_obj'][$afid]['type'] = $affRole[$afpolicy->role];
                    $catalog['affiliates_obj'][$afid]['dob'] = date("m/d/Y",strtotime($affiliate->dob));
                    $catalog['affiliates_obj'][$afid]['edate'] = 
                                                date("m/d/Y",strtotime($afpolicy->effective_date));
                }
            }

            $data['catalog'] = $catalog;
            $data['process_id'] = $process_ID;
            $data['policy_cost'] = $policy->getPolicyCosts();
            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

    private function validateReviewPolicy(array $data){
        $rules = array(
                "num_policy"               => "required|integer",
                "annexes.*.affiliate"      => "exist:affiliate,id",
                "annexes.*.annex"          => "required_if:annexes.*.affiliate",
                "annexes.*.edate"          => "required_if:annexes.*.affiliate",
                "extras.*.affiliate"       => "exist:affiliate,id",
                "extras.*.description"     => "required_if:annexes.*.affiliate",
                "extras.*.type"            => "required_if:annexes.*.affiliate|in:1,0",
            );

        $validation = Validator::make($rules,$data);
        if($validation->fails()){
            //validation failed, throw an exception
            throw new \Exception( $validation->messages() );
        }
    }

	public function review(Request $request,$process_ID){
		\DB::beginTransaction();
        $code = null;
        try{
            $pup=ProcessReviewProspectPolicy::findProcess($process_ID);
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }

            $input = $request->all();
            $data = (array)json_decode($input['review_policy_obj']);

            
            $policy = $pup->procedureEntryRel->policy;

            //save policy_number
            //check that there is not other policy with the same number for the same insurance company
            $plan = $policy->getPlan();
            $result = Policy::checkPolicyNumberExist($plan,$data['num_policy']);
            if($result){
                throw new \Exception("Ya existe una póliza con ese número");
            }
            $policy->policy_number = $data['num_policy'];
            $policy->state = 'wait_cu_response';
            $policy->save();
            

            $affPolicy = $policy->affiliates()
                                    ->pluck("id","affiliate_id");

            //save exlusion y ammends
            foreach ($data["extras"] as $extra) {
                $extra = (array)$extra;
                if($extra["affiliate"]==""){
                    $code = 422;
                    throw new Exception("Falta información del afiliado en el regitstro de enmiendas y exclsiones");
                }
                AffiliatePolicyExtra::create(   
                                        [
                                    "affiliate_policy_id" => $affPolicy[$extra["affiliate"]], 
                                    "description"         => $extra["description"], 
                                    "type"                => $extra["type"]
                                        ]
                                            );
            }
            
            foreach ($data["annexes"] as $annex) {
                $annex = (array)$annex;
                if($annex["affiliate"]==""){
                    $code = 422;
                    throw new \Exception("Falta información del afiliado en el regitstro de enmiendas y exclsiones");
                }
                $edate = date("Y-m-d",strtotime($annex['edate']));
                AffiliatePolicyAnnex::create(   
                                        [
                                    "affiliate_policy_id" => $affPolicy[$annex["affiliate"]], 
                                    "description"         => $annex["annex"], 
                                    "effective_date"      => $edate
                                        ]
                                            );
            }

            //register the cost of each quote of policy, 
            //register the total cots in prima 
            //VALIDATIONS
            //num_payments == num_quotes
            //value_quotes > 0
            //TODO: I should calculate the cost of taxes using the info of the values by affiliate
            $quote_costs = (array)$data['policy_cost'];
            $num_quotes = $policy->numQuotes->number;
            if($num_quotes!=(count($quote_costs)-1)) {
                print_r($num_quotes);
                print_r("cuotas enviadas ".count($quote_costs));
                $code = 400;
                throw new \Exception("La información de los costos de la póliza contiene errores");
            }

            $user = JWTAuth::parseToken()->authenticate();

            $quoteData = array();
            $quoteData["policy_id"] = $policy->id;
            $quoteData['quotes'] = array();
            
            foreach ($quote_costs as $key => $quote) {
                if($key == "total_cost"){
                    continue;
                }
                $quote = (array)$quote;
                $quoteData['quotes'][$key] = array();
                $quoteData['quotes'][$key]["user_regiter_cost"] = $user->id;
                $quoteData['quotes'][$key]["quote_number"] = $key + 1;
                $quoteData['quotes'][$key]["items"] = array();
                $quoteData['quotes'][$key]["discounts"] = array();
                $quoteData['quotes'][$key]["taxes"] = array();
                $index = 0;

                $primeValues = $quote['itemPrimes'];
                foreach ($primeValues as $kp => $value) {
                    $item = (array)$value;
                    $quoteData['quotes'][$key]["itemPrimes"][$kp]["value"] = $item['value'];
                    $quoteData['quotes'][$key]["itemPrimes"][$kp]["concept"] = $item['concept'];
                    if(isset($item['new']) && $item['new']==1){
                        $quoteData['quotes'][$key]["itemPrimes"][$kp]["new"] = 1;
                    }else{
                        $quoteData['quotes'][$key]["itemPrimes"][$kp]["new"] = 0;
                        $quoteData['quotes'][$key]["itemPrimes"][$kp]["id"] = $item['id'];
                    }
                }

                $discountValues = $quote['discounts'];
                foreach ($discountValues as $kp => $value) {
                    $item = (array)$value;
                    $quoteData['quotes'][$key]["discounts"][$kp]["value"] = $item['value'];
                    $quoteData['quotes'][$key]["discounts"][$kp]["concept"] = $item['concept'];
                    $quoteData['quotes'][$key]["discounts"][$kp]["new"] = 1;
                }

                $taxValues = $quote['itemTaxes'];
                foreach ($taxValues as $kp => $value) {
                   $item = (array)$value;
                    $quoteData['quotes'][$key]["taxes"][$kp]["value"] = $item['value'];
                    $quoteData['quotes'][$key]["taxes"][$kp]["concept"] = $item['concept'];
                    if(isset($item['new']) && $item['new']==1){
                        $quoteData['quotes'][$key]["taxes"][$kp]["new"] = 1;
                    }else{
                        $quoteData['quotes'][$key]["taxes"][$kp]["new"] = 0;
                        $quoteData['quotes'][$key]["taxes"][$kp]["id"] = $item['id'];
                    }
                }

            }
            $pcs = new PolicyCostService($policy);
            $pcs->confirmPolicyCosts($quoteData);

            $this->sendEmailDataPolicy($pup,$policy);

            $pup->finish();
            $code = 200;
            \DB::commit();
            $this->novaMessage->setRoute('emission/pending');
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $code = 500;
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
	}


    public function policyExtraByPolicyId($policy_id){
        $index = 0;
        $policyExtra=array();

        $policy = Policy::find($policy_id);
        $policyAffiliate = $policy->affiliatesAll;

        foreach ($policyAffiliate as $affiliate) {
            if(!is_null($affiliate)){
                $affiliatePolicy = AffiliatePolicy::find($affiliate->id);
                foreach ($affiliatePolicy->anmend as $affiliateExtra) {
                    if(!is_null($affiliateExtra)){
                        $affiliateExtra = AffiliatePolicyExtra::find($affiliateExtra->id);
                        $policyExtra[$index] = $affiliateExtra;
                        $policyExtra[$index]['affiliate_policy_id'] = AffiliatePolicy::find($affiliateExtra->affiliate_policy_id);
                        $index++;
                    }
                }
            }
        }
        return $policyExtra;
    }


    public function policyAnnexByPolicyId($policy_id){
        $index = 0;
        $policyAnnex=array();

        $policy = Policy::find($policy_id);
        $policyAffiliate = $policy->affiliatesAll;

        foreach ($policyAffiliate as $affiliate) {
            if(!is_null($affiliate)){
                $affiliatePolicy = AffiliatePolicy::find($affiliate->id);
                foreach ($affiliatePolicy->annex as $affiliateAnnex) {
                    if(!is_null($affiliateAnnex)){
                        $affiliateAnnex = AffiliatePolicyAnnex::find($affiliateAnnex->id);
                        $policyAnnex[$index] = $affiliateAnnex;
                        $policyAnnex[$index]['affiliate_policy_id'] = AffiliatePolicy::find($affiliateAnnex->affiliate_policy_id);
                        $index++;
                    }
                }
            }
        }
        return $policyAnnex;
    }


    public function AffiliateByAffiliatePolicyId($affiliatePolicy_id){
        $affiliatePolicy = AffiliatePolicy::find($affiliatePolicy_id);
        $affiliate = $affiliatePolicy->affiliate;
        return $affiliate;
    }
<<<<<<< HEAD
    
=======

>>>>>>> 3824d6436dcaceff2794647b5f6c057d6e9d78d1

    private function sendEmailDataPolicy(ProcessReviewProspectPolicy $process,
                                            $policy){

        $policyExtra = $this->policyExtraByPolicyId($policy->id);
        $annexpolicy = $this->policyAnnexByPolicyId($policy->id);
        $data['client_name']                =  $policy->full_name;
        $data['plan']                       =  $policy->planDeducible->plan->name;
        $data['deductibles']                =  $policy->planDeducible->name;
        $data['policy_number']              =  $policy->policy_number;
        $data['edate'] = date("m/d/Y",strtotime($policy->start_date));

        $affiliatesPolicy = $policy->affiliates;
        $affarray = array();
        $index = 0;
        foreach ($affiliatesPolicy as $afpolicy) {
            if(is_null($afpolicy->dismiss_date)){
                $affiliate = $afpolicy->affiliate;
                $affarray[$index]['full_name']    =  $affiliate->full_name;
                $affarray[$index]['type']         =  $afpolicy->affRole->name;
                $affarray[$index]['dob']          =  $affiliate->dob;
                $affarray[$index]['start_date']   =  date("m/d/Y",strtotime($afpolicy->effective_date));
                $index++;
            }
        }
        $data['affiliate']=$affarray;
        $textra = AffiliatePolicyExtra::getTypeOptions();
        $index = 0;
        $data['exclusion'] = array();
        foreach ($policyExtra as $polyex) {
            $affiliate_policy_id = $polyex->affiliate_policy_id->id;
            $afipolicyExtra = $this->AffiliateByAffiliatePolicyId($affiliate_policy_id);

            $data['exclusion'][$index]['full_name']    =  $afipolicyExtra->full_name;
            $data['exclusion'][$index]['dob']          =  $afipolicyExtra->dob;
            $data['exclusion'][$index]['type']         =  $textra[$polyex->type];
            $data['exclusion'][$index]['description']  =  $polyex->description;
            $index++;
        }

        $data['anexo'] = array();
        foreach ($annexpolicy as $anpol) {
            $affiliate_policy_id = $anpol->affiliate_policy_id->id;
            $afipolicyAnnex = $this->AffiliateByAffiliatePolicyId($affiliate_policy_id);

            $data['anexo'][$index]['full_name']         =  $afipolicyAnnex->full_name;
            $data['anexo'][$index]['anexo']             =  $anpol->description;
            $data['anexo'][$index]['e_date']         =  date("m/d/Y",strtotime($anpol->effective_date));
            $index++;
        }
        $data_pago=$policy->getPolicyCosts();
        $pagos=array();
        $index=0;
        foreach ($data_pago as $key => $value) {
            if( ($key!=="total_cost") ){
                $pagos[]=$value;
            }
        }
        $data['pago']=$pagos;
        $data['total_cost']=$data_pago['total_cost'];
        $data['number_payments'] = NumberPayments::find($policy->payments_number_id)->description;
        $template_data['params'] = $data; 
        $template_data['template_file'] = 'emission::email_policy_data';
        //end data needed to create the email content
        

        //data needed to send email
        $agente=Agente::find($policy->agente_id);
        $deducible = \Modules\Plan\Entities\Deducible::with('plan')
                                ->select('name','plan_id')
                                ->find($policy->plan_deducible_id);
        //get email template of InsuranceCompany to which the process is related 
        $emailComp = \Modules\InsuranceCompany\Entities\InsuranceCompanyEmail::
                            where('insurance_company_id',
                                    $deducible->plan->insurance_company_id)
                            ->where('reason',$process->getEmailTemplateReason())
                            ->first();
        $param=array();
        if($emailComp!=null){
            $param['subject']=$emailComp->subject;
        }
        $to['address']=trim($agente->email);
        $to['name']=$agente->getFullNameAttribute();
        $customer=$policy->customer;
        $param["variables"]['TRAMITE_ID']=$process->procedure_entry_id;
        $param["variables"]['CUSTOMER']=$customer->getFullNameAttribute();

        $emailUtils=new EmailUtils();
        //the reason 'requestPolicyInsuranceCompany' is a generic template that is used to send the email where teh broker ask for the policy to the insuranceCompany
        $emailUtils->sendEmilProcess(
                                $process->getEmailTemplateReason(),
                                $to,
                                $param,
                                true,
                                $template_data
                            );
    }
}