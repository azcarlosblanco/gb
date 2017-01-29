<?php namespace Modules\RRHH\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\EnumPhone;
use App\User;
use Modules\RRHH\Entities\Employee;
use Modules\Email\Entities\SendEmail;
use Modules\Authorization\Entities\Role;
use Modules\RRHH\Entities\Department;
/*use App\Province;
use App\City;*/
use App\Jobs\CreateEmployeeEmail;
use Validator;
use App\Person;

class EmployeeController extends NovaController {
	
	public function index(Request $request){
		$code=null;
		try{
	        $pagenum = $request->input('pagenum', 0);
	        $employees = Employee::with('user')
	        						->with('currentDepartment')
	        						->with('phones');

	        if( $pagenum > 0 ){
	            $employees = $employees->paginate($pagenum);
	        }else{
	            $employees = $employees->get();
	        }

            //print_r($employees);

            $list=array();
            $index=0;
            foreach ($employees as $value) {
                $list[$index]['id']=$value->id;
                $list[$index]['full_name']=$value->full_name;
                $list[$index]['user_id']=$value->user_id;
                $list[$index]['email']=$value->user->email;
                $roles=array();
                foreach ($value->user->roles as $role) {
                    $roles[]=$role['display_name'];
                }
                $phones=array();
                foreach ($value->phones as $phone) {
                    $phones[]=$phone['number'];
                }
                $list[$index]['roles']      = implode($roles,' , ');
                $list[$index]['phones']     = implode($phones,' , ');
                $list[$index]['department'] = $value
                                                ->currentDepartment()
                                                ->first()
                                                ->description;
                $list[$index]['buttons'] = array(
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
                                     'message' => 'Confirma que deseas eliminar al empleado' . $value->full_name,
                                     'method' => 'DELETE',
                                     'uri' => 'rrhh/employee',
                                 ]
                            ),
                       );
                $index++;
            }

	        if($request->has('withView') && $request['withView']){    
                $this->novaMessage->setData(
                                        $this->renderIndex($list));		
	        }else{
	            $this->novaMessage->setData($list);
	        }
	        $code=200;
	    }catch(\Exception $e){
	    	$code=$e->getCode();
	    	if($code==null){
	    		$code=500;
	    	}
	    	$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
	    }
	    return $this->returnJSONMessage($code);
    }

    public function form(Request $request){
        $code=200;
        try{
            //get list of roles
            $roles=Role::all()->pluck('name', 'id');

            $param['departments']=Department::all()->pluck('name', 'id');
            $param['roles']=Role::all()->pluck('name', 'id');
            $param['type_id']=Person::getDocTypeList();
            /*$param['provinces']=Province::all()->pluck('name', 'id');
            $param['cities']=City::all()->pluck('name', 'id');*/

            $result = $this->novaMessage->setData(
                    $this->renderForm(
                                "rrhh/employee",
                                "POST",
                                $param,
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

    public static function validator(array $data,$edit=false)
    {
        $rules = [
            'name'           => 'required',
            'lastname'       => 'required',
            'department_id'  => 'required|exists:department,id',
            'type_id'        => 'required|exists:person_doctype,id',
            'p_home'         => 'required',
            'p_cel'          => 'required',
        ];

        if(!$edit){
            $rules['document_id'] = 'required|unique:employee';
            $rules['email']       = 'required|unique:user';
            $rules['password']    = 'required';
            $rules['cpassword']   = 'required|same:password';
        }else{
            if( (isset($data['password'])) && ($data['password'] != "") ){
                $rules['password']    = 'required';
                $rules['cpassword']   = 'required|same:password';
            }
        }

        return Validator::make($data, $rules);
    }

    public function store(Request $request){
        $code = null;
        try{
            \DB::beginTransaction();

            $input = $request->all();

            //validaciones
            $validator = EmployeeController::validator($input);
            if ($validator->fails()) {
                $this->novaMessage->setData($validator->errors());
                throw new \Exception("El formulario contine errores", 422);
            }

            if(!User::validatePassword($input['password'])){
                $errors = ["contraseña"=>"La contraseña debe ser mínimo de 8 caracteres y contener dígitos, letras mayúsculas y minúsculas"];
                $this->novaMessage->setData($errors);
                throw new \Exception("El formulario contine errores", 422);
            }

            //create user
            $du=[
                "name"     => $input['name'],
                "lastname" => $input['lastname'],
                "email"    => $input['email'],
                "password" => $input['password'],
                'roles'    => $input['roles']];
            $user=User::createUser($du);

            //create employee
            $de=$input;
            $de['user_id']=$user->id;
            $emp=Employee::createEmployee($de);

            //mandar correo con los datos del password al correo del empleado
            $this->dispatch(new CreateEmployeeEmail($emp,$input['password']));
            $this->novaMessage->setData($emp->id);
            $code=201;
            \DB::commit();
        }catch(\Exception $e){
            //show message error
            //$code=$e->getCode();
            $code=($code==null)?500:$code;
            $this->novaMessage->addErrorMessage('Error:',$e->getMessage());
            \DB::rollback();
        }
        return $this->returnJSONMessage($code);
    }

    public function formView($id,Request $request){
        $code=200;
        try{
            $emp=Employee::with('user')
                            ->with('currentDepartment')
                            ->with('phones')
                            ->find($id);

            if($emp==null){
                throw new \Exception("Empleado no exite", 404);
            }

            //get list of roles
            $roles=Role::all()->pluck('name', 'id');

            $param['departments']=Department::all()->pluck('name', 'id');
            $param['roles']=Role::all()->pluck('name', 'id');
            $param['type_id']=Person::getDocTypeList();
            /*$param['provinces']=Province::all()->pluck('name', 'id');
            $param['cities']=City::all()->pluck('name', 'id');*/

            $data['name']=$emp->name;
            $data['lastname']=$emp->lastname;
            $data['document_id']=$emp->document_id;
            $data['type_id']="".$emp->pid_type;
            $data['department_id']="".$emp
                                    ->currentDepartment()
                                    ->first()
                                    ->id."";
            $data['roles[]']="".$emp->user->roles->first()->id."";
            $data['email']=$emp->user->email;
            $phones=$emp->phones()->get();
            foreach ($phones as $phone) {
                if(EnumPhone::HOME==$phone['phone_type']){
                    $data['p_home']=$phone['number'];
                }elseif(EnumPhone::CELLULAR==$phone['phone_type']){
                    $data['p_cel']=$phone['number'];
                }
            }
            /*$address=$emp->currentAddress()->first();
            $data['province_id']=$address->province_id;
            $data['city_id']=$address->city_id;
            $data['street_1']=$address->street_1;
            $data['street_2']=$address->street_2;
            $data['num_house']=$address->num_house;
            $data['post_code']=$address->post_code;
            $data['references']=$address->references;*/

            $result = $this->novaMessage->setData(
                    $this->renderForm(
                                "rrhh/employee/$id",
                                "POST",
                                $param,
                                $data,
                                true
                            )
                );
        }catch(\Exception $e){
            //show message error
            $code=500;
            $this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
    }

    public function update($id,Request $request){
        $code=200;
        try{
            \DB::beginTransaction();

            $input = $request->all();

            //validaciones
            $validator = EmployeeController::validator($input,true);
            if ($validator->fails()) {
                $this->novaMessage->setData($validator->errors());
                throw new \Exception("El formulario contine errores", 422);
            }

            $emp=Employee::find($id);
            if($emp==null){
                throw new \Exception("Empleado no exite", 404);
            }

            if( (isset($input['password'])) && ($input['password']!="") ){
                //update user password
                if(!User::validatePassword($input['password'])){
                    $errors = ["contraseña"=>"La contraseña debe ser mínimo de 8 caracteres y contener dígitos, letras mayúsculas y minúsculas"];
                    $this->novaMessage->setData($errors);
                    throw new \Exception("El formulario contine errores", 422);
                }

                $user = $emp->user;
                $user->updatePassword($input['password']);
            }

            //create employee
            $de=$input;
            $emp->updateEmployee($de);

            \DB::commit();
        }catch(\Exception $e){
            //show message error
            $code=$e->getCode();
            $code=($code==null)?500:$code;
            $this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
            \DB::rollback();
        }
        return $this->returnJSONMessage($code);
    }

    public function delete($id,Request $request){
        $code=200;
        try{
            \DB::beginTransaction();

            $input = $request->all();

            $emp=Employee::find($id);
            if($emp==null){
                throw new \Exception("Empleado no exite", 404);
            }

            $user=$emp->user();
            $emp=$emp->delete();
            //delete user associate to the employee
            $user->delete();

            \DB::commit();
        }catch(\Exception $e){
            //show message error
            $code=$e->getCode();
            $code=($code==null)?500:$code;
            $this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
            \DB::rollback();
        }
        return $this->returnJSONMessage($code);
    }

    private function renderForm($url,$method,$param,$data,$edit=false){
        $form['method']=$method;
        $form['url']=$url;
        $form["title"]="Empleados";
        $form['sections'][0]=[
                    'label' => 'Datos Personales',
                    'fields' => array(
                        array(
                                'label' => 'Nombre',
                                'name'  => 'name',
                                'type'  => 'text',
                            ),
                        array(
                                'label' => 'Apellido',
                                'name'  => 'lastname',
                                'type'  => 'text',
                            ),
                        array(
                                'label' => 'Tipo Identificación',
                                'name'  => 'type_id',
                                'type'  => 'select',
                                'options' => $param['type_id'],
                            ),
                        array(
                                'label' => 'Documento Identidad',
                                'name'  => 'document_id',
                                'type'  => 'integer',
                                'readonly' => $edit?1:0
                            ),
                        array(
                                'label' => 'Department',
                                'name'  => 'department_id',
                                'type'  => 'select',
                                'options' => $param['departments'],
                            ),
                        array(
                                'label' => 'Role',
                                'name'  => 'roles[]',
                                'type'  => 'checkbox',
                                'options' => $param['roles'],
                            )
                        )
                ];
        $form['sections'][1]=[
                    'label' => 'Usuario',
                    'fields' => array(
                        array(
                                'label' => 'Email',
                                'name'  => 'email',
                                'type'  => 'email',
                                'readonly' => $edit?1:0
                            ),
                        array(
                                'label' => 'Contraseña',
                                'name'  => 'password',
                                'type'  => 'text',
                                'optional' => $edit?true:false,
                            ),
                        array(
                                'label' => 'Confimar Contraseña',
                                'name'  => 'cpassword',
                                'type'  => 'text',
                                'optional' => $edit?true:false,
                            ),
                        )
                ];
        $form['sections'][2]=[
                    'label' => 'Datos Contacto',
                    'fields' => array(
                        array(
                                'label' => 'Teléfono Casa',
                                'name'  => 'p_home',
                                'type'  => 'text',
                            ),
                        array(
                                'label' => 'Celular',
                                'name'  => 'p_cel',
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

    private function renderIndex($employees){
        $index['display']['title']='Lista Empleados';
        $index['display']['header']=array(
                            array('label' =>'Nombre Completo',
                                  'filterType'=>'text',
                                  'fieldName' =>'full_name'),
                            array('label' =>'Email',
                            	  'filterType'=>'text',
                                  'fieldName' =>'email'),
                            array('label' =>'Teléfono',
                            	  'filterType'=>'text',
                                  'fieldName' =>'phones'),
                            array('label' =>'Role',
                            	  'filterType'=>'text',
                                  'fieldName' => "roles"),
                            array('label' =>'Departamento',
                            	  'filterType'=>'text',
                                  'fieldName' =>'department'),
                            array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$employees;
        //TODO: WHEN FIXED AUTHENTICATICATION SET THE CORRECT PERMISSIONS
        //if(\Auth::user()->can('agente_create')){
            $index['actions'][]=array(
    							'value'   => 'Crear Empleado',
    							'type'    => 'button',
    							'name'    => 'create_user',
    							'link'    => '.create');
        //}
        return $index;
    }
	
}