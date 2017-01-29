<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\FileEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Agente\Entities\Agente;
use Modules\Policy\Entities\Policy;
use Modules\Email\Entities\EmailUtils;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\Deducible;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Illuminate\Http\Request;
use JWTAuth;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessRegisterCustomerResponse extends ProcessEntry
{

	use \App\UploadAndDownloadFile;
	private $email_template_reason
					='rejectPolicy';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'RegisterCustomerResponse');
	}

	public function doProcess(Request $request){
		$response=$request['response'];
		if($response=='yes'){
			//marcar la poliza como esperando pago por parte del cliente
			$policy=$this->procedureEntryRel->policy;
			//eliminar los afiliados
			$policy->state='wait_client_payment';
			$policy->save();			
		}else{
			//marcar la poliza como cancelada y cancelar el proceso
			$user = JWTAuth::parseToken()->authenticate();
			$this->cancel();

			$procedure=$this->procedureEntryRel;
			$procedure->cancel("Cliente no desea la póliza",$user->id);

			$policy=$procedure->policy;
			//eliminar los afiliados
			$policy->state='decline';
			$policy->save();

			//should send a email saying the client does not want the policy anymore
			$this->sendRejectPolicyEmail($policy);
		}
	}

	public function sendRejectPolicyEmail($policy){
		/*$deducible=Deducible::with('plan')
							->select('name','plan_id')
							->find($policy->plan_deducible_id);
		
		$agente=Agente::find($policy->agente_id);

		//get email template of InsuranceCompany to which the process is related 
		$emailComp=InsuranceCompanyEmail::
							where('insurance_company_id',$deducible->plan->insurance_company_id)
							->where('reason',$this->email_template_reason)
							->first();

		if($emailComp==null){
			throw new 
				\Exception("Email configuration for insurance company not found", 1);
		}

		$to['address']=trim($emailComp->email);
		$to['name']=$emailComp->contact_name;
		$param['template']=$emailComp->template;
		$param['subject']=$emailComp->subject;

		$customer=$policy->customer;
		$param["variables"]['TRAMITE_ID']=$this->procedure_entry_id;
		$param["variables"]['CUSTOMER']=$customer->getFullNameAttribute();
		//deberia ser policy_startDate
		$param["variables"]['POLICY_NUMBER']=$policy->policy_number;

		$emailUtils=new EmailUtils();
		//the reason 'requestPolicyInsuranceCompany' is a generic template that is used to send the email where teh broker ask for the policy to the insuranceCompany
		$emailUtils->sendEmilProcess(
				$this->email_template_reason,
				$to,$param);*/
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID(true);
	}

}