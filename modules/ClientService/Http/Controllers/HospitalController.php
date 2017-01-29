<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Utilities\Entities\Location;
use Modules\ClientService\Entities\Hospital;
use Modules\ClientService\Entities\Specialty;
use Modules\Plan\Entities\Plan;
use Validator;

class HospitalController extends NovaController {
	
	public function index(Request $request)
	{
		$code=200;
		try{
			$result= Hospital::with('specialty')
			                ->with('plan')
			                ->get();
		    
			$country_list=Location::getCountriesList();
            $city_list=Location::getCitiesList();
            $province_list=Location::getStatesList(); 
           

			  if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					
					$data[$index]['name']            = $value['name'];
					$data[$index]['country_id']      = $country_list[$value["country_id"]];
					$data[$index]['province_id']     = $province_list[$value["province_id"]];
					$data[$index]['city_id']         = $city_list[$value["city_id"]];
					$data[$index]['address']         = $value['address'];
					$specialities = array();
					foreach ($value['specialty'] as $specialty) {
						$specialities[] = $specialty->display_name;
					}
					$data[$index]['specialty']  = implode(", ", $specialities); 
                    $planes = array();
                    foreach ($value['plan'] as $plan) {
                      $planes[]=$plan->name;
                    }
                    $data[$index]['plan']  = implode(", ", $planes);
					
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
	                                                         'message' => 'Confirma que deseas eliminar el hospital',
	                                                         'method' => 'DELETE',
	                                                         'uri' => 'hospital',
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
			$country_list=Location::getCountriesList();
			$province_list=Location::getStatesList();
			$city_list=Location::getCitiesList();
			$specialty=Specialty::pluck('display_name','id');
			$plan=Plan::pluck('name','id');
            
			$this->novaMessage->setData(
				$this->renderForm(
							"POST",
							"hospital",
							$country_list,
							$province_list,
							$city_list,
							$specialty,
							$plan,
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
		$result = new Hospital();
	
		$code = null;

		\DB::beginTransaction();
    	try {

    		$rules = array(
					"name"        => "required",
					"address"     => "required"
				);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

    		$result = new Hospital();
    		$result->name                = $input['name'];
    		if ($request['country_id']!= null){
				$result->country_id      =$input["country_id"];
			  }
			if ($request['province_id']!= null){
                $result->province_id     = $input["province_id"];
			}
			if ($request['city_id']!= null){
    		    $result->city_id             = $input['city_id'];
    		}
    		$result->address             = $input['address'];
    		$result->save();
    		if ($request['specialty_id']!= null){
			    $specialty_id=$input["specialty"];
			    $result->specialty()->attach([$specialty_id]);
			}
			if ($request['plan']!= null) {
			   $plan_id=$input["plan"];
			   $result->plan()->attach([$plan_id]);
			}
			
			$code=200;
			$this->novaMessage->addSuccesMessage('Creado',"Hospital Creado");
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
			$hospital= Hospital::find($id);
			$hospital['country_id']   = "".$hospital['country_id'];
			$hospital['province_id']  = "".$hospital['province_id'];
			$hospital['city_id']      = "".$hospital['city_id'];
			$country_list=Location::getCountriesList();
			$province_list=Location::getStatesList();
			$city_list=Location::getCitiesList();
			$specialty=Specialty::pluck('display_name','id');
			$plan=Plan::pluck('name','id');
            
			if($hospital===null){
				$code=404;
				throw new \Exception("Hospital solicitado no existe", 404);
			}

			if($request->has("withView")){
				$this->novaMessage->setData(
					$this->renderForm(
							"POST",
							"hospital/$id",
							$country_list,
							$province_list,
							$city_list,
							$specialty,
							$plan,
							$hospital
						)
				);
			}else{
				$this->novaMessage->setData($hospital);
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
			$result = Hospital::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Hospital solicitado no existe", 404);
			}
			$result->name             = $input['name'];
    		$result->country_id       = $input['country_id'];
    		$result->province_id      = $input['province_id'];
    		$result->city_id          = $input['city_id'];
    		$result->address          = $input['address'];
    		$result->save();
			$specialty_id=$input["specialty"];
			$result->specialty()->attach([$specialty_id]);
			$plan_id=$input["plan"];
			$result->plan()->attach([$plan_id]);
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Actualizado',"Hospital actualizado");
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
			$result = Hospital::find($id);
			if($result===null){
				$code=404;
				throw new \Exception("Hospital solicitado no existe", 404);
			}

			$result->specialty()->detach();
			$result->plan()->detach();
			$result->delete();
			$code=200;
			\DB::commit();
			$this->novaMessage->addSuccesMessage('Borrado',"Hospital ha sido eliminado exitosamente");
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
        $index['display']['title']='Hospitales';
        $index['display']['header']=array(
        	                array('label' =>'Nombre',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                            array('label' =>'Pais',
                                  'filterType'=>'text',
                                  'fieldName' =>'country_id'),
                             array('label' =>'Provincia',
                            	  'filterType'=>'text',
                                  'fieldName' =>'province_id'),
                             array('label' =>'Ciudad',
                            	  'filterType'=>'text',
                                  'fieldName' =>'city_id'),
                             array('label' =>'Direccion',
                            	  'filterType'=>'text',
                                  'fieldName' =>'address'),
                             array('label' =>'Especialidad',
                            	  'filterType'=>'text',
                                  'fieldName' =>'specialty'),
                             array('label' =>'Plan',
                            	  'filterType'=>'text',
                                  'fieldName' =>'plan'),
                             array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
        $index['actions'][]=array(
							'value'   => 'Ingresar Hospital',
							'type'    => 'button',
							'name'    => 'crear_hospital',
							'link'    => '.create');
        //}
        return $index;
    }
	
    public function renderForm($method,$url,$country,$province,$city,$specialty,$plan,$data)
      {
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Hospitales";
        $form['sections'][0]=[
                            'label' => 'Hospital',
                            'fields' => array(
	                            array(
	                                    'label' => 'Nombre',
	                                    'name'  => 'name',
	                                    'type'  => 'text',
	                                    
	                                ),
	                            array(
	                                    'label' => 'Pais',
	                                    'name'  => 'country_id',
	                                    'type'  => 'select',
	                                    'options' => $country,
	                                ),
	                            array(
	                                    'label' => 'Provincia',
	                                    'name'  => 'province_id',
	                                    'type'  => 'select',
	                                    'options' => $province,
	                                ),
	                            array(
	                                    'label' => 'Ciudad',
	                                    'name'  => 'city_id',
	                                    'type'  => 'select',
	                                    'options' => $city,
	                                ),
	                            array(
	                                    'label' => 'Direccion',
	                                    'name'  => 'address',
	                                    'type'  => 'text',
	                                    
	                                ),
	                            array(
	                                    'label'   => 'Especialidad',
	                                    'name'    => 'specialty',
	                                    'type'    => 'checkbox',
	                                    'options' => $specialty,
	                                    'value'   =>"1",
	                                ),
	                            array(
	                                    'label'   => 'Plan',
	                                    'name'    => 'plan',
	                                    'type'    => 'checkbox',
	                                    'options' => $plan,
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