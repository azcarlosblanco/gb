<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Reception\Entities\ProcessUploadReceipt;
use JWTAuth;

class UploadReceiptController extends NovaController {
	
    protected $module_path='reception/newPolicy/uploadReceipt';


	function __construct()
	{
        parent::__construct();
	}

    public function form($process_ID){
        $this->novaMessage->setData(
            $this->renderForm(
                'POST',
                $this->module_path."/$process_ID"       )
            	);
        return $this->returnJSONMessage();
    }

	public function upload($process_ID, Request $request){
		\DB::beginTransaction();
		try{
            $pup=ProcessUploadReceipt::findProcess($process_ID);
            //check process if the same time that we are requeted
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }

			$pup->doProcess($request);
            $pup->finish();

    		\DB::commit();
    		$this->novaMessage->setRoute(
    				'reception/pending');
    		$this->novaMessage->addSuccesMessage('Finalizado','Tramite ha sido completado');
    		return $this->returnJSONMessage(201);
    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
    		$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
    		return $this->returnJSONMessage(404);
    	}
	}

	//View function
	private function renderForm($method,$url){
		$form['method']=$method;
        $form['url']=$url;
        $form['files']=true;
        $form["title"]="Subir Acuse de Recibido";
        $form['sections'][0]=[
                            'fields' => array(
                                array(
                                        'label'    => 'Acuse de Recibido',
                                        'name'     => 'filefields[]',
                                        'type'     => 'file',
                                        'num_file' => '1'
                                    )
                                )
                        ];
        $form['actions'][]=array(
                                    'display' => 'Continuar',
                                    'type'    => 'submit'
                                );
        return $form;
	}
}