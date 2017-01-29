<?php namespace Modules\Claim\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessClaimsReviewDocuments extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ClaimsReviewDocuments');
	}



	public function doProcess(Request $request){

	}

	public function getResponsibleID($current=false){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID(false);
	}
}