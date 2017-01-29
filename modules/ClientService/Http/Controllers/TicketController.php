<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\ClientService\Entities\Ticket;
use Modules\ClientService\Entities\TicketCat;
use Modules\ClientService\Entities\TicketDetail;
use Modules\Policy\Entities\Policy;
use App\ProcedureCatalog;
use App\ProcedureEntry;
use App\FileEntry;
use App\ProcedureDocument;
use DB;
use App\UploadAndDownloadFile;
use App\Currency;
use JWTAuth;
use Carbon\Carbon;
use Modules\Claim\Entities\ClaimSettlement;
use Modules\Claim\Entities\ClaimFile;


class TicketController extends NovaController {

  use UploadAndDownloadFile;

	public function index(Request $request)
	{
		$code=200;
		try{
			$result=Ticket::with('ticket_cat')
                     ->with('policy')
			               ->with('responsible')
                     ->with('ticket_detail')
			               ->get();

			  if($request->has('withView') ) {
  				$data=array();
          $index=0;
          $today = Carbon::now();

          foreach ($result as $key => $value) {


            $start_date= new Carbon($value['start_date']);
            $days = $today->diffInDays($start_date);
            $detailticket = $value->ticket_detail()->orderBy("created_at","desc")->first();
            $days_detail = $detailticket['created_at'];
            $detail_date= new Carbon($days_detail);
            $days_pass_detail =$today->diffInDays($detail_date);

            $policy = $value['policy'];
            $data[$index]['id']                = $value['id'];
            $data[$index]['policy_num']        = $policy->policy_number;
            $data[$index]['type']              = $value['ticket_cat']['category'];
            $data[$index]['subject']           = $value['ticket_cat']['display_name'];
            $data[$index]['extra_1']         = "";
            $data[$index]['extra_2']         = "";
            if($value['table_type']=="claim_settlement"){
              $settlementData = ClaimSettlement::with('claimFile')
                                                 ->find($value['table_id']);
              $data[$index]['extra_1'] = $settlementData['ic_num_claim'];
              $data[$index]['extra_2'] = $settlementData['claimFile']['description'];
            }
            if($value['table_type']=="claim_file"){
              $claimFile = ClaimFile::find($value['table_id']);
              $data[$index]['extra_2'] = $settlementData['claimFile']['description'];
            }
            $data[$index]['start_date']        = $value['start_date'];
            $data[$index]['end_date']          = $value['end_date'];
            $data[$index]['responsible_id']    = $value['responsible']['name'];
            $data[$index]['days_pass']         = $days;
            $data[$index]['days_pass_detail']  = $days_pass_detail;
            $data[$index]['buttons'] = array(
                                array(
                                     'class' => 'available',
                                     'active' => true,
                                       'link'  => '.view',
                                       'params' => [
                                                           'id'   => $value->id,
                                                         ],
                                                  'icon' => 'glyphicon glyphicon-eye-open',
                                                  'description' => 'Ver'
                                                 ),
                                           array(
                                                 'class' => 'available',
                                                 'active' => true,
                                                 'link'  => '.list_files',
                                                 'params' => [
                                                           'id'   => $value->id,
                                                        ],
                                                 'icon' => 'glyphicon glyphicon-file',
                                                 'description' => 'Archivos'
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


	private function renderIndex($content){
        $index['display']['title']='Ticket';
        $index['display']['header']=array(
        	                   array('label' =>'Id. Tikect',
                                  'filterType'=>'text',
                                  'fieldName' =>'id'),
        	                   array('label' =>'# Poliza',
                            	  'filterType'=>'text',
                                  'fieldName' =>'policy_num'),
                             array('label' =>'Tipo',
                                'filterType'=>'text',
                                  'fieldName' =>'type'),
                             array('label' =>'Asunto',
                            	  'filterType'=>'text',
                                  'fieldName' =>'subject'),
                             array('label' =>'# Reclamo',
                                'filterType'=>'text',
                                  'fieldName' =>'extra_1'),
                             array('label' =>'# Factura',
                                'filterType'=>'text',
                                  'fieldName' =>'extra_2'),
                             array('label' =>'F. Apertura',
                            	     'filterType'=>'text',
                                   'fieldName' =>'start_date'),
                             array('label'     =>'F. Cierre',
                            	     'filterType '=>'text',
                                   'fieldName'  =>'end_date'),
                             array('label'     =>'Dias Ticket',
                                   'filterType '=>'text',
                                   'fieldName'  =>'days_pass'),
                             array('label'     =>'Dias Ultimo Detalle',
                                   'filterType '=>'text',
                                   'fieldName'  =>'days_pass_detail'),
                             array('label' =>'Acciones',
                            	    'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
        $index['actions'][]=array(
							'value'   => 'Generar Ticket',
							'type'    => 'button',
							'name'    => 'crear_ticket',
							'link'    => '.create');
        //}
        return $index;
    }


	public function form()
	{

        $policy_id = Policy::all()->pluck('policy_number','id');
        $category = TicketCat::all()->pluck('category','category');
        $category_type = TicketCat::all()->pluck('display_name','id');
        $table_type = ProcedureCatalog::all()->pluck('description','id');
        $table_id = ProcedureEntry::all()->pluck('id','id');

        $data=array();
        $data['catalog']=array();
        $data['catalog']['policy_id']  = $policy_id;
        $data['catalog']['ticket_cat_id'] = $category;
        $data['catalog']['type_ticket'] = $category_type;
        $data['catalog']['table_type'] = $table_type;
        $data['catalog']['table_id'] = $table_id;
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();
	}

    public function getType($id, Request $request)
    {
    	$listIDs=ProcedureEntry::where("procedure_catalog_id",$id)
    								->pluck('id','id');
    	$this->novaMessage->setData($listIDs);
        return $this->returnJSONMessage();
    }


    public function getTypeTicket($id, Request $request)
    {

      $listTypeTicket=TicketCat::where("category",$id)
                               ->pluck('display_name','id');
      $this->novaMessage->setData($listTypeTicket);
         return $this->returnJSONMessage();
    }


    public function viewdetail($id, Request $request)
    {
       try{

       		$ticket = Ticket::with('ticket_cat')
       						->with('policy')
       						->with('responsible')
       						->find($id);


    	  if ($ticket == null){
    	  	throw new \Exception("El ticket no existe");
    	  }

    	   $data = array();
         $data['detail'] = array();
         $id_file=array();


         $data['detail']['ticket_id']        = $ticket->id;
         $data['detail']['ticket_cat_id']    = $ticket->ticket_cat->name;
         $data['detail']['policy_id']        = $ticket->policy_id;
         $data['detail']['customer']         = $ticket->policy->customer->full_name;

         $data['detail']['ticketdetail_obj'] = array();
         $detailticket = $ticket->ticket_detail()->orderBy("created_at","desc")->get();

	      foreach ($detailticket as $key => $detail){
    		  $data['detail']['ticketdetail_obj'][$key]['created_at'] = date("m/d/Y H:i",
    		  																strtotime($detail->created_at));
    		  $data['detail']['ticketdetail_obj'][$key]['user_id']    = $detail->user->name;
    		  $data['detail']['ticketdetail_obj'][$key]['type']       = $detail->type;
    		  $data['detail']['ticketdetail_obj'][$key]['comment']    = $detail->comment;

          $extradata = (array)json_decode($detail['extra_data']);
          if($detail['type']==1 && $extradata !=null){

           foreach ($extradata['filesID'] as $key2 => $value) {
            $file=FileEntry::find($value);
            $data['detail']['ticketdetail_obj'][$key]['files'][$key2]['id']   = $file['id'];
            $data['detail']['ticketdetail_obj'][$key]['files'][$key2]['name'] = $file['original_filename'];
           }
          }
           if($detail['type']==2 && $extradata !=null){
            foreach ($extradata['To'] as $key3 => $value) {
              $data['detail']['ticketdetail_obj'][$key]['email'][$key3]['mail']    = $value;
            }
            foreach ($extradata['Cc'] as $key4 => $value) {
             $data['detail']['ticketdetail_obj'][$key]['copy'][$key4]['cc']    = $value;
            }

           }

          }

         $data['typeTicket'] = ["call"=>TicketDetail::LLAMADA,"email"=>TicketDetail::EMAIL,"replay"=>TicketDetail::REPLAY];

	         $this->novaMessage->setData($data);
            return $this->returnJSONMessage();

        }catch(ModelNotFoundException $ex){
        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        }
        return $this->returnJSONMessage();
    }

     public function questions(Request $request)
      {
          try
          {
            $questions = \DB::table('cs_questions')->select('description')->get();
            $data = array();
            $data['question'] = array();
            foreach ($questions as $key => $value) {
              $data['question'][$value->description]=$value->description;
            }

            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();

          }catch(ModelNotFoundException $e){
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
          }
          return $this->returnJSONMessage();
      }

    public function email ($id, Request $request)
    {
      try
      {
        $ticket= Ticket::with("policy")
                        ->find($id);

        $email_customer= $ticket->policy->customer->email;
        $name_customer= $ticket->policy->customer->full_name;
        $customer=array();
        $customer['name']=$name_customer;
        $customer['email']="<".$email_customer.">";
        $data_customer = implode(" ",$customer);

        $email_agent= $ticket->policy->agente->email;
        $name_agent= $ticket->policy->agente->full_name;
        $agente=array();
        $agente['name']=$name_agent;
        $agente['email']="<".$email_agent.">";
        $data_agent = implode(" ",$agente);

        $email = array();
        $email['agente'] = $data_agent;
        $email['customer'] = $data_customer;

        $data_email=implode(";",$email);
        //$data_email= (json_encode($email));

        $data=array();
        $data['email'] = array();
        $data['email'] = $data_email;
        $this->novaMessage->setData($data);
        return $this->returnJSONMessage();
      }catch(ModelNotFoundException $ex){
        $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
      }
      return $this->returnJSONMessage();
    }
    public function SaveQuestions(Request $request)
      {
           $input = $request->all();
           \DB::beginTransaction();
           $code = null;
           if ($input != null)
           {
           try {
             \DB::table('cs_questions')->insert(
                 ['description' => $input['question']]
                );
             $code=200;
            \DB::commit();
            $this->novaMessage->addSuccesMessage('Creado',"Pregunta creada");
           }catch(\Exception $e){
           //show message error
           if($code==null){
             $code = 500;
            }
            \DB::rollBack();
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
            }
          }
             return $this->returnJSONMessage($code);
       }

     public function store(Request $request)
  	 {
  		$input = $request->all();
  		$result = new Ticket();
  		\DB::beginTransaction();
  		$code = null;
    	try {
    		 $result->creationticket($input);
  			 $code=200;
  			 \DB::commit();
         $this->novaMessage->setData($result->id);
  			 $this->novaMessage->addSuccesMessage('Creado',"Ticket creado");
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


	public function storedetail($id, Request $request)
	{
    $code = null;
	  try {
        $input = $request->all();
        $ticket=Ticket::find($id);

        if($ticket===null){
            $code=404;
            throw new \Exception("Ticket solicitado no existe", 404);
        }

        $tickeDetail = new TicketDetail();
        \DB::beginTransaction();

        $user = JWTAuth::parseToken()->authenticate();
        $input['user_id'] = $user->id;
        $input['ticket_id'] = $id;

    		$tickeDetail->followupticket($input);
         //upload attachments
        /*$policy_id = $ticket->policy_id;

        $table_type = "TicketDetail";
        $params = array();
        $params['fieldname']    = 'files';
        $params['table_type']   = $table_type;
        $params['table_id']     = $tickeDetail->id;
        $params['subfolder']    = 'policy/'.$policy_id.'tickets'.$id;;
        $params['multiple']     = true;
        $uploadedFile = $this->uploadFiles($request, $params);*/

			 $code=200;
			 \DB::commit();
    	 $this->novaMessage->addSuccesMessage('Creado',"Detalle creado");
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

  public function closeTicket($id, Request $request){
    $code = null;
    try{
       \DB::beginTransaction();
       $ticket=Ticket::find($id);

       if($ticket===null){
             $code=404;
             throw new \Exception("Ticket solicitado no existe", 404);
          }
       $end_date = Carbon::now();
       $ticket->end_date = $end_date;
       $ticket->save();
       $code=200;
       \DB::commit();
       $this->novaMessage->addSuccesMessage('Cerrado',"Ticket cerrado");
    }catch( \Exception $e ){
        if($code==null){
          $code = 500;
        }
        \DB::rollBack();
        $this->novaMessage->addErrorMessage('Error',$e->getMessage());
    }
    return $this->returnJSONMessage($code);


  }

  public function getTicketCat(Request $request){
    try{
        if(isset($request['category'])){
          $tc=TicketCat::where('category',$request['category'])
                          ->get();
        }else{
          $tc=TicketCat::get();
        }
        $this->novaMessage->setData($tc);
        return $this->returnJSONMessage(200);
    }catch( \Exception $e ){
        $this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
        return $this->returnJSONMessage(500);
    }
  }

  public function getClaim($id,Request $request){
      try{
        $ticket= Ticket::with("policy")
                        ->find($id);

        $procedure = ProcedureEntry::find($ticket->table_id);

          if($procedure->procedure_catalog_id == 2){

            $policy = $procedure->policy->policy_number;
            $claim  = $procedure->id;

            $fileEntry = fileEntry::where('table_id','=',$claim)->first();
            $claimData = json_decode($fileEntry->data);

            $claimType = ProcedureDocument::where('procedure_catalog_id','=',2)->get();
            $currentType = ProcedureDocument::find($claimData->procedure_document_id);

            $currencies = Currency::all();

            $data = array();
            $data['policy']               = $policy;
            $data['claim']['id']          = $claim;
            $data['claim']['amount']      = $claimData->amount;
            $data['claim']['description'] = $fileEntry->description;
            $data['claim']['currency']    = $claimData->currency;
            $data['claim']['type']        = $currentType;
            $data['claim']['types']       = $claimType;
            $data['claim']['currencies']  = $currencies;

            $this->novaMessage->setData( $data);
              return $this->returnJSONMessage();
          }
      }catch( \Exception $e){
        $this->novaMessage->addErrorMessage($e->getCode(),$e->getMessage());
        return $this->returnJSONMessage(500);
      }
  }

  public function editClaim($id,Request $request){
    $data = $request->all();

    $procedure = ProcedureEntry::find($data['claim']);

    $claim  = $procedure->id;

    $fileEntry = fileEntry::where('table_id','=',$claim)->first();
    $claimData = json_decode($fileEntry->data);

    $claimData->procedure_document_id = $data['type'];
    $claimData->amount                = $data['amount'];
    $claimData->currency              = $data['currency'];

    $fileEntry->description           = $data['description'];
    $fileEntry->data                  = json_encode($claimData);
    $fileEntry->save();

    $this->novaMessage->setData( $data);
      return $this->returnJSONMessage();
  }
}
