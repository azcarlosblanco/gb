<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\TypeHospitalization;
use App\Person;
use Validator;


class TypeHospitalizationController extends NovaController 
{
	public function index(Request $request)
	{
		$code=200;
		try{
			$result=TypeHospitalization::get();

			  if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					//print_r($value);
					$data[$index]['name']            = $value['name'];
					
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
	                                                         'message' => 'Confirma que deseas eliminar el Tipo Hospitalización',
	                                                         'method' => 'DELETE',
	                                                         'uri' => 'type_hospitalization',
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
	
	public function form()
	{
		$code=200;
		try{
			            
			$this->novaMessage->setData(
				$this->renderForm(
							"POST",
							"type_hospitalization",
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
		$result = new TypeHospitalization();
	
		$code = null;

		\DB::beginTransaction();
    	try {

    		$rules = array(
					"name" => "required",
					);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}
    		$result = new TypeHospitalization();
    		$result->name             = $input['name'];
    		$result->save();
    		$code=200;
			$this->novaMessage->addSuccesMessage('Creado',"Tipo Hospitalización Creado");
			\DB::commit();
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

	public function view($id,Request $request)
	{
		$code=null;
    	try {
			$type=TypeHospitalization::find($id);
			
			if($type===null){
				$code=404;
				throw new \Exception("Tipo Hospitalización solicitado no existe", 404);
			}
			if($request->has("withView")){
				$this->novaMessage->setData(
					$this->renderForm(
							"POST",
							"type_hospitalization/$id",
							$type
						)
				);
			}else{
				$this->novaMessage->setData($doctor);
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
			$result = TypeHospitalization::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Tipo Hospitalización solicitado no existe", 404);
			}
			$result->name             = $input['name'];
    		$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Actualizado',"Tipo Hospitalización actualizado");
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
			$result = TypeHospitalization::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Tipo Hospitalización solicitado no existe", 404);
			}
			$result->delete();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Borrado',"Tipo Hospitalización ha sido eliminada exitosamente");
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

    private function renderIndex($content)
    {
        $index['display']['title']='Tipo Hospitalización';
        $index['display']['header']=array(
        	                array('label' =>'Nombre',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                            array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
        $index['actions'][]=array(
							'value'   => 'Ingresar Tipo Hospitalización',
							'type'    => 'button',
							'name'    => 'crear_tipo_hospitalizacion',
							'link'    => '.create');
        return $index;
    }

    public function renderForm($method,$url,$data)
    {
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Tipo Hospitalización";
        $form['sections'][0]=[
                            'label' => 'Tipo Hospitalización',
                            'fields' => array(
	                            array(
	                                    'label' => 'Nombre',
	                                    'name'  => 'name',
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