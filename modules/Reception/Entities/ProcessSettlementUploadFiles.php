<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;

class ProcessSettlementUploadFiles extends ProcessEntry {

    use \App\UploadAndDownloadFile;

    function __construct(){
        //call to method start of the
        parent::__construct(array(),'SettlementUploadFiles');
    }

    public function start($procedureEntry=null){
      parent::start($procedureEntry);
      //we create folder at start to avoid issues with multiple requests trying to create a dir
      $this->createDir('settlements/'.$this->id);
    }

    public function doProcess(Request $request){

    }

    public function getResponsibleID($current=true){
        //the responsible is the user that is currently logged in the application
        return parent::getResponsibleID($current);
    }

}
