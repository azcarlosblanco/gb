<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Entities\ProcessRegisterCustomerResponse;
use App\ProcessEntry;
use Illuminate\Http\Request;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\Deducible;

class RegisterCustomerResponseController extends NovaController {
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID){
		try{
            $pup=ProcessRegisterCustomerResponse::
            			findProcess($process_ID);
            
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }

            $data["process_ID"]=$process_ID;
            $data["catalog"]=array("response"=>["yes"=>"Sí","no"=>"No"]); 
            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

	public function registerResponse($process_ID,Request $request){
		\DB::beginTransaction();
        try{
            $pup=ProcessRegisterCustomerResponse::findProcess($process_ID);
            if($pup==null){
                throw new Exception("Process with does id does not exist", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new Exception("Process is already complete", 1); 
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
        $form["url"]="emission/newPolicy/registerCustomerResponse/$id";
        $form["method"]="POST";
        $form["title"]="Emisión - Registrar Respuesta Cliente";
        $form['sections'][0]=[
                            'label' => "Respuesta del Cliente",
                            'fields'=>array( 
                                array(
                                        "label"=>"El cliente Desea la Póliza",
                                        "name"=>"response",
                                        "type"=>"select",
                                        'options' => 
                                        		['yes'=>'Sí',
                                        		'no'=>'No'], 
                                    ),
                                )
                            ];
        $form['actions'][]=array(
                                'display' => 'Registrar Respuesta',
                                'type'    => 'submit'
                            );
        return $form;
	}
	
}