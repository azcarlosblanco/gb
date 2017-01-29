<?php namespace Modules\Email\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Email\Entities\EmailUtils;

class EmailController extends Controller {

	public function sendEmail()
	{
		$emailUtils=new EmailUtils();
		$emailUtils->sendEmilProcess('RequestAppNewPolicyBD',
								[
								 'address'=>'rochimer@hotmail.com',
								 'name'=> 'Rocio Mera'
								],
								[
								"variables"=>
											['CUSTOMER'      =>'Alex Mero',
											'POLICY_NUMBER'  =>'0000001',
											'EFFECTIVE_DATE' =>'15-05-2015']
								]
							);
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
	
}