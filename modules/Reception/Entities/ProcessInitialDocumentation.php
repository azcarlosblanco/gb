<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessInitialDocumentation extends ProcessEntry
{
	use \App\UploadAndDownloadFile;
	
	function __construct(){
		//call to method start of the
		parent::__construct(array(),'InitialDocumentation');
	}

	public function doProcess(Request $request){
		$data['name'] = $request['name'];
		$data['lastname'] = $request['lastname'];
		$data['identity_document'] = $request['identity_document'];
		$data['email'] = $request['email'];
		$data['mobile'] = $request['mobile'];
		$data['phone'] = $request['phone'];
		$data['agente_id'] = $request['agente_id'];
		$data['upload_cheque'] = $request['upload_cheque'];
		$data['plan_id'] = $request['plan_id'];
		$data['prev_insurance'] = $request['prev_insurance']; 

		if($data['prev_insurance']){
			$data['prev_insurance_comp'] = $request['prev_insurance_comp'];
		}


		RequestPolicyData::create(
								[
									'data'      => json_encode($data),
									'process_id'=> $this->id,
									'agente_id' => $data['agente_id'],
									'customer_fullname' =>
										$data['name']." ".$data['lastname'],
									'customer_identity' =>
										$data['identity_document'],
									'plan_id' => $data['plan_id']
								]
							);

		//print_r('Process Entry ID');
		//print_r($this->id);
		/*if($data['upload_cheque']==1){
			//create process that indicate reception has to upload cheque
			$pro=new ProcessSendCheckIC();
			$pro->start($this->procedureEntryRel);
			$pro->createSendDocument($this->id);
		}*/

		//create cron to handle multiple files
		$cron = new \App\CronTask();
		$cron->type = 1;
		$cron->action = '\Modules\Reception\Entities\ProcessInitialDocumentation::findAndFinish';
		$cron->table_type = 'procedure_entry';
		$cron->table_id = $this->procedure_entry_id;

		//validate file data format for this case
		$decoded = array();
		$data = $request->input('files', '');
		$data = (array)json_decode($data);
		foreach($data as $key=>$val){
			$decoded[$key] = (array)$val;
			if( !array_has($decoded[$key], 'name') || !array_has($decoded[$key], 'category') ){
				throw new \Exception('invalid file data format');
			}
		}

		$cron->data = $request->input('files', '');
		$cron->save();

		$this->createDir('newPolicy/'.$this->procedure_entry_id);
		return array('process_id'=>$this->id, 'cron_id'=>$cron->id);
	}//end doProcess

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

	public static function findAndFinish($procedure_id){
		try{
			\DB::beginTransaction();
			$procedure = \App\ProcedureEntry::findOrFail($procedure_id);
			$process = self::findProcess(
									$procedure->getLastActiveProcess()->id
								);
			$process->finish();
			\DB::commit();
			return true;
		}catch( \Exception $e ){
			\DB::rollback();
			return false;
		}
	}//end findAndFinish

}
