<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Modules\Reception\Entities\InitialDocumentationData;
use Illuminate\Http\Request;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessUploadReceipt extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'UploadReceipt');
	}

	public function doProcess(Request $request){
		$policy=$this->procedureEntryRel->policy;
		$policy->state='active';
		$policy->save();

		//subir recibo de recibido de bd
		$params['fieldname']='filefields';
		$params['table_type']='procedure_entry';
		$params['table_id']=$this->procedure_entry_id;
		$params['subfolder']='newPolicy/'.$this->procedure_entry_id;
		$params['multiple']=true;
		$params['description_files'][]='receipt_bd';
		$result=$this->uploadFiles($request,$params);

	}

	//porque

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

}