<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Entities\ProcessRequestAppNewPolicyBD;
use App\ProcessEntry;

class RequestAppNewPolicyBDController extends NovaController {
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID){
        try{
            $pup=ProcessRequestAppNewPolicyBD::
                        findProcess($process_ID);
            /*if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }*/
            $this->novaMessage->setData(
                            $this->renderForm($pup->id)
                        );
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

	public function requestAppNewPolicyBD($process_ID,Request $request){
		\DB::beginTransaction();
        try{
            $pup=ProcessRequestAppNewPolicyBD::findProcess($process_ID);
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
                    'emission/pending');
            return $this->returnJSONMessage(201);

        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

    public function renderForm($id){
        $form["url"]="emission/newPolicy/requestAppNewPolicyBD/$id";
        $form["method"]="POST";
        $form["title"]="Solicitar Poliza";
        $form['sections'][0]=[
                            'fields'=>array( 
                                    array(
                                            "label"=>"",
                                            "name"=>"mensaje",
                                            "type"=>"display",
                                            "disabled"=>1,
                                        )
                            )
                            ];
        $form['actions'][]=array(
                            'display' => 
                                'Solicitar Poliza',
                            'type'    => 'submit'
                            );
        
        $form['data_fields']['mensaje']='Enviar correo solicitando pòliza';
        return $form;
    }


}