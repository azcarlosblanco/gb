<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\ProcedureCatalog;
use App\ProcedureEntry;
use App\ProcessCatalog;
use App\ProcessEntry;
use Modules\Authorization\Entities\Role;
use Modules\Policy\Entities\Policy;
use Modules\Customer\Entities\Customer;
use Modules\Agente\Entities\Agente;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use Modules\GuiaEnvio\Entities\GuiaEnvio;
use Modules\GuiaEnvio\Entities\GuiaEnvioItem;
use Modules\GuiaEnvio\Entities\Carrier;
use Modules\GuiaEnvio\Entities\GuiaEnvioRelation;
use JWTAuth;


class DispachingDocsController extends NovaController {

    use \App\UploadAndDownloadFile;

	protected $module_path='reception/guiaremision';

	public function reportSendDocuments(Request $request){
		//get list documents by send
        $code = null;
        try{
            $filters = array();
            $filters['receiver'] = "";
    		$query=SendDocument::where('state','bysend');
    		if($request->has('receiver') && $request['receiver']!=''){
    			$receivedata=explode("-", $request['receiver']);
    			$query->where('receiver_id',$receivedata[1])
    					->where('receiver_type',$receivedata[0]);
                $filters['receiver'] = $request['receiver'];
    		}
    		$list=$query->get();

    		$result=array();
    		$receiver=array();
            $ic=InsuranceCompany::pluck('company_name','id');
            $ag=Agente::select('name','lastname','id')
                        ->get();
            foreach ($ic as $key => $value) {
                $receiver['ic-'.$key]=$value;
            }
            foreach ($ag as $key => $value) {
                $receiver['agent-'.$value['id']]=$value['name']." ".$value['lastname'];
            }
    		$index=0;
    		foreach ($list as $header) {
    			$result[$index]['registerid']=$header->id;
    			$result[$index]['reason']=$header->reason;
    			if($header->receiver_type=='ic'){
    				$ic=InsuranceCompany::find($header->receiver_id);
    				$result[$index]['receiver_name']=$ic->company_name;
    				//$receiver['ic-'.$header->receiver_id]=$ic->company_name;
    			}elseif($header->receiver_type=='agent') {
    				$agent=Agente::find($header->receiver_id);
    				$result[$index]['receiver_name']=
    					$agent->getFullNameAttribute();
    				//$receiver['agent-'.$header->receiver_id]=
    					$agent->name;
    			}else{
    				$result[$index]['receiver_name']=$header->receiver_name;
    			}
    			$result[$index]['receiver_id']=$header->receiver_id;

    			$user = JWTAuth::parseToken()->authenticate();

    			//$result[$index]['sender']=$user->getFullNameAttribute();
                $result[$index]['sender']="";
    			$result[$index]['sender_id']=$user->id;
    			$index++;
    		}

    		if($request->has('withView') && $request['withView']){
                    $this->novaMessage->setData(
    	               $this->renderSendDocuments($result,$receiver,$filters)
                );
    		}else{
    			$this->novaMessage->setData($result);
    		}
            $code = 200;
        }catch(\Exception $e){
            $code = 500;
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
		return $this->returnJSONMessage($code);
	}

	public function createGuideForm(Request $request){
		//validate we have a list of documents to send
        $code = null;
        try{
    		if(!$request->has('registerid') || 
    			count($request['registerid'])==0){
                $code = 422;
    			throw new \Exception("Seleccione al menos un registro", 422);
    		}

    		//get list docs
    		$flag=false;
    		$query=SendDocument::where('state','bysend')
    								->whereIn('id',$request['registerid']);
    		if($request->has('receiver') && $request['receiver']!=''){
    			$receivedata=explode("-", $request['receiver']);
    			$query->where('receiver_id',$receivedata[1])
    					->where('receiver_type',$receivedata[0]);
    			$flag=true;
    		}
    		$listDocs=$query->get();
    		if(count($listDocs)==0){
                $code = 422;
    			throw new \Exception("Los registros seleccionados no son válidos", 422);
    		}

            $data['receiver_name']="";
            $data['receiver_address']="";
            $data['receiver_phone']="";
    		if($flag){
    			$header=$listDocs[0];
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
    	        }
    		}

    		$index=0;
    		foreach ($listDocs as $header){
    			$data['groupDocuments'][$index]['reasons']=$header->reason;
    			$data['groupDocuments'][$index]['IDs']=$header->id;
    			
    			$items=$header->items;
    			$documents=array();
    			foreach ($items as $doc) {
    				$documents[]=$doc->description;	
    			}
    			$data['groupDocuments'][$index]['documents']=implode("\n",$documents);
    			$index++;
    		}

    		$carries=Carrier::pluck('full_name','id');

    		$this->novaMessage->setData(
                $this->renderForm(
                    'POST',
                    $this->module_path,
                    $data,
                    $carries
                    )
                );
            $code = 200;
        }catch(\Exception $e){
            if($code==null){
                $code = 500;
            }
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
	}

	
	public function printGuide(Request $request){
		try{
            $data=$this->getDataGuide($request);
            $pdf = \PDF::loadView('reception::guia', $data);
            return $pdf->download(
            			'guia_remision_'.$data['track_number'].'.pdf'
            		);  
        }catch(\Exception $e){
            //show message error
            $this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
            return $this->returnJSONMessage(500);
        }
	}

	private function getDataGuide(Request $request){
		//data from request in the form
		$carries=Carrier::get();

        //print_r($carries);

       	//carrier
       	$data['carrier_id']=$request['carrier_id'];
        foreach($carries as $carrier){
            if($carrier->id == $data['carrier_id']){
                $data['mensajero_name']=$carrier['full_name'];
                $data['mensajero_id']=$carrier['identification'];
            }
        }
        

        //general
        //TODO: this will fail is there amore than one person creating guides at teh same time
        $maxid = \DB::table('guia_remision')->max('id');
        if ($maxid ==null){
            $maxid=1;
        }

       	$data['date']=date('Y-m-d');
        $data['track_number']=$maxid;
        $data['reason']='guia_varios';
        $data['sender']= JWTAuth::parseToken()->authenticate()->id;
        $data['external_track_number']="";

        //data
        $data['receiver_name']=$request['receiver_name'];
        $data['receiver_address']=$request['receiver_address'];
        $data['receiver_phone']=$request['receiver_phone']?$request['receiver_phone']:"";

        //items
       	$gdocs=(array)json_decode($request['groupDocuments_text']);

       	$data['guia_items']=array();
        $data['idDocs']=array();
       	$index=0;
       	foreach($gdocs as $key => $group){
            $group = (array)$group;
            if(isset($group['IDs'])){
                $data['idDocs'][]=$group['IDs'];
            }
       		$docs=explode("\n", $group['documents']);
       		foreach ($docs as $value) {
       			$data['guia_items'][$index]['description']=$value;
       			$data['guia_items'][$index]['num_copies']=1;
                $index++;
            }
       	}
       	return $data;
	}

	public function createGuide(Request $request){
        try{
            \DB::beginTransaction();
            $dataGuia=$this->getDataGuide($request);
            $this->doAction($request, $dataGuia);
            \DB::commit(); 
            return $this->returnJSONMessage(200);
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
            return $this->returnJSONMessage(500);
        }
	}

    private function doAction(Request $request, $dataGuia){
        $data['date']=$dataGuia['date'];
        $data['track_number']=$dataGuia['track_number'];
        $data['reason']=$dataGuia['reason'];
        $data['sender']=$dataGuia['sender'];
        $data['receiver_name']=$dataGuia['receiver_name'];
        $data['receiver_address']=$dataGuia['receiver_address'];
        $data['receiver_phone']=$dataGuia['receiver_phone'];
        $data['carrier_id']=$dataGuia['carrier_id'];
        $data['external_track_number']=$dataGuia['external_track_number'];
        //$data['foreign_id']=$dataGuia['foreign_id'];

        $dataItem=$dataGuia['guia_items'];

        $processIDs=array();
        if(count($dataGuia['idDocs'])>0){
            //process related with this documents that have to be mark as done
            $processIDs=SendDocument::whereIn('id',$dataGuia['idDocs'])
                            ->pluck('process_id');

            //mark document as sent
            $docs=SendDocument::whereIn('id',$dataGuia['idDocs'])
                                ->update(['state' => 'sent']);
        
        }

        //guardar guia en la base
        $guia=GuiaEnvio::create(
                $data
            );

        //guardar items de la guia en la base
        $items=array();
        foreach ($dataItem as $key => $item) {
            $items[]=['description'=>$item['description'],
                    'num_copies' =>$item['num_copies'],
                    'guia_remision_id'=> $guia->id];
        }

        GuiaEnvioItem::insert($items);

        //mark process as finished
        if(count($processIDs)>0){
            $processes=ProcessEntry::whereIn('id',$processIDs)
                                    ->get();

            foreach ($processes as $process) {
                $p=ProcessEntry::newFromModel($process);
                $p->finish();
                //associate the process to the guide
                GuiaEnvioRelation::create([
                                        "table_name" => "process_entry",
                                        "table_id"   => $p->id,
                                        "guia_remision_id" => $guia->id
                                        ]);
            }
        }

        //upload guia
        $params['fieldname']='filefields';
        $params['table_type']='guia_remision';
        $params['table_id']=$guia->id;
        $params['subfolder']='guia_remision';
        $params['multiple']=true;
        $params['description_files'][]='guia_remision';
        $entryIDs=$result=$this->uploadFiles($request,$params);

        $guia->file_entry_id=$entryIDs[0];
        $guia->save();
    }

	public function reportGuiaRemision(){
		
	}

	//view functions
	public function renderSendDocuments($content,$receivers,$filters){
		$index['display']['title']='Listado Documentos por Enviar';
		$index['display']['header']=array(
							array('label' =>'',
                                  'fieldName' =>'registerid'),
                            array('label' =>'Motivo',
                                  'filterType'=>'text',
                                  'fieldName' =>'reason'),
                            array('label' =>'Destinatario',
                                  'filterType'=>'text',
                                  'fieldName' =>'receiver_name'),
                            array('label' =>'Emisor',
                            	  'filterType'=>'text',
                                  'fieldName' =>'sender'),
                        );

		$index['actions'][]=array(
							'label'    => 'Para',
							'type'     => 'select',
							'options'  => $receivers,
							'name'     => 'receiver',
							'onchange' => '.envio-documentos',
                            'initvalue' => ($filters['receiver']?$filters['receiver']:""));
		$index['actions'][]=array(
							'value'   => 'Crear Guía',
							'type'    => 'button',
							'name'    => 'crear_guia',
							'link'    => '.create-guide');
		$index['display']['select']=1;
        $index['list']=$content;
        return $index;
	}

	public function renderForm($method,$url,$data,$carries){
		/*$form['method']=$method;
        $form['url']=$url;
        $form['files']=true;
        $form["title"]="Guia de Remisión";
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
                            'label' => 'Impresión Guía',
                            'fields' => array(
                                array(
                                        'label'    => 'Imprimir Guía',
                                        'name'     => 'print_guia',
                                        'type'     => 'link',
                                        'onclick'  => "imprimirguia()",
                                        'link' => $this->module_path."/printguide"
                                    ),
                                array(
                                        'label'    => 'Cargar Guía',
                                        'name'     => 'filefields[]',
                                        'type'     => 'file',
                                        'num_file' => '1'
                                    )
                                )
                        ];

        $documentation=array();
        foreach ($data['documents'] as $docs) {
    		$documentation[]=array(
                                        'label'    => 'Motivo',
                                        'name'     => 'reasons[]',
                                        'type'     => 'text',
                                    );
    		$documentation[]=array(
                                        'label'    => 'Documentos',
                                        'name'     => 'documents[]',
                                        'type'     => 'textarea',
                                    );
        }
        $form['sections'][2]=[
                            'label' => 'Documentos a Enviar',
                            'fields' => $documentation,
                        ];
        $form['actions'][]=array(
                                    'display' => 'Crear la Guìa',
                                    'type'    => 'submit'
                                );*/
        $form['display']['carrier_opt']=$carries;
        $form['data_fields']=$data;
        return $form;
	}
}
