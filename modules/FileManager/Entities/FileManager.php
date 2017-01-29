<?php namespace Modules\Filemanager\Entities;

use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;

class FileManager {
  protected $type;
  protected $data;
  protected $table_type;
  protected $table_id;
  protected $ref_id;
  protected $action;

  use \App\UploadAndDownloadFile;

  function __construct($type, $table_type='', $table_id=''){
    $this->type = $type;
    $this->table_type = $table_type;
    $this->table_id = $table_id;
    $this->data = '';
    $this->ref_id = null;
	}

  function setRefID($val){
    $this->ref_id = $val;
  }

  function setData($val){
    $this->data = $val;
  }

  function uploadWithCron($action=null){
    $cron = \App\CronTask::where('type', $this->type)
                         ->where('table_type', $this->table_type)
                         ->where('table_id', $this->table_id)
                         ->first()
    $cron = new \App\CronTask();
		$cron->type = 1;
		$cron->action = '\Modules\Reception\Entities\ProcessInitialDocumentation::findAndFinish';
		$cron->table_type = 'procedure_entry';
		$cron->table_id = $this->procedure_entry_id;
    $cron->save();

    $this->createDir('newPolicy/'.$this->procedure_entry_id);


    $params = array();
    $params['fieldname'] = 'file';
    $params['subfolder'] = 'newPolicy/'.$table_id;
    $params['table_type'] = $table_type;
    $params['table_id'] = $table_id;
    $params['cronid'] = $cronid;
    $params['data'] = json_encode(array('ts'=>$ts,
                                        'procedure_document_id'=>$category,
                                        'process_id' => $id));
    $params['multiple'] = false;

    //TODO should use cronid
    $fupentry = \App\CronTask::where('table_type', $table_type)
                                      ->where('table_id', $table_id)
                                      ->orderBy('id', 'desc')
                                      ->first();

    if( is_null($fupentry) ){
      throw new \Exception('no entry');
    }


    try{
      if( $old_fid ){
        //if get file id try to update
        $updated = $this->updateFile($request, $old_fid, $params);
        $uploadedFiles = array($updated);
        //$claim_file = ClaimFile::firstOrNew(['procedure_entry_id' => $table_id, 'file_entry_id'=>$old_fid]);
      }
      else{
        $uploadedFiles = $this->uploadFiles($request, $params);
      }

    }catch(\Exception $e){
      throw $e;
    }

  }//end upload

}//end class
