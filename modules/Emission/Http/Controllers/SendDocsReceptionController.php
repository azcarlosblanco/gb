<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Entities\ProcessSendDocsReception;
use App\ProcessEntry;

class SendDocsReceptionController extends NovaController {
	
	protected $module_path='emission/newPolicy/sendDocsRec';
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID){
        try{
            $pup=ProcessSendDocsReception::
                        findProcess($process_ID);
            $policy = $pup->procedureEntryRel->policy;

            if($pup==null){
                throw new \Exception("Proceso con ese id no existe", 1);  
            }
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Proceso ya se termino", 1); 
            }

        	$data['documents']=implode("\n", $this->getListDocs($policy));

            $this->novaMessage->setData(
                            $this->renderForm
                            	(
                            		$pup->id,
                            		$data
                            	)
                        );

            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

	private function getListDocs($policy){
		$docs[]="Póliza #".$policy->policy_number.": Anexo B, Cuadro de beneficios máximos (Original y Copia)";
		$docs[]="Póliza #".$policy->policy_number.": Cobertura (Original y Copia)";
		$docs[]="Póliza #".$policy->policy_number.": Anexo A, Condiciones Particulares (Original y Copia)";
		$docs[]="Póliza #".$policy->policy_number.": Condiciones Generales de Afiliación (Original y Copia)";
		$docs[]="Póliza #".$policy->policy_number.": Tarjeta de Membresía ( <<N>> )";
		return $docs;
	}

	public function printLetter($process_ID,Request $request){
		try{
			$pup=ProcessSendDocsReception::findProcess($process_ID);
            //check process if the same time that we are requeted
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);
            }

            //get the data to generate the data for the letter send together with the policy	
            //date
            //customer_title
            //customer_name
            //plan_name
            //policy_num
            //num_cards	
            $policy=$pup->procedureEntryRel->policy;
            $plan_name=$policy->getPlan()->value('name');
            $data['date']=strftime("%B %d del %Y");
            $customer=$policy->customer;
            $data['customer_name']=$customer->full_name;
            $data['customer_title']='Senor';
            $data['policy_num']=$policy->policy_number;
            $data['plan_name']=$plan_name;
            $data['list_docs']=explode("\n", $request['documents']);

            $pdf = \PDF::loadView('emission::letter_delivery_policy', $data);
            return $pdf->download('letter_delivery_policy.pdf');

    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
    		$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
    		return $this->returnJSONMessage(500);
    	}
	}

	public function process($process_ID,Request $request){
		\DB::beginTransaction();
        try{
            $pup=ProcessSendDocsReception::findProcess($process_ID);
            if($pup==null){
                throw new Exception("No existe proceso con ese ID", 1);
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new Exception("Procese está ya completo", 1); 
            }

            $pup->doProcess($request);
            $pup->finish();

            \DB::commit();
            $this->novaMessage->setRoute(
                    'emission/pending');
            return $this->returnJSONMessage(200);

        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

    public function renderForm($id,$data){
        $form["url"]=$this->module_path."/$id";
        $form["method"]="POST";
        $form["title"]="Enviar proceso recepción";
        $form['sections'][0]=[
                            'label' => 'Documentos a Enviar',
                            'fields' => array(
                                array(
                                        'label'    => 'Documentos',
                                        'name'     => 'documents',
                                        'type'     => 'textarea',
                                        'num_rows' => '7'
                                    ),
                                )
                        ];
        $form['sections'][1]=[
                            'label' => 'Imprimir Carta',
                            'fields' => array(
                                array(
                                        'label'    => 'Imprimir Carta',
                                        'name'     => 'print_letter',
                                        'type'     => 'link',
                                        'link' => "/".$this->module_path."/$id/printLetter"
                                    ),
                                )
                        ];
        $form['actions'][]=array(
                            'display' => 'Enviar Proceso Recepción',
                            'type'    => 'submit'
                            );
        $form['data_fields']=$data;
        return $form;
    }
}