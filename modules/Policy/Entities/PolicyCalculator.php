<?php namespace Modules\Policy\Entities;

use Modules\Policy\Entities\Policy;
use Modules\Policy\Entities\QuoteCode;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;

class PolicyCalculator {
  protected $policy;

  function __construct( Policy $policy ){
    $this->policy = $policy;
	}

  public function calculatePremiums(){
    $resp = array();
    $ins_comp = $this->policy->planDeducible->plan->insuranceCompany;

    switch($ins_comp->id){
      case 1:
        $resp = $this->calculatePremiumsBD($ins_comp->id);
      break;

      default:
      break;
    }

    return $resp;
  }//end calculatePremiums

  protected function calculatePremiumsBD($ins_comp){
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

    //manually create the cookiejar
    //$cookieJar = new CookieJar();
    $cookieJar = new FileCookieJar($cookieFile, true);//print_r($cookieJar);exit;
    $quote_members = array();
    foreach( $this->policy->affiliates as $aff ){
      $curr_plan = $this->policy->planDeducible->plan;
      $quote_plans[] = array(
        "IsDeleted"=>"false",
        "PlanId"=>QuoteCode::getValue($curr_plan, $ins_comp),
        "PlanOptionId"=>QuoteCode::getValue($this->policy->planDeducible, $ins_comp),
        "QuoteId"=>0,
        "QuotePlanId"=>0,
        "QuotePremiums"=>array(),
        "Tag"=>"plan-premier-plus-tm"
      );

      $quoteRiders = [];
      $apaddionalCovers = $aff->addCover;
      foreach($apaddionalCovers as $key => $apaddcover){
        /*
        IsSmoker:false
        PlanId:9
        PlanOptionId:8
        RiderId:22
        RiderOptionId:166
        */
        $acv = $apaddcover->additionalCoverV; 
        $quoteRiders[$key]['RiderId']=QuoteCode::getValue($acv->addCover, $ins_comp);
        $quoteRiders[$key]['RiderOptionId']=QuoteCode::getValue($acv, $ins_comp);
        $quoteRiders[$key]['PlanOptionId']=$quote_plans[0]['PlanOptionId'];
        $quoteRiders[$key]['PlanId']=$quote_plans[0]['PlanId'];
        $quoteRiders[$key]['IsSmoker']=false;
      }

      $quote_members[] = array(
        "Age"=>$aff->affiliate->getAge(),
        "Email"=>"",
        "FirstName"=>"",
        "GenderId"=>100,
        "LastName"=>"",
        "MemberTypeId"=>QuoteCode::getValue($aff->affRole, $ins_comp),
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
    
    $effday = date(\DateTime::ISO8601,strtotime($this->policy->start_date));

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
        "PaymentFrecuencyId"=>QuoteCode::getValue($this->policy->numQuotes, $ins_comp),
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

      $response = $client->request('POST', $quote_url, ['headers'=>$headers, 'body' => $params]);
      $str_resp = $response->getBody()->getContents();
      $resp_class = json_decode($str_resp);//print_r($resp_class);exit;


      if( isset($resp_class->authorizeAction) && ($resp_class->authorizeAction == 'login') ){
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


    foreach($resp_class->list as $i){
      foreach( $i->Premium as $p ){
        $premium = array();
        $items = array();
        $premium['total'] = $p->Total;
        $premium['plan_id'] = $p->QuotePlanId;

        foreach( $p->QuotePremiumDetails as $det ){
          $temp = array();
          $temp['amount'] = $det->Amount;
          $temp['name'] = $det->InvoiceComponent->Name;
          $temp['commissionable'] = ($det->InvoiceComponent->IsCommissionable==true)?1:0;
          $temp['isdiscount'] = 0;
          $items[] = $temp;
        }

        $premium['items'] = $items;
        $all_premiums[] = $premium;
      }
    }

    return $all_premiums;
    //return (isset($resp_class->list)) ? $resp_class->list : array();
  }

}
