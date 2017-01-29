<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\Hospitalization;
use Modules\Policy\Entities\Policy;
use Modules\ClientService\Entities\Doctor;
use Modules\ClientService\Entities\Specialty;
use Modules\ClientService\Entities\Hospital;
use Modules\ClientService\Entities\TypeHospitalization;
use Modules\ClientService\Entities\ProcessInputDataHospitalizacion;
use App\Diagnosis;
use Carbon\Carbon;
use Validator;

class HospitalizationController extends NovaController {
	
	 public function form()
     {
        $policy_id = Policy::all()->pluck('policy_number','id');
        $hospital = Hospital::all()->pluck('name','id');
        $doctor = Doctor::all()->pluck('name','id');
        $diagnosis= Diagnosis::all()->pluck('display_name','id');
        $specialty = Specialty::all()->pluck('display_name','id');
        $type_hospitalization = TypeHospitalization::all()->pluck('name','id'); 

        $data=array();
        $data['catalog']=array();
        $data['catalog']['type_hospitalization_id'] = $type_hospitalization;
        $data['catalog']['policy_id']  = $policy_id;
        $data['catalog']['hospital_id'] = $hospital;
        $data['catalog']['diagnosis_id']=$diagnosis;
        $data['catalog']['doctor_id'] = $doctor;
        $data['catalog']['specialty_id'] = $specialty;

        $this->novaMessage->setData($data);
       return $this->returnJSONMessage();
     }

     public function store(Request $request)
      {
         $input = $request->all();
         $result = new Hospitalization();
         $band = false;
         $band1=false;
          $code = null;
         \DB::beginTransaction();
        
         try 
          {    
                $rules = array(
                                 "policy_id"                =>  "required",
                                 "type_hospitalization_id"  =>  "required",
                                 "hospital_id"              =>  "required",
                                 "doctor_id"                =>  "required",
                                 "specialty_id"             =>  "required",
                                 "diagnosis_id"             =>  "required",
                                 "process"                  =>  "required"                            
                               );

                $vresult = Validator::make($input,$rules,array());
                if($vresult->fails()){
                     $code=422;
                     $this->novaMessage->setData($vresult->errors());
                     throw new \Exception("Error Processing Request");
                }

                $pro = new ProcessInputDataHospitalizacion();
                $pro->start();

                $procedureId = $pro->procedure_entry_id;

                   $result = new Hospitalization();
                   $result->type_hospitalization_id    = $input['type_hospitalization_id'];
                   $result->policy_id                  = $input['policy_id'];
                   $result->hospital_id                = $input['hospital_id'];
                   $result->doctor_id                  = $input['doctor_id'];
                   $result->diagnosis_id               = $input['diagnosis_id'];
                   $result->procedure_entry_id         = $procedureId;
                   $result->specialty_id               = $input['specialty_id'];

                                      
                   if ($input['form']!= null && $input['form']!= 'undefined'){
                     $result->form                     = $input['form'];}
                  
                   if ($input['form']== null || $input['form']== 'undefined'){
                     $band1= true;
                    }
                   
                   if($input['report']!= null && $input['report']!= 'undefined') {
                     $result->report                   = $input['report'];}

                   if ($input['report']== null || $input['report']== 'undefined'){
                     $band= true;
                    } 
                                         
                   $result->save();
                   $code=200;
                   \DB::commit();

                  if ($band == false && $band1==false )
                  {  $pro->finish();
                     $this->novaMessage->addSuccesMessage('Creado',"Hospitalización creada correctamente");
                  }

                   if ($band == true || $band1==true )
                  {
                     $this->novaMessage->addSuccesMessage('Creado',"Hospitalización creada, falta adjuntar los archivos");
                  }
                  
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

	
}