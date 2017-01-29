<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Reception\Entities\ProcessClaimsInit;
use Modules\Policy\Entities\Policy;
use Modules\Claim\Entities\ClaimFile;
use JWTAuth;
use Modules\Authorization\Entities\Role;
use App\Currency;

class ClaimsInitController extends NovaController {

    use \App\UploadAndDownloadFile;
    protected $module_path='reception/newClaims';

	function __construct()
	{
        parent::__construct();
	}

  public function form(){
      $data['policies'] = Policy::active()->pluck('policy_number','id');

			$cid = \App\ProcedureCatalog::where('name','claims')->value('id');
			$data['categories'] = array();

			if(!empty($cid)){
				$data['categories'] = \App\ProcedureDocument::where('procedure_catalog_id', $cid)
				->select('id', 'name', 'description')
				->get();
			}

      $data['currency'] = Currency::pluck("display_name","id");

      $this->novaMessage->setData($data);
      return $this->returnJSONMessage(200);
  }

	public function claimsInit(Request $request){
		\DB::beginTransaction();
		try{
			//validate policy id exists
			$id = $request->input('pid', '');
			$num_files = intval($request->input('num_files', 0));

			if( $num_files < 1 ){
				throw new \Exception('no files selected',400);
			}

			if( empty($id) ){
				throw new \Exception('Póliza no existe',400);
			}

			$policy = Policy::find($id);
      if($policy==null){
        throw new \Exception('Póliza no existe',404);
      }

      if(!$policy->isActive()){
        throw new \Exception('La Póliza no está activa',422);
      }


			$pid = new ProcessClaimsInit();
			$pid->start();

			$procedure = $pid->procedureEntryRel;
			if( is_null($procedure) ){
				throw new \Exception('Trámite no existe',404);
			}

			$procedure->policy_id = $id;
			$procedure->save();

			//create cron to handle multiple files
      $cron = new \App\CronTask();
      $cron->type = 1;
      $cron->action = '\Modules\Reception\Entities\ProcessClaimsInit::findAndFinish';
      $cron->table_type = 'procedure_entry';
      $cron->table_id = $procedure->id;

      //validate file data format for this case
      $decoded = array();
      $data = $request->input('files', '');
      $data = (array)json_decode($data);
      foreach($data as $key=>$val){
        $decoded[$key] = (array)$val;
        if( !array_has($decoded[$key], 'name') || !array_has($decoded[$key], 'category') ){
          throw new \Exception('La petición no es válida',400);
        }
      }

      $cron->data = $request->input('files', '');
      $cron->save();

  		\DB::commit();
  		//$this->novaMessage->setRoute('reception/pending');
      //avoid concurrency issue when trying to create dir
      $this->createDir('newclaims/'.$pid->id);
  		$this->novaMessage->setData(array('process_id'=>$pid->id, 'cron_id'=>$cron->id));
  		return $this->returnJSONMessage(200);
  	}catch(\Exception $e){
  		\DB::rollback();
  		//show message error
  		$this->novaMessage
              ->addErrorMessage($e->getCode(),$e->getMessage());
  		return $this->returnJSONMessage(500);
    }
	}

	public function uploadClaimFile(Request $request, $id){
    $create_failed = false;
    $data = array();

		\DB::beginTransaction();
		try{
			$category = $request->input('category', false);
			$description = $request->input('description', '');
      $currency = $request->input('currency', '');
      $amount = $request->input('amount', 0);
			$ts = $request->input('ts', false);
      $cronid = $request->input('cron_id', false);
      $table_type = 'procedure_entry';

      $old_fid = $request->input('fid', false);

      if( empty($ts) || empty($category) || empty($cronid) ){
				throw new \Exception('some data missing');
			}

			//$process = ProcessClaimsInit::findOrFail($id);
			$process = ProcessClaimsInit::findProcess($id);
      if( $process == null ){
        throw new \Exception('invalid claim');
      }

      if( !$process->isActive() ){
        throw new \Exception('invalid process');
      }

      $table_id = $process->procedure_entry_id;

      //TODO validate category id belongs to process id
      \App\ProcedureDocument::findOrFail($category);

      $params = array();
  		$params['fieldname'] = 'file';
  		$params['subfolder'] = 'newclaims/'.$id;
  		$params['table_type'] = $table_type;
  		$params['table_id'] = $table_id;
      $params['cronid'] = $cronid;
  		$params['data'] = json_encode(array('ts' => $ts, 
                                          'procedure_document_id' => $category,
                                          'currency' => $currency,
                                          'amount' => $amount,
                                          'valid' => 1,
                                          'usa' => 0,
                                          )
                                    );
  		$params['multiple'] = false;

      //TODO should use cronid
      $fupentry = \App\CronTask::where('table_type', $table_type)
                                        ->where('table_id', $table_id)
                                        ->orderBy('id', 'desc')
                                        ->first();

      if( is_null($fupentry) ){
        throw new \Exception('no entry');
      }

      $policy = $process->procedureEntryRel->policy;
      $policy_number = ( !is_null($policy) ) ? $policy->policy_number : '';
      $data = array('procedure_id' => $process->procedure_entry_id,
                    'process_id'   => $id,
                    'policy_number'=> $policy_number,
                    'ts'           => $ts);

      try{
        if( $old_fid ){
          //if get file id try to update
          $updated = $this->updateFile($request, $old_fid, $params);
          $uploadedFiles = array($updated);
          //$claim_file = ClaimFile::firstOrNew(['procedure_entry_id' => $table_id, 'file_entry_id'=>$old_fid]);
        }
        else{
          $uploadedFiles = $this->uploadFiles($request, $params);
          //$claim_file = new ClaimFile();
        }

      }catch(\Exception $e){
        if(!$old_fid){
          $create_failed = true;
        }
        throw $e;
      }

      if( $uploadedFiles ){
          //check if all files r uploaded then finish process
          $data['success'] = 1;
      }//end if uploadedfiles

			\DB::commit();

      $this->novaMessage->setData($data);
  		return $this->returnJSONMessage(200);
		}catch(\Exception $e){
			\DB::rollback();

      if( $create_failed ){
        //create FileEntry as status=2 (with error) to reupload later
        $e_params['status'] = 2;
        $e_params['data'] = $ts;
        $e_params['cronid'] = $cronid;
        $e_fentry = new \App\FileEntry();
        $e_fentry->saveWithDefaults($e_params);

        $data['success'] = 0;
        $this->novaMessage->setData($data);
      }

  		//show message error
  		$this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
  		return $this->returnJSONMessage(500);
		}
	}

  public function view($id){
    $table_type = 'procedure_entry';

    try{
      $process = ProcessClaimsInit::findProcess($id);
      if( $process == null ){
        throw new \Exception('invalid claim');
      }

      $table_id = $process->procedure_entry_id;
      $fupentry = \App\CronTask::where('table_type', $table_type)
                                        ->where('table_id', $table_id)
                                        ->orderBy('id', 'desc')
                                        ->first();

      if( is_null($fupentry) ){
        throw new \Exception('no entry');
      }

      $cid = \App\ProcedureCatalog::where('name','claims')->value('id');
			$data['categories'] = array();

			if(!empty($cid)){
				$data['categories'] = \App\ProcedureDocument::where('procedure_catalog_id', $cid)
				->select('id', 'name', 'description')
				->get();
			}

      $data['currency'] = Currency::pluck("display_name","id");

      $policy = $process->procedureEntryRel->policy;
      if( !is_null($policy) ){
        $data['policy_id'] = $policy->id;
        $data['policy_number'] = $policy->policy_number;
      }
      else{
        $data['policy_id'] = 0;
        $data['policy_number'] = 0;
      }

      $data['process_id'] = $id;
      $data['process_status'] = $process->state;

      $decoded = array();
      $flist = $fupentry->data;
  		$flist = (array)json_decode($flist);
  		foreach($flist as $key=>$val){
  			$decoded[$key] = (array)$val;
        $decoded[$key]['cron_id'] = $fupentry->id;
  		}

      $files = \App\FileEntry::select('id', 'data', 'status')
                                  ->where('table_type', $table_type)
                                   ->where('table_id', $table_id)
                                   ->get();
      $overall_status = 1;
      $fcount = 0;
      foreach( $files as $file ){
        $tmp_data = (array)json_decode($file->data);
        $ts = array_get($tmp_data, 'ts', '');

        if( empty($ts) ){
          continue;
        }

        $decoded[$ts]['status'] = $file->status;
        $decoded[$ts]['id'] = $file->id;

        if( $file->status == 2 ){
          $overall_status = 2;
        }

        $fcount++;
      }

      if( $fcount < $fupentry->expected ){
        $overall_status = 0;
      }

      $data['files'] = $decoded;
      $data['state'] = $overall_status;
      $data['testcount'] = $this->countFilesInDir('newclaims/'.$id);
      $this->novaMessage->setData($data);
      return $this->returnJSONMessage(200);
    }catch(\Exception $e){
      $this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
  		return $this->returnJSONMessage(404);
    }
  }

}
