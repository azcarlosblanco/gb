<?php namespace Modules\Claim\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Illuminate\Http\Request;
use App\User;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use App\ProcedureEntry;
use Modules\Reception\Entities\ProcessClaimsSendDocsBD;
use JWTAuth;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessClaimsPrintLetter extends ProcessEntry
{
	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ClaimsPrintLetter');
	}

	public function sendDocumentsReception(Request $request){
		$uid=JWTAuth::parseToken()->authenticate()->id;

		$policy = $this->procedureEntryRel->policy;
		$plan = $policy->getPlan();
		$ic = $plan->insuranceCompany;

		//create process to send documents to BD
		$pro=new ProcessClaimsSendDocsBD();
		$pro->start($this->procedureEntryRel);
		
		$doc=SendDocument::create(
						[
							'reason'             => 'papeles reclamos',
							'sender'             => $uid,
							'receiver_id'        => $ic->id, 
							'receiver_type'      => 'ic',
							'process_id'	     => $pro->id
						]
					);

		//get list files i have to send
		$peid=$this->procedure_entry_id;
        $claims=ProcedureEntry::find($peid)
                        ->claims()
                        ->with('affiliatePolicy')
                        ->get();

        $documentType = \App\ProcedureDocument
                    		::pluck('description','id');

		$items=array();
		foreach ($claims as $claim) {
            //claim_file
            $cfs=$claim->files;
            foreach ($cfs as $cf) {
                //get detail of files
                if($documentType[$cf->procedure_document_id]=="Factura"){
                	$desc = "Póliza ".$policy->policy_number.": ".$documentType[$cf->procedure_document_id]." ".$cf->description.", valor=".$cf->amount;
        		}else{
        			$desc = "Póliza ".$policy->policy_number.": ".$documentType[$cf->procedure_document_id]." ".$cf->description;
        		}
                $items[]=[
                		'description' => $desc,
						'num_copies'  => 1,
						'send_document_id' => $doc->id,
                    ];
            }
        }
		SendDocumentItem::insert($items);
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID(true);
	}
}