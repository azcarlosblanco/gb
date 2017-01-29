<?php namespace Modules\Email\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Email\Entities\EmailByReason;
use Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use JWTAuth;

class EmailUtils{
	private $subject;
	private $from=[];
	private $to=[];
	private $content="";
	private $cc=[];
	//array with the address of the file we want to attach
	//(
	// 'pathtofile'=>
	// 'display'=>
	// 'mime'=>
	//)
	private $attachments=[];

	function __construct(){
		//
	}

	public function sendEmailProcessMultiplesDestinataries($reason,
															$params,
															$template=false,
															$templateParams=array()){
		$this->setEmailParameters($reason,$params);

		//destinatary address
		$this->to['addresses'] = $this->to['names'] = array();
		if( isset($params['to']) && 
			is_array($params['to']) ){
			foreach ($params['to'] as $to) {
				$to['address'] = trim($to['address']);
				if (filter_var($to['address'], FILTER_VALIDATE_EMAIL)) {
				 	$this->to['addresses'][] = $to['address'];
				 	$this->to['names'][] = (isset($to['name']))?$to['name']:"";	
				}else{
					throw new \Exception("La dirección de correo no es válida", 422);
				}
			}
		}

		if(count($this->to['addresses'])==0){
			throw new \Exception("Debe intorducir al menos un destinatario", 422);
		}


		//carbon copy address
		$user = JWTAuth::parseToken()->authenticate();
		$this->cc['addresses'][] = $user->email;
		$this->cc['names'][] = "";
		if( isset($params['cc']) && 
			is_array($params['cc']) ){
			foreach ($params['cc'] as $cc) {
				$cc['address'] = trim($cc['address']);
				if (filter_var($cc['address'], FILTER_VALIDATE_EMAIL)) {
				 	$this->cc['addresses'][] = $cc['address'];
				 	$this->cc['names'][] = (isset($cc['name']))?$cc['name']:"";	
				}else{
					throw new \Exception("La dirección de correo no es válida", 422);
				}
			}
		}

		$email_type=['raw' => $this->content];
		$data_email = array();
		if($template){
			$email_type=$templateParams['template_file'];
			$data_email=$templateParams['params'];
		}

		Mail::queue($email_type,$data_email,function ($m) {
            $m->from($this->from['address'], $this->from['name']);
            $m->to($this->to['addresses'], $this->to['names']);
	        $m->cc($this->cc['addresses'], $this->cc['names']);
            $m->subject($this->subject);
            //adjuntar archivos al correo

            if(count($this->attachments)>0){
            	foreach ($this->attachments as $key => $value) {
            		//check if thefile exist in the system before attach it to the system
            		//use the Storgae function to be sure we dont attach a file in a directeory
            		//that is not secure
	        		if(Storage::disk('local')->exists($value['filename'])){
	        			$m->attach($value['pathToFile'],
	            			['as'   => $value['display'],
	            			 'mime' => $value['mime']]);
	        		}
	            }
            }
        });

	}

	public function sendEmilProcess($reason,
									$to,
									$extraParams,
									$template=false,
									$templateParams=array()){

		$this->setEmailParameters($reason,$extraParams);

		$user = JWTAuth::parseToken()->authenticate();
		$this->cc[] = $user->email;
		if( isset($extraParams['cc']) && 
			is_array($extraParams['cc']) ){
			foreach ($extraParams['cc'] as $email) {
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				 	$this->cc[] = $email;	
				}else{
					throw new \Exception("La dirección de correo no es válida", 422);
				}
			}
		}

		if(is_array($to) && $to['address']!=''){
			if(filter_var($to['address'], FILTER_VALIDATE_EMAIL)){
				$this->to['address']=$to['address'];
				$this->to['name']=isset($to['name'])?$to['name']:"";
			}else{
				throw new \Exception("La dirección de correo no es válida", 422);
			}
		}else{
			throw new Exception("The parameter to must be an array
						(address=>'eamil','name'=>'name_receive')", 1);
		}


		$email_type=['raw' => $this->content];
		$data_email = array();
		if($template){
			$email_type=$templateParams['template_file'];
			$data_email=$templateParams['params'];
		}

		Mail::queue($email_type,$data_email,function ($m) {
            $m->from($this->from['address'], $this->from['name']);
            $m->to($this->to['address'], $this->to['name']);
	        $m->cc($this->cc);
            $m->subject($this->subject);
            //adjuntar archivos al correo

            if(count($this->attachments)>0){
            	foreach ($this->attachments as $key => $value) {
            		//check if thefile exist in the system before attach it to the system
            		//use the Storgae function to be sure we dont attach a file in a directeory
            		//that is not secure
	        		if(Storage::disk('local')->exists($value['filename'])){
	        			$m->attach($value['pathToFile'],
	            			['as'   => $value['display'],
	            			 'mime' => $value['mime']]);
	        		}
	            }
            }
        });
	}

	private function setEmailParameters($reason,$extraParams){

		$emailConf=EmailByReason::where('reason',$reason)->first();

		$user = JWTAuth::parseToken()->authenticate();
		$this->from['address']=$user->email;
		$this->from['name']=$user->full_name;

		//set subject of email
		$this->subject=
				isset($extraParams['subject'])&&$extraParams['subject']!=""
						?$extraParams['subject']:$emailConf->subject;

		//set the content of the email
		if(isset($extraParams['template'])
				&& $extraParams['template']!=""){
			$this->content=$extraParams['template'];
		}elseif(isset($extraParams['html']) && $extraParams['html']){
			$this->content=$emailConf->template_html;
		}else{
			$this->content=$emailConf->template;
		}


		//replace in the subject and content of email the variables
		if(isset($extraParams['variables'])){
			$this->subject=self::renderTemplate($this->subject
									,$extraParams['variables']);
		}
		if(isset($extraParams['variables'])){
			$this->content=self::renderTemplate($this->content
									,$extraParams['variables']);
		}

		//set attachments
		$this->setAttachments($extraParams);
	}

	public function setAttachments($extraParams){
		if(isset($extraParams['attachments']) &&
			 is_array($extraParams['attachments'])){
				$this->attachments=$extraParams['attachments'];
        }
	}

	public static function renderTemplate($content,$param){
		//this function must retun an html format of email
		foreach ($param as $key => $value) {
			$content=str_replace("<$key>", $value, $content);
		}
		return $content;
	}

	public static function parseEmailField($emailField){

	    $emails=array();
	    if( isset($emailField) ){
	      	$addresses = explode(";", $emailField);
	      	foreach ($addresses as $key => $value) {
	      		$value = trim($value);
	      		if($value!=""){
		        	if( preg_match('!(.*?)\s*<\s*(.*?)\s*>!', $value, $matches) ){
		          		if (!filter_var($matches[2], FILTER_VALIDATE_EMAIL)) {
		          			throw new \Exception("La dirección de correo ".$value." no es válida");
		          		}
		          		$emails[$key]['name'] = $matches[1];
		          		$emails[$key]['address'] = $matches[2];
		        	}else if(preg_match('!\s*(.*?)\s*!', $value, $matches)){
		          		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
		          			throw new \Exception("La dirección de correo ".$value." no es válida");
		          		}
		          		$emails[$key]['name'] = "";
		          		$emails[$key]['address'] = $value;
		        	}else{
		        		throw new \Exception("La dirección de correo ".$value." no es válida");
		        	}
	      		}
	      	}
	    }else{
			throw new \Exception("El campo de correo no es válido");
	    }

	    return $emails;
	}
 

}
