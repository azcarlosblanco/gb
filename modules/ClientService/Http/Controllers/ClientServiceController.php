<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\Emergency;
use Modules\Policy\Entities\Policy;
use Modules\ClientService\Entities\Doctor;
use Modules\ClientService\Entities\Specialty;
use Modules\ClientService\Entities\Hospital;
use Modules\ClientService\Entities\Observation;
use Modules\ClientService\Entities\Ticket;
use Modules\ClientService\Entities\TicketDetail;
use Modules\ClientService\Entities\TicketCat;
use Modules\ClientService\Entities\Hospitalization;
use Modules\ClientService\Entities\ProcessCSWarrantyLetter;
use Modules\ClientService\Entities\ProcessCSInputData;
use Modules\Agente\Entities\Agente;
use App\Diagnosis;
use App\ProcedureEntry;
use App\UploadAndDownloadFile;
use Modules\Email\Entities\EmailUtils;
use Modules\Authorization\Entities\Role;
use JWTAuth;
use Validator;
use Carbon\Carbon;

class ClientServiceController extends NovaController {

	 use UploadAndDownloadFile;
	
	public function index(Request $request)
	{
		$code=200;
		try
		 {
			$result = ProcedureEntry::with('policy')
										->with('csEmergency')
                    ->with('hospitalization')
										->whereHas('procedureCatalog',function ($query) {
										    $query->where('name', 'emergencycs');})
                    ->with([ 'procedureCatalog'  =>  function($query) {
                            $query->where('name', 'hospitalizations');}])
                    ->pending()
										->orderBy('created_at', 'desc')
										->get();

             
			if($request->has('withView') ) {
  			  	$data=array();
              	$index=0;

              $roleID=Role::where('name','client_service')->first()->id;

	            foreach ($result as $key => $value) 
              {

  	            if(empty($actionButtons)){
  						      $value->load('procedureCatalog');
  						      $actionButtons = $value->getListActionButtons($roleID);
  					    }

  				     	$buttons=$value->getListButtonsV2($actionButtons);
                $emergencys = Emergency::where('procedure_entry_id',$value->id)
                                    ->first(); 
            
                $today = Carbon::now();
                $date_create = $emergencys['created_at'];
                $detail_date= new Carbon($date_create);
                $days_pass =$today->diffInDays($detail_date);

      			 		if(isset($buttons['current_description'])){
      						$data[$index]['id']            = $value->id;
      						$data[$index]['policy_number'] = $emergencys->policy->policy_number;
      						$data[$index]['customer']      = $emergencys->policy->customer->full_name;
      						$data[$index]['num_days']      = $days_pass;
                  $data[$index]['type']          = 'Emergencias';
      						$data[$index]['buttons']       = $buttons['buttons'];  
      						$index++;
      					}
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

	
	public function renderIndex($content){
        $index['display']['title']='Tramites Pendientes';
        $index['display']['header']=array(
        					array('label' =>'#',
                                  'filterType'=>'text',
                                  'fieldName' =>'id'),
        	                array('label' =>'Cliente',
                                  'filterType'=>'text',
                                  'fieldName' =>'customer'),
                            array('label' =>'# Poliza',
                                  'filterType'=>'text',
                                  'fieldName' =>'policy_number'),
                            array('label' =>'# Dias',
                                  'filterType'=>'text',
                                  'fieldName' =>'num_days'),
                            array('label' =>'Tipo',
                                  'filterType'=>'text',
                                  'fieldName' =>'type'),
                            array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
        $index['actions'][]=array(
							'value'   => 'Nuevo Trámite',
							'type'    => 'button',
							'name'    => 'crear_tramite',
							'link'    => '.create');
        //}
        return $index;
    }
	

	public function form (){
		$data=array();
        $data['catalog']=array();
        $data['catalog']['customer_policy_id']  = 0;
        
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();

	}

	 public function formWarranty ( $id, Request $request)
     {
    
      try{ 
            
            $observation= Observation::where('table_id', $id)
                                      ->where('table_type',"emergency")
                                      ->get();
                       
		    $data = array();
		    $data['detail'] = array();
		    $data['detail']['observationdetail_obj'] = array();
		    $data['detail']['id'] = $id;

			foreach ($observation as $key => $detail){
				
			  $data['detail']['observationdetail_obj'][$key]['description'] = $detail->description;
		    }

		    $this->novaMessage->setData($data);
		      return $this->returnJSONMessage();
		            
		 }catch(ModelNotFoundException $ex){
		        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
		        }
		  return $this->returnJSONMessage();
	 }
  
    public function view($id, Request $request)
     {
      try{
            $emergency=Emergency::where('procedure_entry_id',$id)
                                 ->first();
            
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
            $data['emergency'] = $emergency;
            $data['emergency']['name'] = $emergency->policy->customer->full_name;

            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
            
        }catch(ModelNotFoundException $ex){
          $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        }
        return $this->returnJSONMessage();
     }

	 public function uploadFile(Request $request){
    try{
        \DB::beginTransaction();
        
        $params = array();
        $uploadedFiles = $this->uploadTempFiles($request, 'file');
        $this->novaMessage->setData($uploadedFiles[0]);
        \DB::commit();
        return $this->returnJSONMessage(200);
    }catch( \Exception $e ){
        \DB::rollback();
        $this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
        return $this->returnJSONMessage(500);
      }
  }
  public function deleteFile(Request $request){
    try{
       \DB::beginTransaction();

       $data=$request->all();     
       $deletefile = $this->deleteFileTemp($data['filetemp_id']);
      
       $this->novaMessage->setData($deletefile);
       \DB::commit();
    }catch( \Exception $e ){
        \DB::rollback();
        $this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
        return $this->returnJSONMessage(500);
      }
  } 

  public function storeObservation(Request $request){
  	
		$input = $request->all();
		$result = new Observation();
		\DB::beginTransaction();
		$code = null;
    $band = false;
    	try {

    		$rules = array(
					"description"         => "required",
					
				);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

    		$result = new Observation();
    		$result->table_type            = $input['table_type'];
    		$result->table_id              = $input['table_id'];
    		$result->description           = $input['description'];
        $result->save();
			  $code=200;
			  \DB::commit();

        $ticket_cat = TicketCat::where('name','warranty_letter_incorrect')
                                ->first();
        $emergency = Emergency::where('procedure_entry_id',$input['table_id'])->first();

        $data = array();
        $data['ticket_cat_id'] = $ticket_cat['id']; 
        $data['short_desc'] = $input['description'];
        $data['id']= $input['table_id'];
        $data['policy_id'] = $emergency['policy']['id'];
        if($emergency['ticket_id'] == null)
        {
          $band = true;
        }
        else
        {
          $band = false;
        }
        $this->createTicket($data, $band);

			$this->novaMessage->addSuccesMessage("Observación Registrada");
		} catch(\Exception $e){
    	  	//show message error
    	  	if($code==null){
    		  	$code = 500;
    		   }
    		\DB::rollBack();
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
		}
		return $this->returnJSONMessage($code);
	}

	public function preView($id,Request $request){ 

        $data=array();
        $data['catalog']=array();
        $data['catalog']['procedure_entry_id']  = $id;        
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();     
	 }

  public function emailAgent ($id, Request $request)
    {
      try
      {
        $emergency= Emergency::where('procedure_entry_id',$id)
                             ->first();
      
        $email_agent= $emergency->policy->agente->email;
        $name_agent= $emergency->policy->agente->full_name;
        $agente=array();
        $agente['name']=$name_agent;
        $agente['email']="<".$email_agent.">";
        $data_agent = implode(" ",$agente);

        $data=array();
        $data['copy'] = array();
        $data['copy'] = $data_agent;
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();
      }catch(ModelNotFoundException $ex){
        $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
      }
      return $this->returnJSONMessage();
    }

    public function emailAgentEmergency ($id, Request $request)
    {
      try
      {
        $policy= Policy::find($id);
      
        $email_agent= $policy->agente->email;
        $name_agent= $policy->agente->full_name;
        $agente=array();
        $agente['name']=$name_agent;
        $agente['email']="<".$email_agent.">";
        $data_agent = implode(" ",$agente);

        $data=array();
        $data['copy'] = array();
        $data['copy'] = $data_agent;
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();
      }catch(ModelNotFoundException $ex){
        $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
      }
      return $this->returnJSONMessage();
    }


  public function sendEmail(Request $request){

    $emergency = Emergency::orderBy("created_at","desc")->first();

    $param["to"] = EmailUtils::parseEmailField($request['email']);
    $param["cc"] = EmailUtils::parseEmailField($request['copy']);
    $param["variables"]['TRAMITE_ID']=$emergency->id;
    $param['template']=$request['content'];

    $attachments = array();
    if(isset($request['listIds'])){
        if(is_array($request['listIds'])){
          $attachments =\App\FileEntryTemp::whereIn('id',$request['listIds'])
                                            ->select('complete_path as pathToFile',
                                                'mime',
                                                'original_filename as display',
                                                'filename')
                                            ->get()
                                            ->toArray();
          $param['attachments'] = $attachments;
        }
    }

    if(isset($request['internallistIds'])){
        if(is_array($request['internallistIds'])){
          $attachments =\App\FileEntry::whereIn('id',$request['internallistIds'])
                                          ->select('complete_path as pathToFile',
                                              'mime',
                                              'original_filename as display',
                                              'filename')
                                          ->get()
                                          ->toArray();
          foreach ($attachments as $attach) {
            $param['attachments'][] = $attach;
          }
        }
    }

    //return view('quotation::email_quotation',$template_data['params']);
    $emailUtils=new EmailUtils();
    //the reason 'quotation' is a generic template that is used to send the email from quotations
    $emailUtils->sendEmailProcessMultiplesDestinataries(
                              "request_warranty_letter",
                              $param,
                              false
                            );
  }

     public function closeTicket($id, Request $request)
      {
        
        $code = null;
        try{
             \DB::beginTransaction();

             $emergency = Emergency::where('procedure_entry_id',$id)
                                    ->first();

             $ticket = Ticket::find($emergency['ticket_id']);
              
            if ($ticket != null)
             { 
               if($ticket['end_date']== null)
               {
                  $end_date = Carbon::now();
                  $ticket->end_date = $end_date;
                  $ticket->save();
                  $code=200;
                  \DB::commit();
               }
             } 

        }catch( \Exception $e ){
              if($code==null){
                $code = 500;
              }
              \DB::rollBack();
               $this->novaMessage->addErrorMessage('Error',$e->getMessage());
          }
         return $this->returnJSONMessage($code);
      }

  public function createTicket($data, $band)
      {
        \DB::beginTransaction();
         $input = $data;

         $tickeCatList = TicketCat::pluck('name','id');
         $tickeCatListDisplayName = TicketCat::pluck('display_name','id');

         $category = $input['ticket_cat_id'];

         $cf = Emergency::where('procedure_entry_id',$input['id'])
                         ->first();

         $policy = $input['policy_id'];    
         $ticket = array();
  
         if($tickeCatList[$category]=='deductible_not_match'){
             $ticket['table_type']="claim_settlement";
             $ticket['table_id']=$cf->settlement->id;
             $ticket['short_desc']=$input['short_desc'];
         }elseif($tickeCatList[$category]=='value_uncovered'){
             $ticket['table_type']="claim_settlement";
             $ticket['table_id']=$cf->settlement->id;
             $ticket['short_desc']=$input['short_desc'];
         }elseif($tickeCatList[$category]=='invoice_no_settle'){
             $ticket['table_type']="claim_file";
             $ticket['table_id']=$cf['id'];
             $ticket['short_desc']=$input['short_desc'];
         }elseif($tickeCatList[$category]=='warranty_letter_incorrect'){
             $ticket['table_type']="emergency";
             $ticket['table_id']=$cf['id'];
             $ticket['short_desc']=$input['short_desc'];
         }else{
             $ticket['table_type']="claim_file";
             $ticket['table_id']=$cf['id'];
             $ticket['short_desc']=$input['short_desc'];
         }

            $ticket['policy_id'] = $policy;
            $ticket['type_ticket'] = $category;

            if($band == true)
            {
                $ticketObj = new Ticket();
                $ticketObj->creationticket($ticket);
                $ticketObj->save();

                $emer = Emergency:: where('procedure_entry_id',$input['id'])->first();
                $emer->ticket_id = $ticketObj->id;
                $emer->save();

                    //create ticket detail, send an email with the claim
                $tickeDetailObj = new TicketDetail();
                $user = JWTAuth::parseToken()->authenticate();
                $tickeDetail['user_id'] = $user->id;
                $tickeDetail['ticket_id'] = $ticketObj->id;
                $tickeDetail['type'] = TicketDetail::EMAIL;
                $tickeDetail['email'] = "kaviles@bestdoctorsinsurance.com";
                $tickeDetail['copy'] = "";

                $tickeDetail['internallistIds'][0]['name'] =  "factura ".$cf['description'];
     
                $dataemail = $this->getDefaultContent($tickeCatList[$ticket['type_ticket']],
                                  $cf);
                $tickeDetail['comment'] = $input['short_desc'];
                $tickeDetail['subject'] = $dataemail['subject'];
                $tickeDetailObj->followupticket($tickeDetail);
                $tickeDetailObj->save();
                \DB::commit();
                $this->novaMessage->setData(array('id'=>$ticketObj->id,
                                                 'display_name'=>$tickeCatListDisplayName[$category]));
                 
             }

            else
            {
                   //create ticket detail, send an email with the claim
                $tickeDetailObj = new TicketDetail();
                $user = JWTAuth::parseToken()->authenticate();
                $tickeDetail['user_id'] = $user->id;
                $tickeDetail['ticket_id'] = $cf['ticket_id'];
                $tickeDetail['type'] = TicketDetail::EMAIL;
                $tickeDetail['email'] = "kaviles@bestdoctorsinsurance.com";
                $tickeDetail['copy'] = "";

                $tickeDetail['internallistIds'][0]['name'] =  "factura ".$cf['description'];
     
                $dataemail = $this->getDefaultContent($tickeCatList[$ticket['type_ticket']],
                                $cf);
                $tickeDetail['comment'] = $input['short_desc'];
                $tickeDetail['subject'] = $dataemail['subject'];
                $tickeDetailObj->followupticket($tickeDetail);
                $tickeDetailObj->save();
                \DB::commit();
                $this->novaMessage->setData(array('id'=>$cf['ticket_id'],
                                                 'display_name'=>$tickeCatListDisplayName[$category]));
            }

            return $this->returnJSONMessage(200);
        }


        private function getDefaultContent($ticketCat,$cf)
       {
          if($ticketCat == "deductible_not_match")
          {
            //applicable only when deducible was already saved
            $email['content'] = "Existen errores en los valores asignados al deducibles en la la factura de número ".$cf['description'];
            $email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
          }else if($ticketCat == "value_uncovered"){
           //applicable only when deducible was already saved
            $email['content'] = "Existen valores no cubiertos en la factura de número ".$cf['description']."\nLos valores no cubiertos corresponden a: ".$notes."\n Por favor indicar porque estos valores no fueron no cubiertos";
            $email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
          }else if($ticketCat == "warranty_letter_incorrect"){
           //applicable only when deducible was already saved
            $email['content'] = "La carta de garantia para la emergencia "."".$cf->id.""."esta incorrecta";
            $email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
          }
          else if($ticketCat == "invoice_no_settle"){
           //applicable only when deducible was already saved
            $email['content'] = "La factura ".$cf['description']." no fue procesada";
            $email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
          }else{
          //applicable only when deducible was already saved
            $email['content'] = "La factura ".$cf['description']." no es válida";
            $email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
          }
          return $email;
       }

}