<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\ProcessCatalog;
use App\FileEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Modules\Email\Entities\EmailUtils;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Mail;
use Modules\Reception\Entities\RequestPolicyData;
use JWTAuth;
use Modules\Authorization\Entities\Role;
use Modules\Payment\Entities\PaymentMethod;
/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessRequestAppNewPolicyBD extends ProcessEntry
{
	private $listProcessToDocuments=
							[
								'InitialDocumentation',
								'UploadPolicyRequest'
							];

	private $email_template_reason='requestPolicyInsuranceCompany';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'RequestAppNewPolicyBD');
	}

	function start($procedure_entry=null){
		parent::start($procedure_entry);
		$this->doProcess();
		$this->finish();
	}

	public function doProcess(){
		//obtener la poliza relacionada al proceso
		$policy=$this->procedureEntryRel->policy;

		$agente=$policy->agente;

		$deducible=Deducible::select('plan_id')
								->find($policy->plan_deducible_id);

		//get InsuranceCompany to which the process is related 
		$insuranceCompany=Plan::find($deducible->plan_id)->insuranceCompany;

		$emailComp=InsuranceCompanyEmail::
							where('insurance_company_id',$insuranceCompany->id)
							->where('reason',$this->email_template_reason)
							->first();

		if($emailComp==null){
			throw new 
				\Exception("Email configuration for insurance company not found", 1);
		}

		$to['address']=trim($emailComp->email);
		$to['name']=$emailComp->contact_name;
		$param['subject']=$emailComp->subject;

		//copy to the agent that sold the policy and copy to the user that is doing the process
		$user = JWTAuth::parseToken()->authenticate();
		$param['cc']=array($user->email,$agente->email);

		$customer=$policy->customer;
		$param["variables"]['TRAMITE_ID']=$this->procedure_entry_id;
		$param["variables"]['CUSTOMER']=$customer->getFullNameAttribute();
		//deberia ser policy_startDate
		$param["variables"]['EFFECTIVE_DATE']=$policy->start_date;

		//data needed to create the template for the email
		$appData = $this->getDataFromRequestPolicyData();
		
		$paymentData = (array)$appData["payment_obj"];
		$dataTemplate['request_invoice'] = false;
		$dataTemplate['request_discount'] = false;
		if($paymentData['request_discount']){
			$dataTemplate['request_discount'] = true;
			$dataTemplate['per_discount'] = $paymentData["discount_percentage"];
			$pm=PaymentMethod::find($paymentData['payment_method'])->display;
			$dataTemplate["payment_method"] = $pm;
		}
		if($paymentData['request_invoice']){
			$dataTemplate['request_invoice'] = true;
		}
		$param['template']=$this->getTemplateEmail($dataTemplate);

		$param["attachments"] = $this->getFilesSendEmail();

		$policy->state = 'wait_ic_response';
		$policy->save();

		$emailUtils = new EmailUtils();
		//the reason 'requestPolicyInsuranceCompany' is a generic template that is used to send the email where teh broker ask for the policy to the insuranceCompany
		$emailUtils->sendEmilProcess($this->email_template_reason,$to,$param);
	}

	public function getTemplateEmail($data){
		$template="Estimado,\r\n Favor procesar la solicitud adjunta, vigencia <EFFECTIVE_DATE>.\r\n";
		if($data['request_discount']){
			$template .="Por favor realizar un descuento del ".$data['per_discount']."% a la póliza por concepto de pago en ".$data["payment_method"]."\r\n";
		}
		if($data['request_invoice']){
			$template .="Por favor enviar junto con la póliza la factura del cliente";
		}

		return $template;
	}

	public function getDataFromRequestPolicyData(){
		$prc = ProcessCatalog::where('name', 'UploadPolicyRequest')->value('id');
		$previousProcedureEntryID = ProcessEntry::where('id', $this->id)->value('procedure_entry_id');
		$previousProcessEntryID = ProcessEntry::where('procedure_entry_id', $previousProcedureEntryID)
                                      ->where('process_catalog_id', $prc)
                                      ->value('id');

        //obtener el ultimo proceso
		$request_policy_data = RequestPolicyData::where('process_id' , $previousProcessEntryID)
													->first()
													->data;

		return (array)json_decode($request_policy_data);
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

	public function getFilesSendEmail(){
		$procedureId=$this->procedureEntryRel->id;

		$attachments = FileEntry::where('table_type','procedure_entry')
								->where('table_id',$procedureId)
								->select('complete_path as pathToFile',
									'mime',
									'original_filename as display',
									'filename',
									'id')
								->get()
								->toArray();
		return $attachments;
	}

}