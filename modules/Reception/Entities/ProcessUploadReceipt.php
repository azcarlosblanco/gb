<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Modules\Reception\Entities\InitialDocumentationData;
use Illuminate\Http\Request;
use Modules\Payment\Entities\PolicyCost;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessUploadReceipt extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	//comment
	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'UploadReceipt');
	}

	public function doProcess(Request $request){
		
		//policy can not be activated if it has not been paid
		$policy = $this->procedureEntryRel->policy;
		$quote = $policy->getPolicyQuote(1);
		if( ($quote->state == PolicyCost::S_PAIDOFF) ) {
			$policy->state = 'active';
			$policy->save();
		}
		
		//subir recibo de recibido de bd
		$params['fieldname']='filefields';
		$params['table_type']='procedure_entry';
		$params['table_id']=$this->procedure_entry_id;
		$params['subfolder']='newPolicy/'.$this->procedure_entry_id;
		$params['multiple']=true;
		$params['description_files'][]='receipt_bd';
		$result=$this->uploadFiles($request,$params);

	}

	public function finish() {

		$finishProcedure = false;
		$policy = $this->procedureEntryRel->policy;
		$quote = $policy->getPolicyQuote(1);
		if( ($quote->state == PolicyCost::S_PAIDOFF) ){
			$finishProcedure = true;
		} else {
			$policy->state = "wait_client_payment";
			$policy->save();
		}

        if($this->state!='finished'){
            $carbon = new \Carbon\Carbon();
            $date = $carbon->now();

            $this->end_date  = $date;
            $this->state     = 'finished';
            $this->save();

            //if next_process is set, set the state of corresponding procedure_entry
            //to
            //to continue with the procedure
            if(is_null($this->procedureEntry) || $this->procedureEntry->id==null){
                $this->procedureEntry=$this->procedureEntryRel;
            }
            if(is_null($this->processCatalog) || $this->processCatalog->id==null){
                $this->processCatalog=$this->processCatalogRel;
            }

            if( $this->isLastProcess() && $finishProcedure ){
                //finish the procedure
                $this->procedureEntry->finish();
            }else{
                $nextProcessesCat=$this->processCatalog->getNextProcesses();
                foreach ($nextProcessesCat as $key => $processCat) {

                    $pr=$this->getProcessByCat($processCat->id);
                    if(isset($pr) &&
                        $pr->state!='cancelled'){
                        //can not start a process that is been doing or has been finished
                            continue;
                    }

                    if($processCat->isCompulsory()){
                        $process = ProcessFactory::
                                        createProcess($processCat->name);
                        if($process->
                            checkPrerequistite($this->procedureEntry) )
                        {
                            $process->start($this->procedureEntry);
                        }
                    }
                }
            }
        }
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

}