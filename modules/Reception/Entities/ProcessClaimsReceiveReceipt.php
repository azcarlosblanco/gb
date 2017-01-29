<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Illuminate\Http\Request;
use App\User;
use App\ProcedureEntry;
use Modules\Claim\Entities\ClaimProcedure;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessClaimsReceiveReceipt extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the
		parent::__construct(array(),'ClaimsReceiveReceipt');
	}

	public function doProcess(Request $request){
		//subir recibo de recibido de bd
		$params['fieldname']='filefields';
		$params['table_type']='procedure_entry';
		$params['table_id']=$this->procedure_entry_id;
		$params['subfolder']='newClaims/'.$this->procedure_entry_id;
		$params['multiple']=true;
		$params['description_files'][]='receipt_bd';
		$result=$this->uploadFiles($request,$params);
	}

	public function finish(){
		//finish this process
		parent::finish();

		//get the claims that are associated with the procedure
		$peid=$this->procedure_entry_id;
		$policy_id=$this->procedureEntryRel->policy->id;
	    $claims=ProcedureEntry::find($peid)
	                                ->claims;

	    //start a settlement procedure for each claim
	    foreach ($claims as $claim) {
			if( $claim->hasActiveSettlement() ){
				throw new \Exception('already started');
			}

			$pid = new ProcessSettlementInit();
			if( is_null($pid) ){
				throw new \Exception('process does not exist');
			}

			$pid->start();

			$procedure = $pid->procedureEntryRel;
			if( is_null($procedure) ){
				throw new \Exception('procedure does not exist');
			}
			$procedure->policy_id = $policy_id;
			$procedure->save();

			$cp = ClaimProcedure::create(
					[
						'claim_id' => $claim->id,
						'procedure_entry_id' => $procedure->id
					]
			);

			if( is_null($cp) ){
				throw new \Exception('error creating claim procedure');
			}

			$pid->finish();
   		}
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}
}
