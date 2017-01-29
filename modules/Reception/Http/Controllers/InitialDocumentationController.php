<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Reception\Entities\ProcessInitialDocumentation;
use Modules\Reception\Entities\RequestPolicyData;
use Modules\Reception\Http\Requests\InitialDocumentationRequest;
use Modules\Agente\Entities\Agente;
use Modules\Plan\Entities\Plan;

class InitialDocumentationController extends NovaController {

  use \App\UploadAndDownloadFile;

  protected $module_path='reception/newPolicy';

	function __construct()
	{
        parent::__construct();
	}

  public function form($view=false,$data=null,$readonly=false,$files=null,$process_ID=null){
      $insurance_company_id = 1;
      $agentList=Agente::all()->pluck('fullName','id');
      $ppga = \Modules\Plan\Entities\PlanCategory::where('name','PPGA')
            ->where('insurance_company_id', $insurance_company_id)
            ->first();

      $planList=Plan::where('plan_category_id',$ppga->id)->pluck('name','id');

			$cid = \App\ProcedureCatalog::where('name','newpolicy')->value('id');
			$documentListDesc = array();
      $documentListName = array();

			if(!empty($cid)){
				$documentList = \App\ProcedureDocument::where('procedure_catalog_id', $cid)
				->select('id','description','name')->get();

        foreach ($documentList as $key => $value) {
          $documentListDesc[$value->id] = $value->description;
          $documentListName[$value->name] = $value->id;
        }
			}

			$form['agentList'] = $agentList;
			$form['planList'] = $planList;
			$form['documentList'] = $documentListDesc;
      $form['docListName'] = $documentListName;
			$form['data'] = $data;
      $form['files'] = $files;
      $form['process_id'] = $process_ID;

      $this->novaMessage->setData($form);
      return $this->returnJSONMessage();
  }

	public function initialDocumentation(InitialDocumentationRequest $request){
		\DB::beginTransaction();
		try{
			$pid=new ProcessInitialDocumentation();
			$pid->start();
			$resp = $pid->doProcess($request);
      //$pid->finish();

  		\DB::commit();
  		
      $this->novaMessage->setData($resp);
  		return $this->returnJSONMessage(200);
  	}catch(\Exception $e){
  		\DB::rollback();
  		//show message error
  		$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  		return $this->returnJSONMessage(404);
  	}
	}//end initial documentation


  public function uploadDocumentationFile(Request $request, $process_ID){
    $id = $process_ID;
    $data = array();

    \DB::beginTransaction();
    try{
      $category = $request->input('category', false);
      $description = $request->input('description', '');
      $ts = $request->input('ts', false);
      $cronid = $request->input('cron_id', false);
      $table_type = 'procedure_entry';

      $old_fid = $request->input('fid', false);

      if( empty($ts) || empty($category) || empty($cronid) ){
        throw new \Exception('some data missing');
      }

      $process = ProcessInitialDocumentation::findProcess($id);
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

      $policy = $process->procedureEntryRel->policy;
      $policy_number = ( !is_null($policy) ) ? $policy->policy_number : '';
      $data = array('procedure_id'=>$process->procedure_entry_id,
                    'process_id'=>$id,
                    'policy_number'=>$policy_number,
                    'ts'=>$ts);

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

      if( $uploadedFiles ){
          //check if all files r uploaded then finish process
          $data['success'] = 1;
      }//end if uploadedfiles

      \DB::commit();

      $this->novaMessage->setData($data);
      return $this->returnJSONMessage(200);
    }catch(\Exception $e){
      \DB::rollback();

      //show message error
      $this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
      return $this->returnJSONMessage(404);
    }
  }//end upload file


    public function view($process_ID, Request $request){
			$table_type = 'procedure_entry';
        try{
            $pup=ProcessInitialDocumentation::findProcess($process_ID);
            //check process if the same time that we are requeted
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);
            }

						$table_id = $pup->procedure_entry_id;

            $pd=RequestPolicyData::where('process_id',$process_ID)
                                ->first();

            $data = (Array)json_decode($pd->data);

						//files data
						$fupentry = \App\CronTask::where('table_type', $table_type)
			                                        ->where('table_id', $table_id)
			                                        ->orderBy('id', 'desc')
			                                        ->first();

            $fe=\App\FileEntry::where("table_type",$table_type)
                            ->where('table_id',$table_id)
                            ->get();

						$decoded = array();
			      $flist = $fupentry->data;
			  		$flist = (array)json_decode($flist);
			  		foreach($flist as $key=>$val){
			  			$decoded[$key] = (array)$val;
              $decoded[$key]['cron_id']=$fupentry->id;
              foreach($fe as $file){
                $fdata=(array)json_decode($file->data);
                if($fdata['ts']==$key){
                  $decoded[$key]['id']=$file->id;
                  $decoded[$key]['name']=$file->original_filename;
                  $decoded[$key]['description']=$file->description;
                  $decoded[$key]['category']=$fdata['procedure_document_id'];
                }
              }
			  		}

            return $this->form(true,$data,true, $decoded, $process_ID);
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
    }

	//View function
	public function renderForm($method,$url,$agentList,$planList,$documentList,$view=false,$data=null,$readonly=false){
		$form['method']=$method;
        $form['url']=$url;
        $form['files']=true;
        $form["title"]="Recepción - Nueva Póliza";
        $form['sections'][0]=[
                            'label' => 'Datos Iniciales',
                            'fields' => array(
                                array(
                                        'label' => 'Nombre',
                                        'name'  => 'name',
                                        'type'  => 'text',
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label' => 'Apellido',
                                        'name'  => 'lastname',
                                        'type'  => 'text',
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label' => 'Documento de Identidad',
                                        'name'  => 'identity_document',
                                        'type'  => 'text',
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label' => 'Email',
                                        'name'  => 'email',
                                        'type'  => 'email',
                                        'optional' => 1,
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label' => 'Celular',
                                        'name'  => 'mobile',
                                        'type'  => 'tel',
                                        'optional' => 1,
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label' => 'Teléfono Fijo',
                                        'name'  => 'phone',
                                        'type'  => 'tel',
                                        'optional' => 1,
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label'   => 'Agente',
                                        'name'    => 'agente_id',
                                        'type'    => 'select',
                                        'options' => $agentList,
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label'   => 'Plan',
                                        'name'    => 'plan_id',
                                        'type'    => 'select',
                                        'options' => $planList,
                                        'disabled' => $readonly
                                    ),
                                array(
                                        'label'   => 'Entrega Cheque',
                                        'name'    => 'upload_cheque',
                                        'type'    => 'checkbox',
                                        'options' => [ 1 => ''],
                                        'optional' => 1,
                                        'disabled' => $readonly
                                    ),
                                )
                            ];
        $form['sections'][1]=[
                            'label' => 'Cargar Documentos',
                            'fields' => array(
                                array(
                                        'label'    => 'Description',
                                        'for'      => 'filefields',
                                        'name'     => 'description_files[]',
                                        'type'     => 'select',
                                        'options'  => $documentList
                                    ),
                                array(
                                        'label'    => 'Documentos',
                                        'name'     => 'filefields[]',
                                        'type'     => 'file',
                                        'num_file' => 'n'
                                    )
                                )
                            ];
        $form['actions'][]=array(
                                    'display' => 'Guardar',
                                    'type'    => 'submit'
                                );
        $form['actions'][]=array(
                                    'display' => 'Cancelar',
                                    'type'    => 'href',
                                );
        $form['data_fields']=$data;
        return $form;
	}

}
