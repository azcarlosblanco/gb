<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\Claim;
use Modules\Claim\Entities\ClaimFile;
use Modules\Claim\Entities\ClaimProcedure;
use Modules\Reception\Entities\ProcessSettlementInit;
use Modules\Reception\Entities\ProcessSettlementUploadFiles;
use JWTAuth;


class SettlementController extends NovaController {

	use \App\UploadAndDownloadFile;
	function __construct(){
	    parent::__construct();
	}

	public function index(){
		return view('reception::index');
	}

	public function initSettlement($id){
		try{
			\DB::beginTransaction();

			//id is a valid Claim
			$claim = Claim::findOrFail($id);
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

			$cp = ClaimProcedure::create(
					[
						'claim_id' => $claim->id,
						'procedure_entry_id' => $procedure->id
					]
			);

			if( is_null($cp) ){
				throw new \Exception('error creating claim procedure');
			}

			//crear la carpeta en donde se subiran los archivos del proceso
			$this->createDir('settlements/'.$pid->id);
			$pid->finish();
			\DB::commit();
			$this->novaMessage->setData($pid->id);
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			\DB::rollback();
			//show message error
  		$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  		return $this->returnJSONMessage(404);
		}
	}//end initSettlement

	private function validateProcess($process_ID, $process_class){
		if( empty($process_ID) ){
			throw new \Exception('invalid process ID');
		}

		//check process exists and not finished
		$class = '\Modules\Reception\Entities\\'.$process_class;
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

		//valid claim exists is not finished
		$claim = $cp->claim;
		if($claim->status == 1){
			throw new \Exception('invalid claim');
		}

		return array('process'=>$process, 'procedure'=>$procedure, 'cp'=>$cp, 'claim'=>$claim);
	}

	public function listUploadedFiles($process_ID){
		try{
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementUploadFiles');
			extract($resp);

			//general info
			$data['process_id'] = $process_ID;
			$data['claim']['id'] = $claim->id;
			$ap = $claim->affiliatePolicy;
			$data['claim']['affiliate_name'] = $ap->affiliate->name;
			$data['claim']['policy_num'] = $ap->policy->policy_number;

			//list all files uploaded for this claim
			$files = \App\FileEntry::select('id', 'data', 'status', 'original_filename')
                                  ->where('table_type', 'procedure_entry')
                                   ->where('table_id', $procedure->id)
                                   ->get();

			$data['files']=array();
			foreach ($files as $key => $value) {
				$data['files'][$key]['id'] = $value['id'];
				$data['files'][$key]['name'] = $value['original_filename'];
				$file_data=(array)json_decode($value['data']);
				$data['files'][$key]['ts']
							= $file_data['ts'];
				$data['files'][$key]['descrip']
							= $file_data['description'];
				$data['files'][$key]['paycheck']
							= $file_data['paycheck'];
				$data['files'][$key]['status'] = $value['status'];
			}

			$this->novaMessage->setData($data);
	 		return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}
	}

	public function uploadSettlementFile(Request $request, $process_ID){
		$data = array();

		\DB::beginTransaction();
		try{
			$paycheck = $request->input('paycheck', 0);
			$description = $request->input('descrip', '');
			$ts = $request->input('ts', false);
      $table_type = 'procedure_entry';

			if( empty($ts) || empty($description) ){
				throw new \Exception('some data missing');
			}

			$process = ProcessSettlementUploadFiles::findProcess($process_ID);
	      	if( $process == null ){
	        	throw new \Exception('invalid process');
	      	}

	      	if( !$process->isActive() ){
	        	throw new \Exception('invalid process');
	      	}

	      	$table_id = $process->procedure_entry_id;

			$params = array();
	  		$params['fieldname'] = 'file';
	  		$params['subfolder'] = 'settlements/'.$process_ID;
	  		$params['table_type'] = $table_type;
	  		$params['table_id'] = $table_id;
	  		$params['data'] = json_encode(array('ts'=>$ts, 'description'=>$description, 'paycheck'=>$paycheck));
	  		$params['multiple'] = false;

      //removing - not necessary
			/*$fupentry = \App\FileUploadEntry::where('table_type', $table_type)
                                        ->where('table_id', $table_id)
                                        ->orderBy('id', 'desc')
                                        ->first();

      if( is_null($fupentry) ){
        throw new \Exception('no entry');
      }*/

			$uploadedFiles = $this->uploadFiles($request, $params);
			if( empty($uploadedFiles) ){
				throw new \Exception('no file uploaded');
			}

			$data['success'] = 1;
			$data['ts'] = $ts;
			\DB::commit();

			$this->novaMessage->setData($data);
  			return $this->returnJSONMessage(200);

		}catch( \Exception $e ){
			\DB::rollback();
			//show message error
  			$this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}
	}

}
