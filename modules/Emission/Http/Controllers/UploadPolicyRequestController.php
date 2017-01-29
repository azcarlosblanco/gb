<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Entities\ProcessUploadPolicyRequest;
use Modules\Reception\Entities\RequestPolicyData;
use Modules\Agente\Entities\Agente;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\DeducibleOptions;
use Modules\Plan\Entities\NumberPayments;
use Modules\Plan\Entities\PlanType;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Customer\Entities\Customer;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Entities\AffiliatePolicy;
use Modules\Affiliate\Entities\AffiliatePolicyDeducible;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\Observation\Entities\ObservationType;
use Modules\Observation\Entities\Observation;
use Modules\Policy\Entities\Policy;
use Modules\Policy\Entities\PolicyDeducible;
use Modules\Customer\Services\Validation\CustomerValidator;
use Modules\Emission\Services\Validation\PolicyEmissionValidator;
use Modules\Payment\Services\Validation\PaymentValidator;
use Modules\Affiliate\Services\Validation\AffiliateValidator;
use Modules\Plan\Services\Validation\PlanValidator;
use Modules\Observation\Services\Validation\ObservationValidator;
use App\ProcessEntry;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcedureCatalog;
use App\Person;
use App\Location;
use App\Quiz;
use App\FileEntry;
use App\HelperConvertUnits;
//use App\UploadAndDownloadFile;
use Carbon\Carbon;
use Modules\Policy\Entities\PrevInsurancePolicy;
use Modules\Payment\Services\PolicyCostService;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use JWTAuth;

class UploadPolicyRequestController extends NovaController {
	use \App\UploadAndDownloadFile;

	function __construct(){
  		parent::__construct();
	}

	public function index(){}

	public function uploadPolicyRequestForm($process_ID){
		//general form data
		$data = array('date_format'=>'Y-m-d');
		$data['process_ID'] = $process_ID;
		$insurance_company_id = 1;

		// customer/affiliate aux data
		$data['person_sex'] = Person::getSexList();
		$data['person_status'] = Person::getStatusList();
		$data['person_doctype'] = Person::getDoctypeList();

		$data['countries'] = Location::getCountriesList();
		$data['states'] = Location::getStatesList();
		$data['cities'] = Location::getCitiesList();

		$cid = \App\ProcedureCatalog::where('name','newpolicy')->value('id');
		$documentListDesc = array();
      	$documentListName = array();
		if(!empty($cid)){
			$documentList = \App\ProcedureDocument::where('procedure_catalog_id', $cid)
			->select('id','description','name')->get();
	        foreach ($documentList as $key => $value) {
	          $documentListDesc[$value->id] = $value->description;
	          $documentListName[$value->name] = $value->id;
	        }
		}
		$data['documentList'] = $documentListDesc;
      	$data['docListName'] = $documentListName;

		//quiz data
		//TODO filter by insurance company and quiz code maybe
		$data['quiz'] = Quiz::with(array('items'=> function($query){
				$query->addSelect(array('id', 'description', 'resp_type', 'quiz_id'));
		}))->select('id', 'description')->get();

		//Por el momento solo mostramos las polizas que son prepagas
		$ppga = \Modules\Plan\Entities\PlanCategory::where('name','PPGA')
						->where('insurance_company_id', $insurance_company_id)
						->first();

		//plan data
		$plan_lst = Plan::with('deducibles.deducibleOptions')
							->select('id', 'name', 'description')
							->where('insurance_company_id', '=', $insurance_company_id)
							->where('plan_category_id',$ppga->id)
							->get();

		foreach( $plan_lst as $key=>$val ){

			if( isset($val->deducibles) ){
				foreach( $val->deducibles as $i => $x  ){
					$val->deducibles[$i]->amount = '';
					if( isset($x->deducibleOptions) ){
						foreach($x->deducibleOptions as $dopt){
							$val->deducibles[$i]->amount .= (isset($dopt->value)) ? $dopt->value.' / ' : '';
						}//end foreach

						$val->deducibles[$i]->amount = preg_replace('/\s\/\s$/', '', $val->deducibles[$i]->amount);
					}
					//add information about additional cover
					$addiotnal_covers[]=array();
					foreach($x->additionalCover as $key => $addCover){
						$addiotnal_covers[$key]['name'] = $addCover->name;
						$addiotnal_covers[$key]['id'] = $addCover->id;
						$addiotnal_covers[$key]['options'] = [];
						$addiotnal_covers[$key]['IsCriticalSelect']=0;
						$addiotnal_covers[$key]['IsMaternity']=0;
						if($addCover->name=="Critical Select"){
							$addiotnal_covers[$key]['IsCriticalSelect']=1;
						}elseif($addCover->name=="Maternity Complications Rider"){
							$addiotnal_covers[$key]['IsMaternity']=1;
						}
						foreach ($addCover->addCoverValue as $key2 => $acv) {
							$addiotnal_covers[$key]['options'][$key2]['name'] = $acv->name;
							$addiotnal_covers[$key]['options'][$key2]['value'] = $acv->value;
							$addiotnal_covers[$key]['options'][$key2]['id'] = $acv->id;
						}
					}
				}//end foreach
			}//end if

			$data['plans'][$val['id']] = $val;
		}

		//payment data
		$data['number_payments'] = NumberPayments::select('id', 'description as name')->get();
		$data['payment_method'] = PaymentMethod::select('id', 'display as method')->get();

		//affiliate relationship
		$data['affiliate_role'] = AffiliateRole::where('name',"!=",'titular')
												 ->lists('name', 'id');

		//get temp generated data if any
		$data['temp_data'] = RequestPolicyData::where('process_id', $process_ID)->orderBy('id', 'desc')->value('data');

		//$data['temp_data'] = RequestPolicyData::where('process_id', $previousProcessEntryID)->orderBy('id', 'desc')->value('data');
		$data['temp_data'] = ( empty($data['temp_data']) ) ? '' : $data['temp_data'];

		$prc = ProcessCatalog::where('name', 'InitialDocumentation')->value('id');
		$previousProcedureEntryID = ProcessEntry::where('id', $process_ID)->value('procedure_entry_id');
		$previousProcessEntryID = ProcessEntry::where('procedure_entry_id', $previousProcedureEntryID)
				->where('process_catalog_id', $prc)
				->value('id');

		//get prev customer data if needed
		$tmp = (array)json_decode($data['temp_data']);
		//$tmp = (array)($data['temp_data']);
		if( !isset($tmp['customer_obj']) || empty($tmp['customer_obj'])  ){
		  //get previous saved data
			$prev_data = RequestPolicyData::where('process_id', $previousProcessEntryID)->orderBy('id', 'desc')->value('data');
			$prev_data = (array)json_decode($prev_data);

			if( count($prev_data) > 0 ){
				$tmp['customer_obj'] = array();
				foreach( $prev_data as $key=>$val ){
					$tmp['customer_obj'][$key] = $val;
				}

				$tmp['customer_obj']['pid_num'] = (isset($tmp['customer_obj']['identity_document'])) ? $tmp['customer_obj']['identity_document'] : '';
				$tmp['max_step'] = 1;
				$data['temp_data'] = json_encode($tmp);
			}
		}//end if prev cust data

		//get prev plan data if needed
		if( !isset($tmp['plan_obj']) || empty($tmp['plan_obj'])  ){
			$prev_planid = RequestPolicyData::where('process_id', $previousProcessEntryID)->orderBy('id', 'desc')->value('plan_id');
			if( $prev_planid ){
				$tmp['plan_obj'] = array();
				$tmp['plan_obj']['id'] = $prev_planid;
				$tmp['plan_obj']['option'] = 0;

				$data['temp_data'] = json_encode($tmp);
			}
		}//end if prev plan data

		//get the previnsurance data id needed
		if( !isset($tmp['eDatePrevInsurance_obj']) || empty($tmp['eDatePrevInsurance_obj']) ){
			$prev_insurance_data = RequestPolicyData::where('process_id', $previousProcessEntryID)
														->first()
														->data;
			$prev_insurance_data = (array)json_decode($prev_insurance_data);

			if(isset($prev_insurance_data['prev_insurance']) &&
				$prev_insurance_data['prev_insurance']==true){
				$tmp['eDatePrevInsurance_obj'] = array();
				$tmp['eDatePrevInsurance_obj']['prev_insurance'] = true;
				$tmp['eDatePrevInsurance_obj']['prev_insurance_comp'] =
													$prev_insurance_data['prev_insurance_comp'];

				$data['temp_data'] = json_encode($tmp);
			}
		}


		//observations data
		$data['observation_type'] = ObservationType::pluck('name', 'id');

		//files data
		$process = ProcessUploadPolicyRequest::findProcess($process_ID);
		$procedure = $process->procedureEntryRel;
		$fe = \App\FileEntry::where("table_type", 'procedure_entry')
										->where('table_id',$procedure->id)
										->get();

		$decoded = array();
		foreach($fe as $file){
			$fdata=(array)json_decode($file->data);
			$key = $fdata['ts'];
			$decoded[$key]['id']=$file->id;
			$decoded[$key]['name']=$file->original_filename;
			$decoded[$key]['description']=$file->description;
			$decoded[$key]['category']=$fdata['procedure_document_id'];
		}

		$data['files'] = $decoded;

		$data['effectiveDate'] = $this->generateDates();

		$this->novaMessage->setData($data);
		return $this->returnJSONMessage();
	}

	public function uploadPolicyRequestSteps(Request $request, $process_ID, $step){
		$data = array();
		//TODO manage error presentation properly
		try{
			//TODO validate process id exists and not finished
			$process = ProcessUploadPolicyRequest::findProcess($process_ID);
			if( empty($process) || !$process->isActive() ){
				throw new \Exception('invalid process');
			}

			$procedure = $process->procedureEntryRel;

	  		$request_policy_data = RequestPolicyData::where('process_id', $process_ID)->first();

	  		if( empty($request_policy_data) ){
	    		$prc = ProcessCatalog::where('name', 'InitialDocumentation')->value('id');
	    		$previousProcedureEntryID = ProcessEntry::where('id', $process_ID)->value('procedure_entry_id');
	    		$previousProcessEntryID = ProcessEntry::where('procedure_entry_id', $previousProcedureEntryID)
	                                          ->where('process_catalog_id', $prc)
	                                          ->value('id');

	    		$previous_data = RequestPolicyData::where('process_id' , $previousProcessEntryID)->first();
	    		$request_policy_data = RequestPolicyData::firstOrNew(['process_id' => $process_ID]);
	    		$request_policy_data->agente_id = $previous_data->agente_id;
	    		$request_policy_data->plan_id = $previous_data->plan_id;
	    		$request_policy_data->data = json_encode(array());
	  		}

  			$data = (Array)json_decode($request_policy_data->data);
			$wiz_obj = $request->input('wiz_obj', '');

			if(empty($wiz_obj) && ($step!=7) ){
				throw new \Exception('invalid data sent');
			}

			$temp = (Array)json_decode($wiz_obj);

			//observations
			$observations = array();
			$observ_ref = '';
			$obs_obj = $request->input('obs_obj', '');

			if( !empty($obs_obj) ){
				$obs_obj = (Array)json_decode($obs_obj);

				if( count($obs_obj) > 0 ){
					foreach( $obs_obj as $observ){
						$observ = (Array)$observ;
						ObservationValidator::validateCreatePolicyFormData($observ);
						$observations[] = $observ;
					}
				}
			}//end if

			if(isset($data['max_step'])){
				if($data['max_step']<$step){
					$data['max_step']=$step;
				}
			}else{
				$data['max_step'] = $step;
			}

			switch($step){
				case 1:
					CustomerValidator::validateCreatePolicyFormData($temp);

					//validate date entered >= 18
					$dob = new Carbon($temp['dob']);

					if( $dob->age < 18 ){
						throw new \Exception('customer_age_not_allowed');
					}

					$data['customer_obj'] = $temp;
					$observ_ref = 'customer_obs';
				break;

				case 2:
					PlanValidator::validateCreatePolicyFormData($temp);
          			$data['plan_obj'] = $temp;
					$request_policy_data->plan_id = $temp['id'];
					$observ_ref = 'plan_obs';
				break;
				case 3:
					if( count($temp) > 0 ){
						$spouse_counter = 0;

						foreach( $temp as $affiliate ){
							//print_r($affiliate);
							$affiliate = (Array)$affiliate;
							AffiliateValidator::validateCreatePolicyFormData($affiliate);
							$spouse_minage = 18;

							//validate role spouse needs age greater or equal than 18
							if( AffiliateRole::isSpouse($affiliate['role']) ){

								if( $spouse_counter > 0 ){
									throw new \Exception("invalid affiliate role");
								}

								$dob = new Carbon($affiliate['dob']);
								if( $dob->age < $spouse_minage ){
									throw new \Exception("invalid affiliate age");
								}

								$spouse_counter++;
							}
						}
					}else{
						//print_r("no manda nada");
					}
					$data['aff_obj'] = $temp;
					$observ_ref = 'aff_obs';
				break;

				case 4:
					if( count($temp) > 0 ){
						foreach( $temp as $section ){
							$section = (Array)$section;
							if( !isset($section['id']) || !isset($section['resp']) ){
								throw new \Exception("invalid quiz");
							}
						}
					}
					else{
						throw new \Exception("invalid quiz");
					}
					$data['quiz_obj'] = $temp;
					$observ_ref = 'quiz_obs';
				break;

				case 5:
					PaymentValidator::validateCreatePolicyFormData($temp);
					$data['payment_obj'] = $temp;
					$observ_ref = 'payment_obs';
				break;

				case 6:
					//effective date
					PolicyEmissionValidator::validateEffectiveDatePrevInsurance($temp);
					$data['eDatePrevInsurance_obj'] = $temp;
					$observ_ref = 'eDatePrevInsurance_obj';
				break;

				case 7:
					if( isset($request['deleteFile']) ){
						//delete the file send in the request
						$resp = $this->deteleFileProcess($request, $procedure, $process_ID);
						$this->novaMessage->setData($resp);
						$this->novaMessage->addSuccesMessage('SUCCESS', 'file deleted');
				    	return $this->returnJSONMessage();
					}else if( isset($request['file']) ){
						$resp = $this->reUploadFiles($request, $procedure, $process_ID);
						$this->novaMessage->setData($resp);
						$this->novaMessage->addSuccesMessage('SUCCESS', 'file uploaded');
				    	return $this->returnJSONMessage();
					}
				break;

				default:
					throw new \Exception('invalid option');
				break;
			}
		}catch( \Exception $e ){
			$this->novaMessage->addErrorMessage('WRONG INPUT', $e->getMessage());
			return $this->returnJSONMessage(422);
		}

		//save observations into db
		foreach($observations as $o){
			$o_item_id = ( isset($o['item_id']) ) ? $o['item_id'] : 0;
			$tmp_obj = Observation::firstOrNew(['process_id' => $process_ID,
								'item_id' => $o_item_id,
								'item_ref'=>$observ_ref]);
			$tmp_obj->content = $o['content'];
			$tmp_obj->type_id = $o['type_id'];
			$tmp_obj->status = 0;
			$tmp_obj->save();
		}

		$request_policy_data->data = json_encode($data);
		$request_policy_data->save();
		$this->novaMessage->setData($request_policy_data);
		$this->novaMessage->addSuccesMessage('SUCCESS', 'temp data saved');
    	return $this->returnJSONMessage();
	}

	public function uploadPolicyRequestSummary($process_ID){
		$data = array();
		//TODO validate process id exists and not finished

		//previous uploaded Files
		$previousProcedureEntryID = ProcessEntry::where('id', $process_ID)->value('procedure_entry_id');

		$attachments = FileEntry::where('table_type','procedure_entry')
								->where('table_id',$previousProcedureEntryID)
								->select('id', 'mime', 'original_filename as name', 'description')
								->get();

		$data['file_list'] = $attachments;

		$observations = Observation::with(array('type'=> function($query){
				$query->addSelect(array('id', 'name'));
		}))->where('process_id', $process_ID)
			 ->select('id', 'item_id', 'item_ref', 'content', 'type_id')->get();

		$data['observations'] = array();
		foreach( $observations as $observation ){
			$categ = ( isset($observation->item_ref) && !empty($observation->item_ref) ) ? $observation->item_ref : 'other';
			$x = array('id' => $observation->id,
								 'content' => $observation->content,
								 'type_id' => $observation->type_id,
								 'type_name' => $observation->type->name
								);
			$data['observations'][$categ][] = $x;
		}

		$this->novaMessage->setData($data);
		$this->novaMessage->addSuccesMessage('SUCCESS', '');
    	return $this->returnJSONMessage();
	}

	public function uploadPolicyRequestSummaryProcess($process_ID){
		//check process exists and not finished

		$procedureEntryID = ProcessEntry::where('id', $process_ID)->value('procedure_entry_id');

		//observations belongs to procesID

		//file belongs to procedureID
		$file_ids = FileEntry::where('table_type','procedure_entry')
								->where('table_id',$procedureEntryID)
								->lists('id');

		//process observation status

		$this->novaMessage->setData($file_ids);
		$this->novaMessage->addSuccesMessage('SUCCESS', 'temp data saved');
    	return $this->returnJSONMessage();
	}

	public function uploadPolicyRequest(Request $request, $process_ID){
		\DB::beginTransaction();
        try{
			// TODO validate processId exists and not finished
			$pup = ProcessUploadPolicyRequest::findProcess($process_ID);
			// TODO validate process ID has no tickets associated

			$rpd = RequestPolicyData::where('process_id', $process_ID)->first();

		    if( empty($rpd) ){
				throw new \Exception('invalid data');
		    }

			$temp_data = (Array)json_decode($rpd->data);
			//validate $temp_data complete
			PolicyEmissionValidator::validateCreateFromTempData($temp_data);

			//parse temp data
			$customer = (Array)$temp_data['customer_obj'];
			CustomerValidator::validateCreatePolicyFormData($customer);

			$plan = (Array)$temp_data['plan_obj'];
			PlanValidator::validateCreatePolicyFormData($plan);
			$plan_id = $plan['id'];
			$plan_option = $plan['option'];
			$plan_addcover = $plan['add_covers'];

			$affiliates = (Array)$temp_data['aff_obj'];
			$quiz = (Array)$temp_data['quiz_obj'];

			$payment = (Array)$temp_data['payment_obj'];
			PaymentValidator::validateCreatePolicyFormData($payment);
			$payment_method = $payment['payment_method'];
			$payment_number = $payment['payment_number'];

			$eDataPrevInsurance = (Array)$temp_data['eDatePrevInsurance_obj'];
			//data start and end policy
			$effectiveDate = $eDataPrevInsurance['effective_date'];
			$start_date=date('Y-m-d', strtotime($effectiveDate));
			$end_date=date('Y-m-d',
							strtotime('+1 year',
								  strtotime($effectiveDate)
									  )
							);

			$prev_insurance = isset($eDataPrevInsurance['prev_insurance'])?
												$eDataPrevInsurance['prev_insurance']:
												false;

			//TODO validate agente exists
			$agente_id = $rpd->agente_id;

			//create customer if not exists
			$customer_obj = Customer::withTrashed()
									->where(['pid_num' => $customer['pid_num'], 
										'pid_type' => $customer['pid_type'] ])
									->first();
			if($customer_obj==null){
				$customer_obj = new Customer();
				$customer_obj->pid_num  = $customer['pid_num'];
				$customer_obj->pid_type = $customer['pid_type'];
			}else{
				$customer_obj->restore();
			}

			$customer_obj->name = $customer['name'];
			$customer_obj->lastname = $customer['lastname'];
			$customer_obj->address = $customer['address'];
			$customer_obj->phone = $customer['phone'];
			$customer_obj->mobile = $customer['mobile'];
			$customer_obj->email = $customer['email'];
			$customer_obj->country_id = $customer['country_id'];
			$customer_obj->state_id = $customer['state_id'];
			$customer_obj->city_id = $customer['city_id'];
			$customer_obj->save();

			//create affiliates if not exist
			//try to create an affiliate from customer data
			//get owner role
			$owner_role_id = AffiliateRole::where('name', 'titular')->value('id');
			if( empty($owner_role_id) || ($owner_role_id < 1) ){
				throw new \Exception('owner role missing');
			}
			$customer['role'] = $owner_role_id;
			$affiliates[] = $customer;
			$affs = array();
			$owner_check = 0;
			$deafult_pid_type = 1;
			$docType = Person::getDoctypeList();
			foreach ($docType as $key => $value) {
				if($value =='cedula'){
					$deafult_pid_type = $key;
				}
			}

			$affRolesList = AffiliateRole::pluck("name","id");
			$sexList = Person::getSexList();

			foreach( $affiliates as $affiliate ){
				$affiliate = (Array)$affiliate;
				if( $affiliate['role'] == $owner_role_id ){
					$aff_obj = Affiliate::withTrashed()
									->where(['pid_num' => $affiliate['pid_num'], 
											'pid_type' => $affiliate['pid_type'] ])
									->first();
					if($aff_obj == null){
						$aff_obj = new Affiliate();
						$aff_obj->pid_num  = $affiliate['pid_num'];
						$aff_obj->pid_type = $affiliate['pid_type'];
					}else{
						$aff_obj->restore();
					}
				}else{
					$hash = hash('sha256',$affiliate['name'].$affiliate['lastname'].$affiliate['dob'].$customer_obj->pid_num.$customer_obj->pid_type);
					$aff_obj = Affiliate::withTrashed()
									->where(['pid_num' => $hash, 
											'pid_type' => $deafult_pid_type ])
									->first();
					if($aff_obj == null){
						$aff_obj = new Affiliate();
						$aff_obj->pid_type = $deafult_pid_type;
						$aff_obj->pid_num = $hash;
					}
				}

				$aff_obj->name = $affiliate['name'];
				$aff_obj->lastname = $affiliate['lastname'];
				$aff_obj->sex = $affiliate['sex'];
				$aff_obj->dob = $affiliate['dob'];

				//transform unit if needed to save height and weigth in cm and lb
				if($affiliate["heightu"]=="m"){
					$affiliate['height'] = HelperConvertUnits::metersToCm(
												$affiliate['height']);
				}

				if($affiliate["weightu"]=="kg"){
					$affiliate['weight'] = HelperConvertUnits::kgToLb(
												$affiliate['weight']);
				}

				$aff_obj->height = $affiliate['height'];
				$aff_obj->weight = $affiliate['weight'];
				$aff_obj->save();

				$affs[] = array('id' => $aff_obj->id, 'role' => $affiliate['role'] , "aff_obj" => $aff_obj);

				if( $affiliate['role'] == $owner_role_id ){
					$owner_check++;
					if($owner_check > 1){
						throw new \Exception("only one policy owner allowed");
					}
				}
			}

			//throw new \Exception("before policy create");
			//create policy
			$actual_date = date('Y-m-d');

			//choosse the correct plan type, according to the number of affiliate in the polocy
			if(count($affs)==1){
				$plan_type_id = PlanType::where("num_members",1)
							->first()
							->id;
			}else if(count($affs)==2){
				$plan_type_id = PlanType::where("num_members",2)
							->first()
							->id;
			}else{
				$plan_type_id = PlanType::where("num_members",">",2)
							->first()
							->id;
			}

			$policy_obj = Policy::create([
				'policy_number' => 0,
				'plan_deducible_id' => $plan_option,
				'agente_id' => $agente_id,
				'payments_number_id' => $payment_number,
				'plan_type_id' => $plan_type_id,
				'emision_number' => 1,
				'endoso_number' => 0,
				'renewal_number' => 0,
				'ptype' => 1,
				'customer_id' => $customer_obj->id,
				'parent_id' => 0,
				'start_date' => $start_date,
				'end_date' => $end_date,
				'emission_date' => $actual_date
			]);

			$deductibles = DeducibleOptions::where('plan_deducible_id', $plan_option)->get();
			if( empty($deductibles) ){
				throw new \Exception("no deductibles found");
			}

			//create affiliate policy entries
			foreach( $affs as $x ){
				$ap = AffiliatePolicy::create([
					'affiliate_id' => $x['id'],
					'policy_id' => $policy_obj->id,
					'premium_amount' => '0',
					'role' => $x['role'],
					'effective_date' =>  $start_date
				]);


				//create affiliate_policy_deducible entry
				foreach( $deductibles as $d ){
					$tmp = AffiliatePolicyDeducible::firstOrNew(['plan_deducible_type_id'=>$d->plan_deducible_type_id, 'affiliate_policy_id'=>$ap->id]);
					$tmp->save();
				}

				//create affiliate policy, additional cover
				foreach ($plan_addcover as $key => $addCoverValueId) {
					$acv = \Modules\Plan\Entities\PlanDeducibleAdditionalCoverValue::with("addCover")->find($addCoverValueId);
					
					$create =false;
					if( $acv['addCover']['name']=="Maternity Complications Rider" ){
						if( ($affRolesList[$x['role']]=="titular" || 
							 $affRolesList[$x['role']]=="esposo(a)")
							&&
							($sexList[$x['aff_obj']['sex']] == "femenino")  ){
							$create =true;
						}
					}else{
						$create =true;
					}

					if($create){
						\Modules\Affiliate\Entities\AffiliatePolicyAdditionalCover::create([
							"effective_date" => $start_date,
							"pd_acv_id" => $addCoverValueId,
							"affiliate_policy_id" => $ap->id
						]);
					}
				}
			}

			//throw new \Exception("Error Processing Request", 1);
			

			//create policy deductibles
			foreach( $deductibles as $d ){
				PolicyDeducible::create([
					'policy_id' => $policy_obj->id,
					'plan_deducible_type_id' => $d->plan_deducible_type_id,
					'amount' => $d->value
				]);
			}

			//previos insurance data, save
			//prev_insurance_data
			if($prev_insurance){
				PrevInsurancePolicy::create([
										"policy_id"           => $policy_obj->id,
										"company_name"        =>
												$eDataPrevInsurance["prev_insurance_comp"],
										"plan_name" 		  =>
												$eDataPrevInsurance["prev_insurance_plan"],
											]);

				if(isset($eDataPrevInsurance["prev_insurance_continue"]) && $eDataPrevInsurance["prev_insurance_continue"]){
					$policy_obj->extend_prev_insurance=1;
					$policy_obj->save();
				}
			}

			//register values of policy
			$pcs = new PolicyCostService($policy_obj);
			$pcs->registerPolicyCosts();

			//throw new \Exception("before procedure create");

      		$procedure=ProcedureEntry::find($pup->procedure_entry_id);
			$procedure->policy_id=$policy_obj->id;
			$procedure->save();

            $pup->finish();
						//throw new \Exception("before commit");
            \DB::commit();
            $this->novaMessage->setRoute(
                    'reception/');
            return $this->returnJSONMessage(201);

        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('INVALID',$e->getMessage());
            return $this->returnJSONMessage(422);
        }
	}

	private function reUploadFiles(Request $request, $procedure, $process_ID){
		try{
			\DB::beginTransaction();
			$category = $request->input('category', false);
			$description = $request->input('description', '');
			$ts = $request->input('ts', false);
			$table_type = 'procedure_entry';
			$old_fid = $request->input('fid', false);

			if( empty($ts) || empty($category) ){
				throw new \Exception('some data missing');
			}

			$params = array();
			$params['fieldname'] = 'file';
			$params['subfolder'] = 'newPolicy/'.$procedure->id;
			$params['table_type'] = $table_type;
			$params['table_id'] = $procedure->id;
			$params['data'] = json_encode(array('ts'=>$ts,
									'procedure_document_id'=>$category,
									'process_id' => $process_ID));
			$params['multiple'] = false;

			if( $old_fid ){
				//if get file id try to update
				$updated = $this->updateFile($request, $old_fid, $params);
				$uploadedFiles = array($updated);
			}
			else{
				$uploadedFiles = $this->uploadFiles($request, $params);
			}

			\DB::commit();
			return ["ts"=>$ts,"id"=>$uploadedFiles[0]];
		}catch( \Exception $e ){
			\DB::rollback();
			throw $e;
		}
	}//end reUploadFiles

	private function deteleFileProcess(Request $request, $procedure, $process_ID){
    	try{
    		$input = $request->all();

	    	if(!isset($input["fid"])){
	    		throw new \Exception("Petición es inválida");
	    	}

	    	$fid = $input["fid"];
	    	$fe = FileEntry::find($fid);
	    	if(!($fe->table_type=="procedure_entry" &&
	    		$fe->table_id==$procedure->id)){
	    		throw new \Exception("Archivo es inválido");
	    	}

	    	$this->deleteFile($fe->id);
    	}catch( \Exception $e ){
    		\DB::rollback();
			throw $e;
    	}
    }//end deleteFile

	private function generateDates(){
        $carbon = new Carbon();
        $month=$carbon->month;
        $day=$carbon->day;
        $year=$carbon->year;

        $dates=array();
        if($day>1 && $day<=15){
            $dmont = $month;
            if($month<10){
                $dmont = "0".$month;
            }
            $dates[$year.'-'.$month.'-15']='15/'.$dmont.'/'.$year;
        }else{
            $firstday=1;
        }
        $month=$month+1;

        $month=$month-2;
        for($i=0;$i<8;$i++){
            $dmont = $month;
            if($month<10){
                $dmont = "0".$month;
            }

            $dates[$year.'-'.$month.'-1']='01/'.$dmont.'/'.$year;
            $dates[$year.'-'.$month.'-15']='15/'.$dmont.'/'.$year;
            if($month==12){
                $year=$year+1;
                $month=1;
            } else {
            	$month++;
            }
        }
        return $dates;
    }

   // public function emailData($process_ID,Request $request){
   	 public function emailData($process_ID){
    	\DB::beginTransaction();
        try{
			// TODO validate processId exists and not finished
			$pup = ProcessUploadPolicyRequest::findProcess($process_ID);
			// TODO validate process ID has no tickets associated

			$rpd = RequestPolicyData::where('process_id', $process_ID)->first();

		    if( empty($rpd) ){
				throw new \Exception('invalid data');
		    }

		    $appData = (array)json_decode($rpd['data']);
		    $paymentData = (array)$appData["payment_obj"];
			$dataTemplate['request_invoice'] = false;
			$dataTemplate['request_discount'] = false;
			if($paymentData['request_discount']){
				$dataTemplate['request_discount'] = true;
				$dataTemplate['per_discount'] = $paymentData["discount_percentage"];
				$pm=PaymentMethod::find($paymentData['payment_method'])->display;
				$dataTemplate["payment_method"] = $pm;
			}
			if($paymentData['request_invoice']){
				$dataTemplate['request_invoice'] = true;
			}

    		$param["content"] = $pup->getTemplateEmail($dataTemplate);
    		$param["attachments"] = array();
    		foreach ($pup->getFilesSendEmail() as $key => $file) {
    			$param["attachments"][$key]['name'] = $file['display'];
    			$param["attachments"][$key]['id'] = $file['id'];
    		}

    		$planobj = (array)$appData["plan_obj"];
			//get InsuranceCompany to which the process is related 
			$insuranceCompany=Plan::find($planobj['id'])->insuranceCompany;
			$emailComp=InsuranceCompanyEmail::
								where('insurance_company_id',$insuranceCompany->id)
								->where('reason',$pup->email_template_reason)
								->first();
			if($emailComp==null){
				throw new 
					\Exception("Email configuration for insurance company not found", 1);
			}

			$customerObj = (array)$appData["customer_obj"];
			$to['address']=trim($emailComp->email);
			$to['name']=$emailComp->contact_name;
			$param['subject']=$emailComp->subject;
			$agente = Agente::find($customerObj['agente_id']);
			//copy to the agent that sold the policy and copy to the user that is doing the process
			$user = JWTAuth::parseToken()->authenticate();
			//$param['cc']=array($user->email,$agente->email);
			//$param['cc']=array("mauricio.guzmanjc@gmail.com");
			// cambios 
			$param['to']=array($to['name']." <".$to['address'].">");

			$this->novaMessage->setData($param);
            return $this->returnJSONMessage(201);
    	}catch(\Exception $e){
            //show message error
            $this->novaMessage->addErrorMessage('INVALID',$e->getMessage());
            return $this->returnJSONMessage(422);
    	}
    }

}
