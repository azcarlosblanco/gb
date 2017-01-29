<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\Emergency;
use Modules\Policy\Entities\Policy;
use Modules\ClientService\Entities\Doctor;
use Modules\ClientService\Entities\Specialty;
use Modules\ClientService\Entities\Hospital;
use App\Diagnosis;
use Carbon\Carbon;
use Validator;
use Modules\ClientService\Entities\ProcessCSInputData;

class EmergencyController extends NovaController {
	
        	
    public function form()
     {

        $policy_id = Policy::all()->pluck('policy_number','id');
        $hospital = Hospital::all()->pluck('name','id');
        $doctor = Doctor::all()->pluck('name','id');
        $diagnosis= Diagnosis::all()->pluck('display_name','id');
        $specialty = Specialty::all()->pluck('display_name','id');

        $data=array();
        $data['catalog']=array();
        $data['catalog']['customer_policy_id']  = $policy_id;
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
         $result = new Emergency();
          $code = null;
         \DB::beginTransaction();
        
         try 
          {    
                $rules = array(
                                 "customer_policy_id"   =>  "required",
                                 "hospital_id"          =>  "required",
                                 "doctor_id"            =>  "required",
                                 "specialty_id"         =>  "required",
                                 "diagnosis_id"         =>  "required",
                                 "start_date"           =>  "required",
                                 "end_date"             =>  "required",
                                 "phone"                =>  "required"
                               );

                $vresult = Validator::make($input,$rules,array());
                if($vresult->fails()){
                     $code=422;
                     $this->novaMessage->setData($vresult->errors());
                     throw new \Exception("Error Processing Request");
                }

                $pro = new ProcessCSInputData();
                $pro->start();

                $date_start= new Carbon($input['start_date']);
                $end_date = new Carbon ($input['end_date']);
                $procedureId = $pro->procedure_entry_id;

                   $result = new Emergency();
                   
                   $result->customer_policy_id     = $input['customer_policy_id'];
                   $result->hospital_id            = $input['hospital_id'];
                   $result->doctor_id              = $input['doctor_id'];
                   $result->diagnosis_id           = $input['diagnosis_id'];
                   $result->specialty_id           = $input['specialty_id'];
                   $result->start_date             = $date_start;
                   $result->end_date               = $end_date;
                   $result->phone                  = $input['phone'];
                   if ($input['accident']=='true')
                   {
                    $result->accident               = 1; 
                   }
                   else
                   {
                    $result->accident               = 0;
                   }
                   if ($input['hospitalized']=='true')
                   {
                    $result->hospitalized               = 1; 
                   }
                   else
                   {
                    $result->hospitalized               = 0;
                   }
                   $result->procedure_entry_id     = $procedureId;                   
                   $result->save();
                   $pro->finish();
                   $code=200;
                   \DB::commit();
                  $this->novaMessage->addSuccesMessage('Creado',"Emergencia Creada");
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