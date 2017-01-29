<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\ProcessCatalog;
use Modules\Reception\Entities\RequestPolicyData;
use Modules\Plan\Entities\Plan;
use Modules\Reception\Entities\ProcessSendDocumentsBD;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use JWTAuth;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessUploadSignedPolicy extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'UploadSignedPolicy');
	}

	public function createSendDocumentRegister(){
		$user = JWTAuth::parseToken()->authenticate();
		$uid = $user->id;
		$idCat=ProcessCatalog::where('name','InitialDocumentation')
						->first()
						->id;
		$iniID=ProcessEntry::where('process_catalog_id',$idCat)
						->where('procedure_entry_id',$this->procedure_entry_id)
						->first()
						->id;
		$pd=RequestPolicyData::where('process_id',$iniID)
								->first();
		$plan=Plan::find($pd->plan_id);
		$ic=$plan->insuranceCompany;

		$policy = $this->procedureEntryRel->policy;

		//create process to send documents to client
		$pro=new ProcessSendDocumentsBD();
		$pro->start($this->procedureEntryRel);
		
		$doc=SendDocument::create(
						[
							'reason'             => "Póliza #".$policy->policy_number." firmada a bd",
							'sender'             => $uid,
							'receiver_id'        => $ic->id, 
							'receiver_type'      => 'ic',
							'process_id'	     => $pro->id,
						]
					);

		$items=array();
		$items[]=[
					'description' => "Póliza # ".$policy->policy_number." firmada por el cliente",
					'num_copies'  => 1,
					'send_document_id' => $doc->id
				];
		SendDocumentItem::insert($items);
	}

	public function doProcess(Request $request){
		$policy=$this->procedureEntryRel->policy;
		$policy->state='send_signed_policy_bd';
		$policy->save();

		$this->createSendDocumentRegister();

		//subir poliza
		//the policy is saved inside the folder 
		//brokerID/policynumber
		$params['fieldname']='filefields';
		$params['table_type']='procedure_entry';
		$params['table_id']=$this->procedure_entry_id;
		$params['subfolder']='newPolicy/'.$this->procedure_entry_id;
		$params['multiple']=true;
		$params['description_files'][]='signed_policy';
		$result=$this->uploadFiles($request,$params);

	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

}