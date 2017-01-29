<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Illuminate\Http\Request;
use App\User;


class ProcessClaimsReceiveSettlement extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ClaimsReceiveSettlement');
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}
}