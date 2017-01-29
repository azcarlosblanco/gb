<?php namespace Modules\Affiliate\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Entities\AffiliatePolicy;
use App\Http\Controllers\Nova\NovaController;
use Modules\Policy\Entities\Policy;
use Modules\Plan\Entities\Plan;
use App\Person;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\PlanDeducibleType;

class AffiliateController extends NovaController {

	protected $module_path='affiliate';

	public function index(Request $request){
		$code=null;
		try{
	        $pagenum = $request->input('pagenum', 0);
	        $affiliates = Affiliate::with(['affiliatePolicy.policy' => function ($query) {
										    $query->orderBy('policy.policy_number', 'asc');
										}]);

	        if( $pagenum > 0 ){
	            $affiliates = $affiliates->paginate($pagenum);
	        }else{
	            $affiliates = $affiliates->get();
	        }

            $list=array();
            $index=0;
            $plan = Plan::pluck('name','id');
            foreach ($affiliates as $value) {
            	$list[$index]['full_name'] = $value->getFullNameAttribute();
	            $list[$index]['dob'] = date("m/d/Y",strtotime($value->dob));
            	
            	$polnumber_array = [];
            	foreach ($value->affiliatePolicy as $affpolicy) {
            		if($affpolicy->dismiss_date == NULL){
            			$polnumber_array[] = $affpolicy->policy->policy_number;
	                	$list[$index]['pid_type'] = $affpolicy->affRole->name;
            		}
            	}
            	$list[$index]['policy_number'] = implode(", ", $polnumber_array);
            	$list[$index]['buttons'] = array(
	                           	array(
	                                 'class' => 'available',
	                                 'active' => true,
	                                 'link'  => '.view',
	                                 'params' => [
	                                 		'process_ID'   => $value->id,
	                                 				],
	                                 'icon' => 'glyphicon glyphicon-eye-open',
	                                 'description' => 'Ver'
	                            ),
	                       );
            	$index++;
            }

	        if($request->has('withView') && $request['withView']){    
                $this->novaMessage->setData(
                                        $this->renderIndex($list));		
	        }else{
	            $this->novaMessage->setData($plans);
	        }
	        $code=200;
	   	}catch(\Exception $e){
	    	if($code==null){
	    		$code=500;
	    	}
	    	$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
	    }
	    return $this->returnJSONMessage($code);
	}

	private function renderIndex($content){
		$index['display']['title']="Afiliados";
		$index['display']['header'][]=array('label' =>'Nombre',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'full_name');
		$index['display']['header'][]=array('label' =>'# Póliza',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'policy_number');
		$index['display']['header'][]=array('label' =>'Rol',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'pid_type');
		$index['display']['header'][]=array('label' =>'DOB',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'dob');
        $index['display']['header'][]= array('label' =>'Botenes de Acción',
                                  			   'fieldName' =>'buttons');
        $index['list']=$content;
        return $index;
	}

	public function view($id){
		$code = null;

		try{
			$insurance_company_id = 1;
			$affiliate = Affiliate::with('affiliatePolicy.policy')
									->findOrFail($id);

			$data=array();

			//catalog
			$catalog = array();
			$catalog['sex'] = Person::getSexList();
			$catalog['pid_type'] = Person::getDoctypeList();
			$catalog['role'] = AffiliateRole::pluck('name', 'id');
			$catalog['number_payments'] = NumberPayments::select('id', 'description as name')->get();

			//init data
			$affdata['aff_name']=$affiliate['name'];
			$affdata['aff_lastname']=$affiliate['lastname'];
			$affdata['pid_type']=$affiliate['pid_type']."";
			$affdata['pid_num']=$affiliate['pid_num'];
			$affdata['dob']=$affiliate['dob'];
			$affdata['sex']=$affiliate['sex'];
			$affdata['height']=$affiliate['height'];
			$affdata['weight']=$affiliate['weight'];


			$affpolicies = $affiliate->affiliatePolicy;
			$oldpolicies = array();
			foreach($affpolicies as $affpolicy){
				if($affpolicy->dismiss_date == NULL){
					//current policy
					$affdata['role'] = $affpolicy->role."";
					$affdata['num_policy'] = $affpolicy->policy->policy_number;
					$affdata['effective_date'] = $affpolicy->effective_date;
					
					$deducible_info = Deducible::with('plan')
										->find($affpolicy->policy->plan_deducible_id);
					$affdata['plan'] = $deducible_info->plan->description;
					$affdata['deductible'] = $deducible_info->name;
					$affdata['end_date'] = $affpolicy->policy->end_date;

					$deductibles_types = PlanDeducibleType::all()->pluck('name', 'id');
					//get affiliate deducibles
					foreach( $affpolicy->deducibles as $d ){
						$type = $d->plan_deducible_type_id;
						$name = $deductibles_types[$type];
						if($name=="local"){
							$affdata['deductible_local'] = $d->amount;
						}else{
							$affdata['deductible_usa'] = $d->amount;
						}
					}

					//get the policy deductibles
					$data['policy_deductibles'] = array();
					foreach( $affpolicy->policy->deducibles as $pd ){
						$name = $deductibles_types[$pd->plan_deducible_type_id];
						if($name=="local"){
							$affdata['policy_deductible_local'] = $pd->amount;
						}else{
							$affdata['policy_deductible_usa'] = $pd->amount;
						}
					}
				}else{
					//old policies
					$oldpolicies['role'] = $affpolicy->role;
					$oldpolicies['policy_number'] = $affpolicy->policy->policy_number;
					$oldpolicies['effective_date'] = $affpolicy->effective_date;
					$deducible_info = Deducible::with('plan')
										->find($affpolicy->policy->plan_deducible_id);
					$oldpolicies['plan'] = $deducible_info->plan->description;
					$oldpolicies['deductible'] = $deducible_info->name;
					$oldpolicies['end_date'] = $affpolicy->dismiss_date;
				}
			}
			
 
			$data["catalog"]=$catalog;
			$data["init_values"]=$affdata;
			$data["old_policies"]=$oldpolicies;
			$data["url"]=$this->module_path."/$id";
			
	    	$this->novaMessage->setData($data);
	        $code=200;
		}catch(\Exception $e){
			if($code==null){
	    		$code=500;
	    	}
	    	$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
		}
		return $this->returnJSONMessage($code);
	}

	public function aggregateAffiliate(){

	}

}
