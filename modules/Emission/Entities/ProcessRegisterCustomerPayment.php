<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\FileEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Modules\Email\Entities\EmailUtils;
use Modules\Agente\Entities\Agente;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Plan\Entities\NumberPayments;
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
class ProcessRegisterCustomerPayment extends ProcessEntry
{
	use \App\UploadAndDownloadFile;
	private $email_template_reason
					='sendPaymentData';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ResgisterCustomerPayment');
	}

	public function doProcess(Request $request){
		
	}

	public function sendPaymentInfo($policy,$payment,$payment_method){
		$deducible=Deducible::with('plan')
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
		$param["variables"]['TRAMITE_ID']    = $this->procedure_entry_id;
		$param["variables"]['CUSTOMER']      = $customer->getFullNameAttribute();
		//deberia ser policy_startDate
		$param["variables"]['POLICY_NUMBER'] = $policy->policy_number;

		$pm = PaymentMethod::where("method",$payment_method)
								->first();
		$param["variables"]['PAYMENT_METHOD']  = $pm->display;
		$np = NumberPayments::find($policy->payments_number_id);
		$param["variables"]['NUMBER_PAYMENTS'] = $np->number;

		//email_template
		$param["template"] = $this->getEmailTemplate($payment_method,$payment);
		
		//get Attach Files
		$pdfid = \Modules\Payment\Entities\PaymentDetailFiles::
												where("table_type",$payment->getTable())
												->where("table_id",$payment->id)
												->pluck("file_entry_id");

		$param["attachments"] = FileEntry::whereIn("id",$pdfid)
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
						$to,
						$param
					);
	}

	private function getEmailTemplate($method,$payment){
		$template = "Estimado,\r\nEl cliente <CUSTOMER> de la póliza con númrero <POLICY_NUMBER> nos informa que va a pagar mediante <PAYMENT_METHOD> en <NUMBER_PAYMENTS> pagos.\r\n";
		$template .="Detalles del pago: \r\n";
		if($method=="cheque"){
			$template .="	* Número de Cheque: ".$payment->cheque_num."\r\n";
			$template .="	* Valor del Cheque: ".$payment->value."\r\n";
			$template .="	* Banco: ".$payment->bank_name."\r\n";
			$template .="Adjuntamos copia del cheque."; 
		}else if($method=="transfer"){
			$template .="	* Número de transferencia: ".$payment->transfer_num."\r\n";
			$template .="	* Valor de la  transferencia: ".$payment->value."\r\n";
			$template .="	* Nombre del titular: ".$payment->titular_account."\r\n";
			$template .="	* Nombre del Banco: ".$payment->bank_name."\r\n";
			$template .="	* Número del Cuenta: ".$payment->account_num_from."\r\n";
			$template .="Adjuntamos el comprobante de transferencia";
		}else if($method=="deposit"){
			$template .="	* Número de Depósito: ".$payment->desposit_num."\r\n";
			$template .="	* Valor del depósito: ".$payment->value."\r\n";
			$template .="	* Nombre del Banco: ".$payment->bank_name."\r\n";
			$template .="	* Número de Cuenta : ".$payment->account_num."\r\n";
			$template .="Adjuntamos el comprobante de depósito";
		}else if($method=="creditcard"){
			$template .="	* Marca de la Tarjeta: ".$payment->creditCardBrand->display_name."\r\n";
			$template .="	* Tipo de la Tarjeta: ".$payment->creditCardType->display_name."\r\n";
			$template .="Adjuntamos el documento de authorización de tarjeta de crédito";
		}
		return $template;
	}

	public function getResponsibleID($current=true){
		//Select a user that belong to the role 
		//and use automatic asignation
		return parent::getResponsibleID(true);
	}

}