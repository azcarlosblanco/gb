<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\Doctor;
use Modules\ClientService\Entities\Specialty;
use App\Person;
use Validator;

class DoctorController extends NovaController {
	
	public function index(Request $request)
	{
		$code=200;
		try{
			$result=Doctor::with('specialty')
			                     ->get();

			  if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					//print_r($value);
					$data[$index]['name']            = $value['name'];
					$data[$index]['pid_type']        = $value['pid_type'];
					$data[$index]['pid_num']         = $value['pid_num'];

					$specialities = array();
					foreach ($value['specialty'] as $specialty) {
						$specialities[] = $specialty->display_name;
					}
					$data[$index]['specialty']  = implode(", ", $specialities); 

					
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
	                                                         'message' => 'Confirma que deseas eliminar el doctor',
	                                                         'method' => 'DELETE',
	                                                         'uri' => 'doctor',
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
			$Specialty=Specialty::pluck('display_name','id');
            
			$this->novaMessage->setData(
				$this->renderForm(
							"POST",
							"doctor",
							$tipo_id,
							$Specialty,
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

	public function form_catalog(){
        
        $tipo_id=Person::getDocTypeList();

        $this->novaMessage->setData($tipo_id);
        return $this->returnJSONMessage();

	}

	public function store(Request $request)
	{
		$input = $request->all();
		$result = new Doctor();
	
		$code = null;

		\DB::beginTransaction();
    	try {

    		$rules = array(
					"name" => "required",
					"pid_type" => "required",
					"pid_num" => "required",
					);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

    		$result = new Doctor();
    		$result->name             = $input['name'];
    		$result->pid_type         = $input['pid_type'];
    		$result->pid_num          = $input['pid_num'];
			$result->save();

			if ($request['specialty']!= null){
				$specialty_id=$input["specialty"];
				$result->specialty()->attach([$specialty_id]);
		    }
			$code=200;
			$this->novaMessage->addSuccesMessage('Creado',"Doctor Creado");
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

	public function view($id,Request $request){
		$code=null;
    	try {
			$doctor=Doctor::find($id);
			$tipo_id=Person::getDocTypeList();
            $Specialty=Specialty::pluck('display_name','id');

			if($doctor===null){
				$code=404;
				throw new \Exception("Doctor solicitado no existe", 404);
			}

			if($request->has("withView")){
				$this->novaMessage->setData(
					$this->renderForm(
							"POST",
							"doctor/$id",
							$tipo_id,
							$Specialty,
							$doctor
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
			$result = Doctor::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Doctor solicitado no existe", 404);
			}
			$result->name             = $input['name'];
    		$result->pid_type         = $input['pid_type'];
    		$result->pid_num          = $input['pid_num'];
			$result->save();
			$specialty_id=$input["specialty"];
			$result->specialty()->attach([$specialty_id]);
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Actualizado',"Doctor actualizado");
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
			$result = Doctor::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Doctor solicitado no existe", 404);
			}

			$result->specialty()->detach();
			$result->delete();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Borrado',"Doctor ha sido eliminado exitosamente");
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
        $index['display']['title']='Doctor';
        $index['display']['header']=array(
        	                array('label' =>'Nombre',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                            array('label' =>'Tipo Identificacion',
                                  'filterType'=>'text',
                                  'fieldName' =>'pid_type'),
                             array('label' =>'Identificacion',
                            	  'filterType'=>'text',
                                  'fieldName' =>'pid_num'),
                             array('label' =>'Especialidad',
                            	  'filterType'=>'text',
                                  'fieldName' =>'specialty'),
                             array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
        $index['actions'][]=array(
							'value'   => 'Ingresar Doctor',
							'type'    => 'button',
							'name'    => 'crear_doctor',
							'link'    => '.create');
        //}
        return $index;
    }

    public function renderForm($method,$url,$tipo,$specialty,$data)
      {
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Doctor";
        $form['sections'][0]=[
                            'label' => 'Doctor',
                            'fields' => array(
	                            array(
	                                    'label' => 'Nombre',
	                                    'name'  => 'name',
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
	                                    'name'  => 'pid_num',
	                                    'type'  => 'Numeric',
	                                    
	                                ),
	                            array(
	                                    'label'   => 'Especialidad',
	                                    'name'    => 'specialty',
	                                    'type'    => 'checkbox',
	                                    'options' => $specialty,
	                                    'value'   =>"1",
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