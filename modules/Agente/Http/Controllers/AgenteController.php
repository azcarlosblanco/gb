<?php namespace Modules\Agente\Http\Controllers;

use App\NovaMessage;
use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Agente\Entities\Agente;
use Modules\Agente\Http\Requests\CreateAgenteRequest;
use Modules\Agente\Http\Requests\UpdateAgenteRequest;
use App\Http\Controllers\Nova\NovaController;
use Validator;
use Modules\Utilities\Entities\Location;

class AgenteController extends NovaController {

	function __construct()
	{
        parent::__construct();
	}

	public function index(Request $request)
	{
        $code=200;
        try{
            $result=Agente::with("leaderRel")
                            ->get();

            $country_list=Location::getCountriesList();
            $city_list=Location::getCitiesList();
            $province_list=Location::getStatesList(); 
        
              if($request->has('withView') ) {
                $data=array();
                $index=0;
                foreach ($result as $key => $value) {
                    //print_r($value);
                    $data[$index]['name']                   = $value['name'];
                    $data[$index]['lastname']               = $value['lastname'];
                    $data[$index]['identity_document']      = $value['identity_document'];
                    $data[$index]['dob']                    = $value['dob'];
                    $data[$index]['mobile']                 = $value['mobile'];
                    $data[$index]['phone']                  = $value['phone'];
                    $data[$index]['email']                  = $value['email'];
                    $data[$index]['skype']                  = $value['skype'];
                    $data[$index]['country']                = $country_list[$value["country_id"]];
                    $data[$index]['province']               = $province_list[$value["province_id"]];
                    $data[$index]['city']                   = $city_list[$value["city_id"]];
                    $data[$index]['address']                = $value['address'];
                    $data[$index]['subagent']               = $value['subagent'];
                    if($value['subagent']==1)
                        $data[$index]['leader']             = $value['leaderRel']['full_name'];
                    else
                        $data[$index]['leader']             = "";
                    $data[$index]['comision']               = $value['comision'];
                    
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
                                                             'message' => 'Confirma que deseas eliminar el agente',
                                                             'method' => 'DELETE',
                                                             'uri' => 'agente',
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

        $agents_list=Agente::all()->pluck('fullName','id');
        
        $country_list=Location::getCountriesList();
        $city_list=Location::getCitiesList();
        $province_list=Location::getStatesList(); 
        $data=array();
        $data['catalog']=array();
        $data['catalog']['agent_list']  = $agents_list;
        $data['catalog']['cities_list'] = $city_list;
        $data['catalog']['country_list'] = $country_list;
        $data['catalog']['province_list'] = $province_list;
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();
	}

	
	public function store(Request $request)
	{
		$input = $request->all();
        $agente = new Agente();
    
        $code = null;

    	
    	\DB::beginTransaction();

    	try {
    	   $rules = array(
                    "name"               => "required",
                    "lastname"           => "required",
                    "identity_document"  => "required",
                    "dob"                => "required|date", 
                    "mobile"             => "required",
                    "phone"              => "required",
                    "email"              => "required|email",
                    "country_id"         => "required",
                    "province_id"        => "required",
                    "city_id"            => "required",
                    "address"            => "required",
                    "comision"           => "required"
                );

           $vresult = Validator::make($input,$rules,array());
            if($vresult->fails()){
                $code=422;
                $this->novaMessage->setData($vresult->errors());
                throw new \Exception("Error Processing Request");
            }
            $comision =$input['comision'];
            $agente = new Agente();
             if($comision >100){
                throw new \Exception("La comision no debe ser mayor a 100");
             }

     		if( $request->has('subagent') && $input['subagent']=="true"){
                $agente->subagent         = 1;
       			$leader=Agente::find($input['leader']);
                if($leader==null){
                    $code=422;
                    throw new \Exception("El agente lider no existe");
                }
                $agente->leader = $leader->id;
                $comision_leader= $leader->comision;
                

                if ($comision > $comision_leader){
                    $code=422;
                    throw new \Exception("La comision del subagente no debe ser mayor que la comision del lider");
                }else{
                    $agente->comision = $input['comision'];
                } 
       		}else{
                $agente->subagent=0;
                $agente->comision = $input['comision'];
            }

            $agente->name                 =$input['name'];
            $agente->lastname             =$input['lastname'];
            $agente->identity_document    =$input['identity_document'];
            $agente->dob                  =$input['dob'];
            $agente->mobile               =$input['mobile'];
            $agente->phone                =$input['phone'];
            $agente->email                =$input['email'];
            $agente->skype                =isset($input['skype'])?$input['skype']:"";
            $agente->country_id           =$input['country_id'];
            $agente->province_id          =$input['province_id'];
            $agente->city_id              =$input['city_id'];
            $agente->address              =$input['address'];
            $agente->save();

            $code = 200;
            $this->novaMessage->addSuccesMessage('Creado',"Agente Creado");
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
	
	
	public function view($id, Request $request)
	{
		try{
            $agente=Agente::findOrFail($id);
            
            $agents_list=Agente::all()->pluck('fullName','id');
            $location=new location();
            $country_list=Location::getCountriesList();
            $city_list=Location::getCitiesList();
            $province_list=Location::getStatesList(); 
            $data=array();
            $data['catalog']=array();
            $data['catalog']['agent_list']  = $agents_list;
            $data['catalog']['cities_list'] = $city_list;
            $data['catalog']['country_list'] = $country_list;
            $data['catalog']['province_list'] = $province_list;
            
            $data['agent'] = $agente;
            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
            
        }catch(ModelNotFoundException $ex){
        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        }
        return $this->returnJSONMessage();
	}
        
	public function update($id, Request $request)
	{   

        $code=null;
        $input = $request->all();
        \DB::beginTransaction();
		try{
			
	        $agente=Agente::find($id);
            if($agente===null){
                $code=404;
                throw new \Exception("Agente solicitado no existe", 404);
            }

            $comision =$input['comision'];
            if($comision >100){
                throw new \Exception("La comision no debe ser mayor a 100");
            }
            

            if($request->has('subagent') && $input['subagent']=="true"){
                $agente->subagent         = 1;
                $leader=Agente::find($input['leader']);
                if($leader==null){
                    throw new \Exception("El agente lider no existe");
                }
                $agente->leader = $leader->id;

                $comision_leader= $leader->comision;
                

                if ($comision > $comision_leader){
                    $code=422;
                    throw new \Exception("La comision del subagente no debe ser mayor que la comision del lider");
                }else{
                    $agente->comision = $input['comision'];
                } 
            }else{
                $agente->subagent = 0;
                $agente->leader = null;
                $agente->comision = $input['comision'];
            }

			$agente->name                 =$input['name'];
			$agente->lastname             =$input['lastname'];
			$agente->identity_document    =$input['identity_document'];
			$agente->dob                  =$input['dob'];
            $agente->mobile               =$input['mobile'];
            $agente->phone                =$input['phone'];
			$agente->email                =$input['email'];
			$agente->skype                =isset($input['skype'])?$input['skype']:"";
			$agente->country_id           =$input['country_id'];
			$agente->province_id          =$input['province_id'];
			$agente->city_id              =$input['city_id'];
			$agente->address              =$input['address'];
			$agente->save();
            $code=200;
            $this->novaMessage->addSuccesMessage('Actualizado',"Agente Actualizado");
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

	
    
	public function delete($id, Request $request)
	{
		try{
			\DB::beginTransaction();

            $agente=Agente::find($id);

            $count=$agente->subagentes()->count();
            if($count==0){
            	$agente->delete();	
            }else{
            	$this->novaMessage->addErrorMessage('No Eliminado','Agente no pude ser eliminado, es lider de otro agente');
            }

            $this->novaMessage->addSuccesMessage('Eliminado','Agente eliminado');
            \DB::commit();
        }catch(ModelNotFoundException $ex){
        	\DB::rollback();
            $this->novaMessage->addErrorMessage('Not found',$ex->getMessage());
        }

        $this->novaMessage->setRoute('agente');
        return $this->returnJSONMessage();
	}

	    
	public function rellocateSubagents($idOldLeader,$idNewLeader)
	{
		try{
            \DB::beginTransaction();
            $oldLeader=Agente::findOrFail($oldLeader);
            $newLeader=Agente::findOrFail($idNewLeader);
            
            Agente::rellocateSubagents($idOldLeader,$idNewLeader);

			$this->novaMessage->addSuccessMessage('Success','Reasignation was successfully');
            \DB::commit();
        }catch(ModelNotFoundException $e){
            \DB::rollback();
        	$this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage();
	}

    
    
    private function renderIndex($agentes){
        $index['display']['title'] ='Agentes';
        $index['display']['header']=array(
                            array('label'=>'Nombre',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                            array('label' =>'Apellido',
                                  'filterType'=>'text',
                                  'fieldName' =>'lastname'),
                            array('label' =>'Email',
                                  'filterType'=>'text',
                                  'fieldName' =>'email'),
                            array('label' =>'Celular',
                                  'filterType'=>'text',
                                  'fieldName' =>'mobile'),
                            array('label' =>'Telefono Fijo',
                                  'filterType'=>'text',
                                  'fieldName' =>'phone'),
                            array('label' =>'Subagente',
                                  'filterType'=>'text',
                                  'fieldName' =>'subagent'),
                            array('label' =>'Lider',
                                  'filterType'=>'text',
                                  'fieldName' =>'leader'),
                            array('label' =>'Comision',
                                  'filterType'=>'text',
                                  'fieldName' =>'comision'),
                            array('label' =>'Acciones',
                                  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                            );        
            $index['actions'][]=array(
                                        'value'   => 'Ingresar Agente',
                                        'type'    => 'button',
                                        'name'    => 'crear_agente',
                                        'link'    => '.create');
            $index['list']=$agentes;
            return $index;
    }

    public function renderForm($url,$method,$agentList){
        $form['method']=$method;
        $form['url']=$url;
        $form['campos']=array(
                            array(
                                    'label' => 'Name',
                                    'name'  => 'name',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Lastname',
                                    'name'  => 'lastname',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'ID',
                                    'name'  => 'identity_document',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Fecha de Nacimiento',
                                    'name'  => 'dob',
                                    'type'  => 'date',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Email',
                                    'name'  => 'email',
                                    'type'  => 'email',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Skype',
                                    'name'  => 'skype',
                                    'type'  => 'email',
                                ),
                            array(
                                    'label' => 'Celular',
                                    'name'  => 'celular',
                                    'type'  => 'digits',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Telefono Fijo',
                                    'name'  => 'phone',
                                    'type'  => 'digits',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Country',
                                    'name'  => 'country',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Province',
                                    'name'  => 'province',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'City',
                                    'name'  => 'city',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Address',
                                    'name'  => 'address',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label' => 'Comision',
                                    'name'  => 'comision',
                                    'type'  => 'text',
                                    'required' => 1,
                                ),
                            array(
                                    'label'   => 'SubAgente',
                                    'name'    => 'subagente',
                                    'type'    => 'checkbox',
                                    'options' => [1],
                                ),
                            array(
                                    'label'   => 'Leader',
                                    'name'    => 'leader',
                                    'type'    => 'combo',
                                    'options' => $agentList 
                                )
                        );
        $form['actions'][]=array(
                                    'display' => 'Submit',
                                    'type'    => 'submit'
                                );
        $form['actions'][]=array(
                                    'display' => 'Cancel',
                                    'type'    => 'href',
                                    'url'     => 'agente'
                                );
        return $form;
    }
}