<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Illuminate\Http\Request;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessChangeEffectiveDate extends ProcessEntry
{

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ChangeEffectiveDate');
	}

	public function doProcess(Request $request){
		//change the date when the policy start 
		$policy=$this->procedureEntryRel->policy;
		if(isset($policy)){
			//validate date if greater than current date
			//calculate the end_date, if 1 year after start_date

			//the start_date come in the formay m/d/y
			$date=date('Y-m-d', strtotime($request['start_date']));
			$policy->start_date=$date;
			$newEndingDate=date('Y-m-d', 
							strtotime('+1 year', 
								  strtotime($request['start_date'])
									  )
							);
			$policy->end_date=$newEndingDate;

			foreach ($policy->affiliates as $affpolicy) {
				$affpolicy->effective_date = $date;
				$affpolicy->save();
			}

			$policy->save();
		}
	}

	public function getResponsibleID($current=true){
		//Select a user that belong to the role 
		//and use automatic asignation
		return parent::getResponsibleID(true);
	}
}