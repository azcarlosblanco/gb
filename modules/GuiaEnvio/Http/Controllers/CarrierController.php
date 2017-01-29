<?php namespace Modules\GuiaEnvio\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\GuiaEnvio\Entities\Carrier;
use App\Person;
use Validator;

class CarrierController extends NovaController {
	
	public function index(Request $request)
	{
		$code=200;
		try{
			$result=Carrier::get();

			  if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					$data[$index]['type']             = $value['type'];
					$data[$index]['full_name']        = $value['full_name'];
					$data[$index]['identification']   = $value['identification']; 
					
					$data[$index]['buttons'] = array(
	                                               array(
	                                                      'class' => 'edit',
	                                                      'params' => [
	                                                         'id' => $value->id,
	                                                     ]
	                                               ),
	                                               array(
	                                                     'class' => 'delete',
	                                                     'params' => [
	                                                         'id' => $value->id,
	                                                         'message' => 'Confirma que deseas eliminar el mensajero',
	                                                         'method' => 'DELETE',
	                                                         'uri' => 'carrier',
	                                                     ]
	                                                ),
	                                           );
					$index++;
				}
				$this->novaMessage->setData($this->renderIndex($data));
			}else{
				$this->novaMessage->setdata($result);
			}
		}catch(\Exception $e){
			//show message error
			$code=500;
    		$this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
		}		
		return $this->returnJSONMessage($code);
	}

	public function form(){
		$code=200;
		try{
			$tipo_id=Person::getDocTypeList();

            $tipo= array ( 'person' => 'Persona',
	                          'company'  => 'Compania');

			$this->novaMessage->setData(
				$this->renderForm(
							"POST",
							"carrier",
							$tipo,
							$tipo_id,
							null
						)
				);

		}catch(\Exception $e){
			//show message error
			$code=500;
    		$this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
		}		
		return $this->returnJSONMessage($code);
	}
	public function store(Request $request)
	{
		$input = $request->all();
		$result = new Carrier();
		\DB::beginTransaction();
		$code = null;
    	try {

    		$rules = array(
					"type" => "required",
					"full_name" => "required",
					"pid_type" => "required|numeric",
					"identification"  =>"required|numeric"
				);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

    		$result = new Carrier();
    		$result->type            = $input['type'];
    		$result->full_name       = $input['full_name'];
    		$result->pid_type        = $input['pid_type'];
    		$result->identification  = $input['identification'];
			$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Creado',"Mensajero Creado");
		}catch(\Exception $e){
    		//show message error
    		if($code==null){
    			$code = 500;
    		}
    		\DB::rollBack();
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
		}
		return $this->returnJSONMessage($code);
	}

	public function view($id,Request $request){
		$code=null;
    	try {
			$carrier=Carrier::find($id);
			$tipo_id=Person::getDocTypeList();
            $tipo= array ( 'person' => 'Persona',
	                       'company'  => 'Compania');

			if($carrier===null){
				$code=404;
				throw new \Exception("Menssajero solicitado no existe", 404);
			}

			if($request->has("withView")){
				$this->novaMessage->setData(
					$this->renderForm(
							"POST",
							"carrier/$id",
							$tipo,
							$tipo_id,
							$carrier
						)
				);
			}else{
				$this->novaMessage->setData($carrier);
			}
			
			
			$code=200;
		}catch(\Exception $e){
    		//show message error
    		if($code===null){
    			$code=500;
    		}
    		$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}
		return $this->returnJSONMessage($code);
	}

	public function update($id, Request $request)
	{
		$code=null;
		$input = $request->all();
		\DB::beginTransaction();
    	try {
			$result = Carrier::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Mensajero solicitado no existe", 404);
			}
			$result->type=$input['type'];
			$result->full_name=$input['full_name'];
			$result->pid_type=$input['pid_type'];
			$result->identification=$input['identification'];
			$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Actualizado',"Mensajero actualizado");
		}catch(\Exception $e){
    		//show message error
    		if($code===null){
    			$code=500;
    		}
    		\DB::rollBack();
    		$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}
		return $this->returnJSONMessage($code);

	}

	public function delete($id)
	{
		$code=null;
		\DB::beginTransaction();
		try {
			$result = Carrier::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Mensajero solicitado no existe", 404);
			}

			$result->delete();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Borrado',"Mensajero ha sido eliminado exitosamente");
		}catch(\Exception $e){
    		//show message error
    		if($code===null){
    			$code=500;
    		}
    		\DB::rollBack();
    		$this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
		}
		return $this->returnJSONMessage($code);
	}
	

	private function renderIndex($users){
        $index['display']['title']='Mensajeros';
        $index['display']['header']=array(
                            array('label' =>'Tipo',
                                  'filterType'=>'text',
                                  'fieldName' =>'type'),
                            array('label' =>'Nombre',
                            	  'filterType'=>'text',
                                  'fieldName' =>'full_name'),
                            array('label' =>'Identificacion',
                            	  'filterType'=>'text',
                                  'fieldName' =>'identification'),
                            array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$users;
        //TODO: WHEN FIXED AUTHENTICATICATION SET THE CORRECT PERMISSIONS
        //if(\Auth::user()->can('agente_create')){
        $index['actions'][]=array(
							'value'   => 'Crear Mensajero',
							'type'    => 'button',
							'name'    => 'crear_mensajero',
							'link'    => '.create');
        //}
        return $index;
    }

      public function renderForm($method,$url,$persona,$tipo,$data)
      {
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Mensajeros";
        $form['sections'][0]=[
                            'label' => 'Mensajeros',
                            'fields' => array(
	                            array(
	                                    'label' => 'Tipo',
	                                    'name'  => 'type',
	                                    'type'  => 'select',
	                                    'options' => $persona,
	                                ),
	                            array(
	                                    'label' => 'Nombre',
	                                    'name'  => 'full_name',
	                                    'type'  => 'text',
	                                    
	                                ),
	                            array(
	                                    'label' => 'Tipo de Identificacion',
	                                    'name'  => 'pid_type',
	                                    'type'  => 'select',
	                                    'options' => $tipo,
	                                ),
	                            array(
	                                    'label' => 'Identificacion',
	                                    'name'  => 'identification',
	                                    'type'  => 'Numeric',
	                                    
	                                ),
	                            
	                           	)
                        ];
        $form['actions'][]=array(
                                    'display' => 'Submit',
                                    'type'    => 'submit'
                                );
        $form['actions'][]=array(
                                    'display' => 'Cancel',
                                    'type'    => 'href'
                                );
        $form['data_fields']=$data;
        return $form;
    }
}