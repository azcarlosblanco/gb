<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\FileEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Modules\Email\Entities\EmailUtils;
use Modules\Agente\Entities\Agente;
use Modules\Payment\Entities\Payment;
use Modules\Plan\Entities\Deducible;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Illuminate\Http\Request;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessRegisterInvoice extends ProcessEntry
{
	use \App\UploadAndDownloadFile;
	private $email_template_reason
					='sendInvoiceAgent';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'RegisterInvoice');
	}

	public function doProcess(Request $request){
		
	}

	public function sendInvoiceCustomer($policy,$invoiceId){
		$deducible=Deducible::with('plan')
							->select('name','plan_id')
							->find($policy->plan_deducible_id);

		$agente=Agente::find($policy->agente_id);

		//get email template of InsuranceCompany to which the process is related 
		$emailComp=InsuranceCompanyEmail::
							where('insurance_company_id',$deducible->plan->insurance_company_id)
							->where('reason',$this->email_template_reason)
							->first();

		if($emailComp!=null){
			$param['template']="Estimado Agente, adjunto la factura de la póliza para ser enviada al cliente.";
			$param['subject']="Factura <CUSTOMER>";
		}

		$to['address']=trim($agente->email);
		$to['name']=$agente->getFullNameAttribute();

		$customer=$policy->customer;
		$param["variables"]['TRAMITE_ID']=$this->procedure_entry_id;
		$param["variables"]['CUSTOMER']=$customer->getFullNameAttribute();
		//deberia ser policy_startDate
		$param["variables"]['POLICY_NUMBER']=$policy->policy_number;

		//get Attach Files
		$param["attachments"] = FileEntry::where("id",$invoiceId)
											->select('complete_path as pathToFile',
														'mime',
														'original_filename as display',
														'filename')
											->get()
											->toArray();

		$emailUtils=new EmailUtils();
		//the reason 'requestPolicyInsuranceCompany' is a generic template that is used to send the email where teh broker ask for the policy to the insuranceCompany
		$emailUtils->sendEmilProcess(
				$this->email_template_reason,
				$to,$param);
	}

	public function finish(){
		$procedure = $this->procedureEntryRel;
		$policy = $this->procedureEntryRel->policy;
		$quote = $policy->getPolicyQuote(1);
		
		$finish = true;
		$proEntries = $procedure->processEntry;
		foreach ($proEntries as $pro) {
			if( ($pro->isActive()) && ($pro->id != $this->id) ){
				$finish = false;
			}
		}

		if($finish){
			$policy->state = "active";
			$policy->save();
			$procedure->finish();
		} else{
			parent::finish();
		}
	}

	public function getResponsibleID($current=true){
		//Select a user that belong to the role 
		//and use automatic asignation
		return parent::getResponsibleID(true);
	}

}