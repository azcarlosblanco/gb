<?php namespace Modules\Plan\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\DeducibleOptions;
use Modules\Plan\Entities\NumberPayments;
use Illuminate\Http\Request;
use App\NovaMessage;
use JWTAuth;

class PlanDeducibleController extends Controller {
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

	        $edit=false;
	        $disabled="";
	        $plan=Plan::findOrFail($id);
	        $deducibles=null;
	        $deducibles_opt=Deducible::getDeduciblesReasons();
			return view('plan::create_plan_deducible',
								compact('deducibles',
										'plan',
										'disabled',
										'edit',
										'deducibles_opt')
								);
		}catch(\Exception $e){
    		//show message error
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    		return redirect('plan')->with('novaMessage',$this->novaMessage);
    	}
	}

	//TODO: VALIDATE NAME OF OPTIONS ARE UNIQUE
	public function store($id, Request $request)
	{
		$input=$request->all();	
    	try {
       		\DB::beginTransaction();

       		$plan=Plan::findOrFail($id);
       		
       		//create the deducibles
       		$deducibleArray=[];
       		$deducibleOptions=[];
       		$deducibles_opt=Deducible::getDeduciblesReasons();
      
       		foreach($input['deducibles'] as $key => $deducible){
       			
       			$deducibleModel=Deducible::create([
		       					'name'    => $deducible['name'],
		       					'plan_id' => $id,
		       				]);
       			foreach ($deducibles_opt as $key => $value) {
       				if (isset($deducible[$key])) {
       					$deducibleOptions[] = new DeducibleOptions([
													'reason' => $key,
													'value'   => $deducible[$key],
												]);
       				}
       			}
				$deducibleModel->deducibleOptions()
       						->saveMany($deducibleOptions);   			
       		}
       		
       		$this->novaMessage
    			->addSuccesMessage('Created','Deducible 555 was created successfully');

    		\DB::commit();
    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
    		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    	}
    	return $this->novaMessage->toJSON();
    	//return redirect('plan')->with('novaMessage',$this->novaMessage);
	}

	public function view($id)
	{
		try{
			$plan=Plan::findOrFail($id);
			$deducibles=Deducibles::with('deducibleOptions')->where('plan_id',$id);
            $edit=true;
	        $disabled="disabled";
	        $deducibles_opt=Deducible::getDeduciblesReasons();

	        $user = JWTAuth::parseToken()->authenticate();
	        if($user->can('plan_edit')){
	            $disabled="";
	        }
	        
			return view('plan::create_plan_deducible',
								compact('deducibles',
										'edit',
										'disabled',
										'deducibles_opt',
										'plan')
						);
        }catch(ModelNotFoundException $ex){
        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        	return redirect('plan')
        			->with('novaMessage',$this->novaMessage);
        }
	}

	public function update($id)
	{
		
	}

	public function delete($idDeducible)
	{
		
	}
	
}