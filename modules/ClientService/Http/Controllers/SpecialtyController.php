<?php namespace Modules\Clientservice\Http\Controllers;


use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\Specialty;
use Validator;


class SpecialtyController extends NovaController{

	public function index(Request $request)
	{
		$code=200;
		try{
			$result=Specialty::get();

			  if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					$data[$index]['name']             = $value['name'];
					$data[$index]['display_name']     = $value['display_name'];
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
	                                                         'message' => 'Confirma que deseas eliminar la 
	                                                         especialidad medica',
	                                                         'method' => 'DELETE',
	                                                         'uri' => 'specialty',
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
			
			$this->novaMessage->setData(
				$this->renderForm(
							"POST",
							"specialty",
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
		$result = new Specialty();
		\DB::beginTransaction();
		$code = null;
    	try {

    		$rules = array(
					"name"         => "required",
					"display_name" => "required"
				);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

    		$result = new Specialty();
    		$result->name            = $input['name'];
    		$result->display_name    = $input['display_name'];
    		$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Creado',"Especialidad Medica Creada");
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
			$specialty=Specialty::find($id);
			
			if($specialty===null){
				$code=404;
				throw new \Exception("Especialidad solicitada no existe", 404);
			}

			if($request->has("withView")){
				$this->novaMessage->setData(
					$this->renderForm(
							"POST",
							"specialty/$id",
							$specialty
						)
				);
			}else{
				$this->novaMessage->setData($specialty);
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
			$result = Specialty::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Especialidad solicitada no existe", 404);
			}
			$result->name=$input['name'];
			$result->display_name=$input['display_name'];
			$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Actualizado',"Especialidad Medica` actualizada");
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
			$result = Specialty::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Especialidad solicitada no existe", 404);
			}

			$result->delete();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Borrado',"Especialidad Medica ha sido eliminada exitosamente");
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
    private function renderIndex($content){
        $index['display']['title']='Especialidades';
        $index['display']['header']=array(
        	                array('label' =>'Abreviatura',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                            array('label' =>'Nombre Completo',
                                  'filterType'=>'text',
                                  'fieldName' =>'display_name'),
                             array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                            
                        );
        $index['list']=$content;
        $index['actions'][]=array(
							'value'   => 'Ingresar Especialidad',
							'type'    => 'button',
							'name'    => 'crear_mensajero',
							'link'    => '.create');
        //}
        return $index;
    }
     public function renderForm($method,$url,$data)
      {
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Especialidades Medicas";
        $form['sections'][0]=[
                            'label' => 'Especialidades Medicas',
                            'fields' => array(
	                            array(
	                                    'label' => 'Abreviatura',
	                                    'name'  => 'name',
	                                    'type'  => 'text',
	                                ),
	                            array(
	                                    'label' => 'Nombre Completo',
	                                    'name'  => 'display_name',
	                                    'type'  => 'text',
	                                    
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