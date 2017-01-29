<?php namespace Modules\Quotation\Entities;

use Modules\Policy\Entities\QuoteCode;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\PlanDeducibleType;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\Deducible;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\Plan\Entities\PlanDeducibleAdditionalCover;
use Modules\Plan\Entities\PlanDeducibleAdditionalCoverValue;

class QuotationCalculator {

	/*array with affiliate details
	[
		["role"=>'',edad=>,sex=>],
		["role"=>'',edad=>,sex=>],
	]*/
	protected $affiliates = [];
	/*
	 *  NumberPayments object
	 */
	protected $paymentMethod;

	protected $effectiveDate;

  	function __construct($affiliates, $numberPayment, $effectiveDate){
    	$this->affiliates = $affiliates;
    	$this->numberPayments = $numberPayment;
    	$this->effectiveDate = $effectiveDate;
	}

	public function calculatePremiums($ins_comp_id,$planDeductibles){
	    $resp = array();

	    switch($ins_comp_id){
		    case 1:
		        $resp = $this->calculatePremiumsBD($ins_comp_id, $planDeductibles);
		    	break;
		    default:
		    	break;
	    }

	    return $resp;
	}//end calculatePremiums

	protected function calculatePremiumsBD($insur_id,$planDeductibles){
	    //send data to BD for calculations
	    $username = 'flormariduena';
	    $password = 'Fm0914126354';
	    $base_url = 'https://agentportal.bestdoctorsinsurance.com/';
	    //file to store cookie data
	    $cookieFile = storage_path().'/cookies/bestdoctorscookie.txt';

	    $login_fields = array (
	      'Username' => $username,
	      'Password' => $password,
	      'AccountType' => 1
	    );

	    $genderCode = ["male"=>100,"female"=>101];

	    //manually create the cookiejar
	    //$cookieJar = new CookieJar();
	    $cookieJar = new FileCookieJar($cookieFile, true);//print_r($cookieJar);exit;

	    $affRoles = AffiliateRole::get()
	    							->keyBy("id");

	    $quoteRiders = array();
	    if( !(is_array($planDeductibles) && count($planDeductibles)>0) ){
	    	throw new \Exception("Debe selccionar al menos una opcion", 400);
	    }

	    $curr_plan = Deducible::find($planDeductibles[0]->id)->plan;
	    if($curr_plan==null){
	    	throw new \Exception("Plan seleccionado no existe", 400);
	    }
	    $planIDCode = QuoteCode::getValue($curr_plan, $insur_id);
	    $index = 0;
	    foreach ($planDeductibles as $deductible) {
	    	$deductible = (array)$deductible;
	   		$deducibleIDCode = QuoteCode::getValueFromID( (new Deducible)->getTable(), 
															$deductible['id'], 
															$insur_id);
	   		$quote_plans[] = array(
				"IsDeleted"=>"false",
				"PlanId"=>$planIDCode,
				"PlanOptionId"=>$deducibleIDCode,
				"QuoteId"=>0,
				"QuotePlanId"=>0,
				"QuotePremiums"=>array(),
				"Tag"=>"plan-premier-plus-tm"
		    );

		    foreach ($deductible['addCoversValue'] as $addCoverV) {
		    	$addCoverV = (array)$addCoverV;
		    	$quoteRiders[$index]['RiderId']=
		    							QuoteCode::getValueFromID(
	    									(new PlanDeducibleAdditionalCover)->getTable(),
	    									$addCoverV['acID'], 
	    									$insur_id);
		        $quoteRiders[$index]['RiderOptionId']=
		        						QuoteCode::getValueFromID(
	        								(new PlanDeducibleAdditionalCoverValue)->getTable(),
	        								$addCoverV['acvID'], 
	        								$insur_id);
		        $quoteRiders[$index]['PlanOptionId']=$deducibleIDCode;
		        $quoteRiders[$index]['PlanId']=$planIDCode;
		        $quoteRiders[$index]['IsSmoker']=false;
			    $index++;
	   		}
	    }

	    $quote_members = array();
	    foreach( $this->affiliates as $aff ){
	    	$aff = (array)$aff;
			$quote_members[] = array(
				"Age"=>$aff['age'],
				"Email"=>"",
				"FirstName"=>"",
				"GenderId"=>$genderCode[$aff['gender']],
				"LastName"=>"",
				"MemberTypeId"=>QuoteCode::getValue($affRoles[$aff['role']], $insur_id),
				"PhoneAreaCode"=>"",
				"PhoneCountryCode"=>"",
				"PhoneExtNumber"=>"",
				"PhoneNumber"=>"",
				"QuoteId"=>0,
				"QuoteMemberId"=>0,
				"QuoteRiders"=>$quoteRiders,
				"Tag"=>"app-0000"
			);
	    }
	    
	    $effday = date(\DateTime::ISO8601,strtotime($this->effectiveDate));

	    $params = array(
				'quote' => array(
				"Agent"=>"null",
				"AgentId"=>4149,
				"Country"=>"null",
				"CountryId"=>64,
				"EffectiveDate"=>$effday,
				"Id"=>0,
				"IncludeSubAgents"=>0,
				"IsDeleted"=>"false",
				"Issuer"=>"null",
				"IssuerId"=>5,
				"MasterAgentId"=>569,
				"PaymentFrecuencyId"=>QuoteCode::getValue($this->numberPayments, $insur_id),
				"PaymentFrequency"=>"null",
				"QuoteNumber"=>"",
				"QuoteStatuses"=>array(),
				"QuoteMembers"=>$quote_members,
				"QuotePlans"=>$quote_plans
			)
	    );

	    $params = json_encode($params);
	    $headers = [
	      'Content-Type'=>'application/json charset=UTF-8',
	    ];

	    $counter =0;
	    do{
			$go = true;
			$counter++;

			$quote_url = $base_url.'Quote/CalculatePremium';
			$client = new Client(['cookies' => $cookieJar]);

			$response = $client->request('POST', 
											$quote_url, 
											['headers'=>$headers, 'body' => $params]
										);
			$str_resp = $response->getBody()->getContents();
			$resp_class = json_decode($str_resp);//print_r($resp_class);exit;

			if( isset($resp_class->authorizeAction) && ($resp_class->authorizeAction == 'login'))
			{
				$go = false;
				//empty or invalid cookie - clear cookiejar just in case
				$cookieJar->clearSessionCookies();
				//try login
				$client_login = new Client(['cookies' => $cookieJar]);
				$login_url = $base_url.'Account/Login';
				$client_login->request('POST', $login_url, ['query'=>$login_fields]);//print_r($cookieJar);exit;
			}

			if( ($counter > 1) && (!$go)  ){
				//print_r($cookieJar);
				//echo 'error - '.$counter.' - '.$go;exit;
				return array();
			}
	    }while( !$go );

	    //tab data received
	    $all_premiums = array();
	    foreach($resp_class->list as $key => $i){
		    $premium = array();
		    foreach( $i->Premium as $Key2 => $p ){
		        $items = array();
		        $premium[$Key2]['total'] = $p->Total;
		        $premium[$Key2]['plan_id'] = $p->QuotePlanId;
		        foreach( $p->QuotePremiumDetails as $det ){
		          $temp = array();
		          $temp['amount'] = $det->Amount;
		          $temp['name'] = $det->InvoiceComponent->Name;
		          $items[] = $temp;
		        }
		        $premium[$Key2]['items'] = $items;
		    }
		    $code = QuoteCode::where("table_type","plan_deducible")
		    			->where("value",$i->PlanOptionId)
		    			->where("insurance_company_id",$insur_id)
		    			->first();
		    $all_premiums[$key]["pland_deducible_id"] = $code["table_id"];
		    $all_premiums[$key]["premium"] = $premium;
	    }

	    return $all_premiums;
  	}

}
