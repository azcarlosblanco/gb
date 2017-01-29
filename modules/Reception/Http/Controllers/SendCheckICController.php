<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Reception\Entities\ProcessSendCheckIC;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use Modules\GuiaEnvio\Entities\GuiaEnvio;
use Modules\GuiaEnvio\Entities\GuiaEnvioItem;
use Modules\GuiaEnvio\Entities\Carrier;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\Agente\Entities\Agente;
use Modules\Reception\Http\Requests\PrintGuiaRequest;
use JWTAuth;

class SendCheckICController extends NovaController {
	
    protected $module_path='reception/newPolicy/sendCheckIC';


	function __construct()
	{
        parent::__construct();
	}

    public function form($process_ID){
        $carries=Carrier::pluck('full_name','id');

        //obtener la informacion de la guia de la tabla documents
        $header=SendDocument::with('items')
                    ->where('process_id',$process_ID)
                    ->first();

        if($header->receiver_type=='ic'){
            $ic=InsuranceCompany::find($header->receiver_id);
            $off=$ic->offices()
                    ->where('default',1)
                    ->first();
            $data['receiver_name']=$ic->company_name;
            $data['receiver_address']=$off->address;
            $data['receiver_phone']="";
        }elseif($header->receiver_type=='agent'){
            $agent=Agente::find($header->receiver_id);
            $data['receiver_name']=$agent->getFullNameAttribute();
            $data['receiver_address']=$agent->address;
            $data['receiver_phone']=$agent->mobile;
        }else{
            $data['receiver_name']="";
            $data['receiver_address']="";
            $data['receiver_phone']="";
        }

        $carries=Carrier::pluck('full_name','id');
        $this->novaMessage->setData(
            $this->renderForm(
                'POST',
                $this->module_path."/$process_ID",
                $process_ID,
                $carries,
                $data
                )
            );
        return $this->returnJSONMessage();
    }

    public function printGuia($process_ID,Request $request){
        try{
            $pup=ProcessSendCheckIC::findProcess($process_ID);
            //check process if the same time that we are requeted
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            
            $data=$this->getDataGuia($pup,$request);
            $pdf = \PDF::loadView('reception::guia', $data);
            return $pdf->download('guia_remision_'.$data['track_number'].'.pdf');  

        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
            return $this->returnJSONMessage(500);
        }
    }

    public function getDataGuia($pup,$request){
        $carries=Carrier::all()
                    ->keyBy('id')
                    ->toArray();

        //obtener la informacion de la guia de la tabla documents
        $header=SendDocument::with('items')
                    ->where('process_id',$pup->id)
                    ->first();

        $items=$header->items;

        if($header->receiver_type=='ic'){
            $ic=InsuranceCompany::find($header->receiver_id);
            $off=$ic->offices()
                    ->where('default',1)
                    ->first();
            $data['receiver_name']=$ic->company_name;
            $data['receiver_address']=$off->address;
            $data['receiver_phone']="";
        }elseif($header->receiver_type=='agent'){
            $agent=Agente::find($header->receiver_id);
            $data['receiver_name']=$agent->getFullNameAttribute();
            $data['receiver_address']=$agent->address;
            $data['receiver_phone']=$agent->mobile;
        }else{
            $data['receiver_name']="";
            $data['receiver_address']="";
            $data['receiver_phone']="";
        }

        $data['receiver_name']=$request['receiver_name']!=''?$request['receiver_name']:$data['receiver_name'];
        $data['receiver_address']=$request['receiver_address']!=''?$request['receiver_address']:$data['receiver_address'];
        $data['receiver_phone']=$request['receiver_phone']!=''?$request['receiver_phone']:$data['receiver_phone'];

        //TODO: this will fail is there amore than one person creating guides at teh same time
        $maxid = \DB::table('guia_remision')->max('id');
        if ($maxid ==null){
            $maxid=1;
        }

        $data['date'] = date('Y-m-d');
        $data['track_number'] = $maxid;
        $data['reason'] = 'send_cheque_bd';
        $user = JWTAuth::parseToken()->authenticate();
        $data['sender'] = $user->id;
        $data['foreign_id'] = $pup->procedure_entry_id;
        $data['external_track_number' ]= "";

        //carrier
        $data['carrier_id']=$request['carrier_id'];
        $data['mensajero_name']=$carries[$request['carrier_id']]['full_name'];
        $data['mensajero_id']=$carries[$request['carrier_id']]['identification'];

        //items
        $data['guia_items']=array();
        $index=0;
        foreach ($items as $key => $item) {
            $data['guia_items'][$index]['description']=$item->description;
            $data['guia_items'][$index]['num_copies']=$item->num_copies;
            $index++;
        }
        return $data;
    }

	public function uploadGuia($process_ID, PrintGuiaRequest $request){
		\DB::beginTransaction();
		try{
            $pup=ProcessSendCheckIC::findProcess($process_ID);
            //check process if the same time that we are requeted
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }

			$dataGuia=$this->getDataGuia($pup,$request);
            $pup->doProcess($request,$dataGuia);
            $pup->finish();

    		\DB::commit();
    		$this->novaMessage->setRoute(
    				'reception/pending');
    		$this->novaMessage->addSuccesMessage('Finalizado','El cheque ha sido enviado');
    		return $this->returnJSONMessage(200);
    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    		return $this->returnJSONMessage(500);
    	}
	}

	//View function
	private function renderForm($method,$url,$id,$carries,$data){
		$form['method']=$method;
        $form['url']=$url;
        $form['files']=true;
        $form["title"]="Registrar envío de cheque";
        $form['sections'][0]=[
                            'label' => 'Datos Destinatario',
                            'fields' => array(
                                array(
                                        'label'    => 'Nombre',
                                        'name'     => 'receiver_name',
                                        'type'     => 'text',
                                    ),
                                array(
                                        'label'    => 'Dirección',
                                        'name'     => 'receiver_address',
                                        'type'     => 'text',
                                    ),
                                array(
                                        'label'    => 'Teléfono',
                                        'name'     => 'receiver_phone',
                                        'type'     => 'text',
                                    ),
                                array(
                                        'label'    => 'Mensajero',
                                        'name'     => 'carrier_id',
                                        'type'     => 'select',
                                        'options'  => $carries,
                                    )
                                )
                        ];
        $form['sections'][1]=[
                            'label' => 'Guia Remisión',
                            'fields' => array(
                                array(
                                        'label'    => 'Imprimir Guía',
                                        'name'     => 'print_guia',
                                        'type'     => 'link',
                                        'onclick'  => "imprimirguia()",
                                        'link' => $this->module_path."/$id/printGuia"
                                    ),
                                array(
                                        'label'    => 'Cargar Guía',
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
        $form['data_fields']=$data;
        return $form;
	}
}