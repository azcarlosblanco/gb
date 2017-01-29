<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\ProcedureEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\User;
use Modules\Authorization\Entities\Role;
use Illuminate\Http\Request;
use App\ProcessCatalog;
use Modules\Reception\Entities\RequestPolicyData;
use Modules\Agente\Entities\Agente;
use Modules\Reception\Entities\ProcessSendPolicyCustomer;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use JWTAuth;

class ProcessSendDocsReception extends ProcessEntry
{

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'SendDocsReception');
	}

	public function doProcess(Request $request){
		
		$uid = JWTAuth::parseToken()->authenticate()->id;

		$idCat=ProcessCatalog::where('name','InitialDocumentation')
						->first()
						->id;

		$iniID=ProcessEntry::where('process_catalog_id',$idCat)
						->where('procedure_entry_id',$this->procedure_entry_id)
						->first()
						->id;

		$pd=RequestPolicyData::where('process_id',$iniID)
								->first();
		$agente=Agente::find($pd->agente_id);

		//create process to send documents to client
		$pro=new ProcessSendPolicyCustomer();
		$pro->start($this->procedureEntryRel);
		
		$policy = $this->procedureEntryRel->policy;
		$policy->state = "send_docs_client";
		$policy->save();
		
		$doc=SendDocument::create(
						[
							'reason'             => 'papeles de la póliza '.$policy->policy_number,
							'sender'             => $uid,
							'receiver_id'        => $agente->id, 
							'receiver_type'      => 'agent',
							'process_id'	     => $pro->id,
						]
					);

		if($request['documents']==""){
			throw new \Exception("Usted debe seleccionar un archivo", 2);
		}

		$data=$request['documents'];
		$items_doc=explode("\n", $data);

		$items=array();
		foreach ($items_doc as $value) {
			$items[]=[
						'description' => $value,
						'num_copies'  => 1,
						'send_document_id' => $doc->id
					];
		}
		SendDocumentItem::insert($items);
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID(true);
	}
}