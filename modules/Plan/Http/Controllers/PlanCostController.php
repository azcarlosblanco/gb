<?php namespace Modules\Plan\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\DeducibleOptions;
use Modules\Plan\Entities\PlanCost;
use Modules\Plan\Entities\NumberPayments;
use Illuminate\Http\Request;
use App\NovaMessage;
use Carbon\Carbon;

class PlanCostController extends Controller {
	
	public $novaMessage;

	function __construct()
	{
        //parent::__construct();
		$this->novaMessage=new NovaMessage();
	}

	public function create($id)
	{
		try {
       		\DB::beginTransaction();

       		$plan=Plan::findOrFail($id)->pluck('name','id');
			//get list of deducibles that belong to the plan
			$deducibles=Deducible::where('plan_id',$id)->pluck('name','id');
			//get list of type payments
			$numberPayments=NumberPayments::all()->pluck('name','id');
	    	$edit=false;
	        $disabled="";
			return view('plan::create_plan_cost',
							compact('deducibles',
									'numberPayments',
									'disabled',
									'edit',
									'plan')
						);
		}catch(\Exception $e){
    		//show message error
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    		return redirect('plan')->with('novaMessage',$this->novaMessage);
    	}
	}

	public function store($id, Request $request)
	{
		$input=$request->all();	
    	try {
       		\DB::beginTransaction();

       		$plan=Plan::findOrFail($id);
       		
       		//create the deducibles
       		$costArrays=[];
       		//return $input;
       		$index=0;
          $now=Carbon::Now();
       		foreach($input['plan_cost'] as $key => $firstlevel){
       			$startAge=$firstlevel['start_age'];
       			$endAge=$firstlevel['end_age'];
       			foreach ($firstlevel['deducible'] as $key2 => $secondlevel) {
       				$deducibleId=$key2;
       				foreach ($secondlevel as $key3 => $value) {
       					$costArrays[$index]['start_age']=$startAge;
       					$costArrays[$index]['end_age']=$endAge;
       					$costArrays[$index]['plan_deducible_id']=$deducibleId;
       					$costArrays[$index]['number_payments_id']=$key3;
       					$costArrays[$index]['value']=$value;
       					$costArrays[$index]['plan_type_id']=1;
       					$costArrays[$index]['created_at']=$now;
       					$costArrays[$index]['updated_at']=$now;
       					$index++;
       					//$costArrays[]=new PlanCost($costs);
       				}
       			}
       		}
       		
       		//return $costArrays;
       		//\DB::table('plan_cost')->insert($costArrays);
       	PlanCost::insert($costArrays);
       		//PlanCost::saveMany($costArrays);
       		//$plan->planCosts()->saveMany($costArrays);
    		\DB::commit();

    		$this->novaMessage->addSuccesMessage('Created','Cost was created successfully');

    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    	}
    	
    	return ($this->novaMessage->toJSON());
    	//return redirect('plan')->with('novaMessage',$this->novaMessage);
	}

	public function view($planID)
	{
		
	}

	public function update($id)
	{
		
	}

	public function delete($idDeducible)
	{
		
	}
	
}