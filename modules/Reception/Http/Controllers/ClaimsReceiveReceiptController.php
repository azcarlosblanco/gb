<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Reception\Entities\ProcessClaimsReceiveReceipt;
use JWTAuth;

class ClaimsReceiveReceiptController extends NovaController {
    
    protected $module_path='reception/newClaims/uploadReceipt';


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
        $code=null;
        try{
            $pup=ProcessClaimsReceiveReceipt::findProcess($process_ID);
            //check process if the same time that we are requeted
            if($pup==null){
                $code = 404;
                throw new \Exception("Process with does id does not exist", 404);
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                $code = 400;
                throw new \Exception("Process is already complete", 400); 
            }

            $pup->doProcess($request);
            $pup->finish();

            $this->novaMessage->setRoute(
                    'reception/pending');
            $this->novaMessage->addSuccesMessage('Finalizado','Tramite ha sido completado');
            \DB::commit();
            $code=200;
        }catch(\Exception $e){
            if($code==null){
                $code=500;
            }
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
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
                                    'display' => 'Finalizar',
                                    'type'    => 'submit'
                                );
        return $form;
    }
}