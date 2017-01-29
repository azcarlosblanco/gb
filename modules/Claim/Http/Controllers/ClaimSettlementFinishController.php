<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\ClaimSettlement;
use Modules\Claim\Entities\ClaimSettlementRefund;
use Modules\Claim\Entities\ClaimFile;
use Modules\Claim\Entities\ClaimProcedure;

use Modules\Email\Entities\EmailUtils;

class ClaimSettlementFinishController extends NovaController {

	function __construct(){
    	parent::__construct();
	}

	private function validateProcess($process_ID, $process_class){
		if( empty($process_ID) ){
			throw new \Exception('invalid process ID');
		}

		//check process exists and not finished
		$class = '\Modules\Claim\Entities\\'.$process_class;
		$process = $class::findProcess($process_ID);
		if( is_null($process) ){
			throw new \Exception('invalid process');
		}

		if( !$process->isActive() ){
			throw new \Exception('invalid process');
		}

		$procedure = $process->procedureEntryRel;

		//validate claim and procedure association exists
		$cp = ClaimProcedure::where('procedure_entry_id', $procedure->id)->first();

		if( empty($cp) ){
			throw new \Exception('invalid claim-procedure');
		}

		//validate claim exists is not finished
		$claim = $cp->claim;
		if($claim->status == 1){
			throw new \Exception('invalid claim');
		}

		//validate file ids exist and belong to current procedure

		return array('process'=>$process, 'procedure'=>$procedure, 'cp'=>$cp, 'claim'=>$claim);
	}//end validateProcess

	public function finishSettlement($process_ID){
		try{
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementFinish');
			extract($resp);

			//check process exists and not finished
			$class = '\Modules\Claim\Entities\ProcessSettlementFinish';
			$process = $class::findProcess($process_ID);

			$procedure = $process->procedureEntryRel;

			//check all settlement active processes and "finish them!(fatality)" lol
			$processes = $procedure->getCurrentProcesses();
			foreach ($processes as $p) {
				$p->finish();
			}

			$this->novaMessage->setData(array('id'=>$process_ID));
			$this->sendFinishedSettlement($process,$process_ID);
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			//show message error
  			$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}//end finishSettlement

	public function sendFinishedSettlement($process,$process_ID){

		$userRef = $process->getResponsibleID($process_ID);
		$user    = User::find($userRef);

		$to['address'] = $user->email;
		$to['name'] = $user->name;

		$reason = "notifyFinishedSettlement";

		$param['template']="Estimado <USER>, a continuacion se le presenta el monto liquidado. \n\n Monto: <AMOUNT>";
		$param['subject']="Liquidacion # <TRAMITE_ID>";
		$param["variables"]['TRAMITE_ID']=   $process_ID;
		$param["variables"]['USER']=    $user->name;
		$param['variables']['AMOUNT']=       $amount;

		$emailUtils=new EmailUtils();

		$emailUtils->sendEmilProcess(
				$reason,$to,$param);

	}

}
