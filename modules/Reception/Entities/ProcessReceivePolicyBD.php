<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Modules\Reception\Entities\InitialDocumentationData;
use Illuminate\Http\Request;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessReceivePolicyBD extends ProcessEntry
{

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ReceivePolicyBD');
	}

	public function doProcess(Request $request){
		
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

}