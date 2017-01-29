<?php namespace Modules\Supplier\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Supplier\Entities\Supplier;
use Modules\Supplier\Entities\SupplierCategory;
use Validator;


class SupplierController extends NovaController {
	
	public function index(Request $request)
	{
		$code=200;
		try{
			$result=Supplier::with('SupplierCategory')
			                     ->get();

			  if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					//print_r($value);
					$data[$index]['name']          = $value['name'];
					$data[$index]['description']   = $value['description'];
					$data[$index]['category']      = $value['SupplierCategory']['name']; 
					
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
	                                                         'message' => 'Confirma que deseas eliminar el proveedor',
	                                                         'method' => 'DELETE',
	                                                         'uri' => 'supplier',
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
			$c=SupplierCategory::pluck('name','id');
			$this->novaMessage->setData(
				$this->renderForm(
							"POST",
							"supplier",
							$c,
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
		$result = new Supplier();
		\DB::beginTransaction();
    	try {

    		$rules = array(
					"name" => "required",
					"description" => "required",
					"category" => "required|numeric"
				);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

    		$result = new Supplier();
    		$result->name             = $input['name'];
    		$result->description      = $input['description'];
    		$result->category         = $input['category'];
			$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Creado',"Proveedor Creado");
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
			$supplier=Supplier::find($id);
			$c=SupplierCategory::pluck('name','id');
			
			if($supplier===null){
				$code=404;
				throw new \Exception("Proveedor solicitado no existe", 404);
			}

			if($request->has("withView")){
				$this->novaMessage->setData(
					$this->renderForm(
							"POST",
							"supplier/$id",
							$c,
							$supplier
						)
				);
			}else{
				$this->novaMessage->setData($supplier);
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
			$result = Supplier::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Proveedor solicitado no existe", 404);
			}
			$result->name          =$input['name'];
			$result->description   =$input['description'];
			$result->category      =$input['category'];
			$result->save();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Actualizado',"Proveedor actualizado");
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
			$result = Supplier::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Proveedor solicitado no existe", 404);
			}

			$result->delete();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Borrado',"Proveedor ha sido eliminado exitosamente");
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
        $index['display']['title']='Proveedor Servicios';
        $index['display']['header']=array(
                            array('label' =>'Nombre',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                            array('label' =>'Descripcion',
                            	  'filterType'=>'text',
                                  'fieldName' =>'description'),
                            array('label' =>'Categoria',
                            	  'filterType'=>'text',
                                  'fieldName' =>'category'),
                            array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
        //TODO: WHEN FIXED AUTHENTICATICATION SET THE CORRECT PERMISSIONS
        //if(\Auth::user()->can('agente_create')){
        $index['actions'][]=array(
							'value'   => 'Crear proveedor',
							'type'    => 'button',
							'name'    => 'crear_proveedor',
							'link'    => '.create');
        //}
        return $index;
    }
    public function renderForm($method,$url,$category,$data)
      {
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Mensajeros";
        $form['sections'][0]=[
                            'label' => 'Proveedor Servicios',
                            'fields' => array(
	                            array(
	                                    'label' => 'Nombre',
	                                    'name'  => 'name',
	                                    'type'  => 'text'
	                                    
	                                  ),
	                            array(
	                                    'label' => 'Descripcion',
	                                    'name'  => 'description',
	                                    'type'  => 'text'
	                                    
	                                ),
	                            array(
	                                    'label'   => 'Categoria',
	                                    'name'    => 'category',
	                                    'type'    => 'select',
	                                    'options' => $category,
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