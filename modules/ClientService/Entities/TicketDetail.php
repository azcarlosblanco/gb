<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;
use Modules\ClientService\Entities\Ticket;
use Modules\Email\Entities\EmailUtils;
use App\UploadAndDownloadFile;
use App\FileEntry;

class TicketDetail extends Model {
  use UploadAndDownloadFile;

	  const LLAMADA = 1;
    const EMAIL = 2;
    const REPLAY =3;
    

    protected $table="ticket_detail";

    protected $fillable = [ 'user_id', 
    						'comment',
    						'type',
    						'ticket_id'
    						];

    use SoftDeletes;
    


    public function ticket(){
		  return $this->belongsTo('Modules\ClientService\Entities\Ticket',
								'ticket_id',
	 							'id');
    }

    public function ticketAttach(){
		return $this->belongsToMany('App\FileEntry',
									'ticket_attach',
									'ticket_detail_id',
									'file_entry_id'); 
	}

	public function user(){
		return $this->belongsTo('App\User',
								'user_id',
	 							'id');
      }
//SERVICES
    private function registercall(array $data){
         try{
           $this->user_id                  =$data['user_id'];
	         $this->comment                  =$data['comment'];
	         $this->type                     =$data['type'];
	         $this->ticket_id                =$data['ticket_id'];
           $this->save();
           }catch(\Exception $e){
	    	  	throw new \Exception($e->getMessage(),$e->getCode());	
    		}
		}

    private function registeremail(array $data){
        
         try
         {			
           $extraData = array();
           $extraData['To'] = explode(";",$data['email']);
           $extraData['Cc'] = explode(";",$data['copy']);
           $this->user_id                  =$data['user_id'];
	         $this->comment                  =$data['comment'];
	         $this->type                     =$data['type'];
	         $this->ticket_id                =$data['ticket_id'];
           $this->extra_data               =json_encode($extraData);
	         $this->save();
	        }catch(\Exception $e){
	    	 	throw new \Exception($e->getMessage(),$e->getCode());	
    		}
		}

    private function registerreplay(array $data){
        
         try
         {      
           $this->user_id                  =$data['user_id'];
           $this->comment                  =$data['comment'];
           $this->type                     =$data['type'];
           $this->ticket_id                =$data['ticket_id'];
           $this->save();
          }catch(\Exception $e){
          throw new \Exception($e->getMessage(),$e->getCode()); 
        }
    }

    public function followupticket(array $data){

        $code  = null;
        $rules = array(
                        "type"     => "required",
                      );
        $vresult = Validator::make($data,$rules,array());
            if($vresult->fails()){
            	$code = 422;
                 throw new \Exception("Falta ingresar tipo",422);
            }

        if($data['type']==self::EMAIL){
            $this->registeremail($data);
            $this->sendEmail($data);
        }

        if ($data['type']==self::REPLAY) {
            $this->registerreplay($data);
        } 

        if($data['type']==self::LLAMADA){
            $this->registercall($data);
            $this->uploadCall($data);  
        }
     }

	 public function getTypeDesc(){
      switch ($this->type) {
        case TicketDetail::LLAMADA:
          return "Llamada";
          break;
        case TicketDetail::EMAIL:
          return "Email";
          break;
        default:
          return $this->state;
          break;
      }
  }	

  public function sendEmail(array $data){

    $ticket = Ticket::with("ticket_cat")
                   ->find($data['ticket_id']);

    $param["to"] = EmailUtils::parseEmailField($data['email']);
    $param["cc"] = EmailUtils::parseEmailField($data['copy']);
    $param["variables"]['TICKET_ID']=$data['ticket_id'];
    $param["variables"]['CATEGORIA']=$ticket->ticket_cat->display_name;
    $param['template']=$data['comment'];

    $attachments = array();
    if(isset($data['listIds'])){
        if(is_array($data['listIds'])){
          $attachments =\App\FileEntryTemp::whereIn('id',$data['listIds'])
                                            ->select('complete_path as pathToFile',
                                                'mime',
                                                'original_filename as display',
                                                'filename')
                                            ->get()
                                            ->toArray();
          $param['attachments'] = $attachments;
        }
    }

    if(isset($data['internallistIds'])){
        if(is_array($data['internallistIds'])){
          $attachments =\App\FileEntry::whereIn('id',$data['internallistIds'])
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
                              "clientservice_ticket",
                              $param,
                              false
                            );
  }

  public function uploadCall(array $data){
    
    $list = array(); 
    $path_to =("policy/".$data['policy_id']."/ticket");
    $category ="ticket_detail";
    $category_id = $this->id;
    
    $archivo=implode(",",$data['listIds']);

    $attachments = array();
    $id_file= array();
    $desc = array();
    $attachments = explode(",",$archivo);
    $nombre=implode(",",$data['description_files']);
    $desc = explode(",",$nombre);
    $extra_data = array();
    
    foreach ($attachments  as  $value) {
      $fid = $this->moveTempFileWithID($value, $path_to, $category,$category_id,$nombre);
      if($fid !==0){
        $id_file[]=$fid;
      }
    }

    $extra_data['filesID']=$id_file;
    $this->extra_data = json_encode($extra_data);
    $this->save();
  }
      
}