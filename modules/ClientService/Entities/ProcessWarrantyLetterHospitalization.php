<?php namespace Modules\Clientservice\Entities;
   
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\ProcessEntry;

class ProcessWarrantyLetterHospitalization extends ProcessEntry {

	    use \App\UploadAndDownloadFile;

	function __construct (){
	     //call to method start of the
		parent::__construct(array(),'WarrantyLetterHospitalization');
	}


	public function doProcess(Request $request){


	}

	public function getResponsibleId($current=true){
	   //the responsible is the user that is currently logged in the application
	   return parent::getResponsibleId($current);
	} 

}