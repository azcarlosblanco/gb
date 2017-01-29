<?php namespace Modules\Reception\Entities;

use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;
use Modules\Agente\Entities\Agente;
use Modules\Plan\Entities\Plan;
use App\ProcessEntry;
use App\ProcedureEntry;
use App\ProcessCatalog;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use Modules\GuiaEnvio\Entities\GuiaEnvio;
use Modules\GuiaEnvio\Entities\GuiaEnvioItem;
use JWTAuth;
use Modules\Payment\Entities\ChequePaymentDetail;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessSendCheckIC extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	private $email_template_reason
					='SendCheckIC';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'SendCheckIC');
	}

	public function uploadcheque(Request $request){
		//upload cheque
		if(count($request['filefields'])>0){
			//the files are saved inside the folder 
			//brokerID/procedureID
			$params['fieldname']='filefields';
			$params['table_type']='procedure_entry';
			$params['table_id']=$this->procedure_entry_id;
			$params['subfolder']='newPolicy/'.$this->procedure_entry_id;
			$params['multiple']=true;
			$params['description_files'][]='cheque_pago_poliza';
			$result=$this->uploadFiles($request,$params);
		}

		//send email with the cheque if this parameter is true
		if($request['send_email']){
			$this->sendEmail();
		}
	}

	public function createSendDocument($paymentID){
		$user = JWTAuth::parseToken()->authenticate();
		$uid=$user->id;

		$cp = ChequePaymentDetail::find($paymentID);

		$cheque_num   = $cp->cheque_num;
		$cheque_value = $cp->value;
		$bank_name    = $cp->bank_name;

		$plan=$this->procedureEntryRel->policy->getPlan();
		$ic=$plan->insuranceCompany;
		
		$doc=SendDocument::create(
						[
							'reason'           => 'cheque pago poliza',
							'sender'           => $uid,
							'receiver_id'      => $ic->id, 
							'receiver_type'    => 'ic',
							'process_id'	   => $this->id
						]
					);
		SendDocumentItem::create(
						[
							'description'      => 'cheque #'.$cheque_num.' por $'.$cheque_value.' del banco '.$bank_name,
							'num_copies'       => 1,
							'send_document_id' => $doc->id
						]
					);
	}

	public function doProcess(Request $request, $dataGuia){
		$data['date']=$dataGuia['date'];
        $data['track_number']=$dataGuia['track_number'];
        $data['reason']=$dataGuia['reason'];
        $data['sender']=$dataGuia['sender'];
        $data['receiver_name']=$dataGuia['receiver_name'];
        $data['receiver_address']=$dataGuia['receiver_address'];
        $data['receiver_phone']=$dataGuia['receiver_phone'];
        $data['carrier_id']=$dataGuia['carrier_id'];
        $data['external_track_number']=$dataGuia['external_track_number'];
        $data['foreign_id']=$dataGuia['foreign_id'];

        $dataItem=$dataGuia['guia_items'];

        //mark document as sent
        $doc=SendDocument::where('process_id',$this->id)
                    		->first();
        $doc->state='sent';
        $doc->save();

		//guardar guia en la base
		$guia=GuiaEnvio::create(
				$data
			);

		//guardar items de la guia en la base
		$items=array();
		foreach ($dataItem as $key => $item) {
			$items[]=['description'=>$item['description'],
					'num_copies' =>$item['num_copies'],
					'guia_remision_id'=> $guia->id];
		}

		GuiaEnvioItem::insert($items);

		//upload guia
		$params['fieldname']='filefields';
		$params['table_type']='procedure_entry';
		$params['table_id']=$this->procedure_entry_id;
		$params['subfolder']='newPolicy/'.$this->procedure_entry_id;
		$params['multiple']=true;
		$params['description_files'][]='guia_remision_db';
		$entryIDs=$result=$this->uploadFiles($request,$params);

		$guia->file_entry_id=$entryIDs[0];
		$guia->save();
	}

	public function sendEmail(){
		//get data from request_policy_data
		//InitialDocumentation
		$idCat=ProcessCatalog::where('name','InitialDocumentation')
								->first()
								->id;
		$processID=ProcessEntry::where('procedure_entry_id',$this->procedure_entry_id)
									->where('state','finished')
									->where('process_catalog_id', $idCat)
									->first()
									->id;
		$pd=RequestPolicyData::where('process_id',$processID)
								->first();

		$plan=Plan::find($pd->plan_id);
		$agente=Agente::find($pd->agente_id);

		//get email template of InsuranceCompany to which the process is related 
		$emailComp=InsuranceCompanyEmail::
							where('insurance_company_id',$plan->insurance_company_id)
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

		//copy to the agent that sold the policy and copy to the user that is doing the process
		$user = JWTAuth::parseToken()->authenticate();
		$param['cc'][]=array($user->email,$agente->email);

		$param["variables"]['AGENT_NAME']=$agente->getFullNameAttribute();

		$param["variables"]['TRAMITE_ID']=$this->procedure_entry_id;
		$param["variables"]['CUSTOMER']=$pd->customer_fullname;
		$param["variables"]['CUSTOMER_ID']=$pd->customer_identity;
		$param["variables"]['PLAN']=$plan->name;

		//get Attach Files
		$attachments = FileEntry::where('table_type','procedure_entry')
								->where('table_id',$procedureId)
								->select('complete_path as pathToFile',
									'mime',
									'original_filename as display',
									'filename')
								->where('description','cheque_pago_poliza')
								->get()
								->toArray();

		$param["attachments"]=$attachments;

		$emailUtils=new EmailUtils();
		//the reason 'requestPolicyInsuranceCompany' is a generic template that is used to send the email where teh broker ask for the policy to the insuranceCompany
		$emailUtils->sendEmilProcess(
				$this->email_template_reason,
				$to,
				$param);
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}
}