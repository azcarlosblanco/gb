<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\Claim;
use Modules\Claim\Entities\ClaimFile;
use Modules\Claim\Entities\ClaimProcedure;
use Modules\Claim\Entities\ClaimSettlement;
use Modules\Claim\Entities\SettlementCalculator;
use Modules\Claim\Entities\ClaimSettlementRefund;
use Modules\Claim\Services\Validation\ClaimValidator;
use Modules\Plan\Entities\PlanDeducibleType;
use App\ProcedureEntry;
use App\ProcessEntry;
use JWTAuth;
use Modules\Authorization\Entities\Role;
use Modules\Agente\Entities\Agente;
use Modules\Payment\Entities\PaymentMethod;
use Carbon\Carbon;
use Modules\ClientService\Entities\Ticket;
use Modules\ClientService\Entities\TicketDetail;
use Modules\ClientService\Entities\TicketCat;
use App\UploadAndDownloadFile;

class ClaimSettlementController extends NovaController {
	use \App\UploadAndDownloadFile;

	function __construct(){
    	parent::__construct();
	}

	/*
	 * Return the procedures that are pending and has been asigned to
	 * the user that is logged
	 */
	public function pendingSettlements(Request $request)
	{

		$content=[];
		$user = JWTAuth::parseToken()->authenticate();
		$roles_user=$user->roles->lists('name');
		$manager=false;
		foreach ($roles_user as $value) {
			if(in_array($value,['administracion','claims_manager'])){
				$manager=true;
			}
		}

		$userID=($manager)?"":$user->id;
		$roleID=Role::where('name','claims')->first()->id;

		$procedures = ProcedureEntry::whereHas('procedureCatalog',function ($query) {
										    $query->where('name', 'settlement');
										})
									->with('procedureCatalog')
									->pending();


		if($userID!=""){
			$procedures->whereHas('processEntry',function ($query) {
								    $query->where('responsible', $userID);
								});
		}
		$procedures->with('processEntry');


		$result = array();
		$index = 0;
		$procedures = $procedures->with('claims.affiliatePolicy')
							->with('policy')
							->get();

		$diagnosisLis = \App\Diagnosis::pluck('display_name','id');
		$today = Carbon::now();
		$invoiceDocID = \App\ProcedureDocument::where('name','claim_invoice')
												->first()
												->id;
		//print_r($procedures);

		$actionButtons=[];
		foreach ($procedures as $procedure) {
			//print_r($procedure);
			//when the procedure is a settlement, there is only one claim associate with the
			//procedure
			$claim = $procedure->claims()->first();
			if(empty($actionButtons)){
				$procedure->load('procedureCatalog');
				$actionButtons = $procedure->getListActionButtons($roleID);
			}

			$buttons=$procedure->getListButtonsV2($actionButtons);

			//just display the settlement is the procedure has an active process in this module
			if(isset($buttons['current_description'])){
				$result[$index]['policy_number'] = $procedure->policy->policy_number;
				$result[$index]['num_claim'] = $claim->id;
				$affiliate = $claim->affiliatePolicy
									->affiliate;
				$result[$index]['affiliate'] = $affiliate->getFullNameAttribute();
				$result[$index]['diagnosis'] = $diagnosisLis[$claim->diagnosis_id];

				$files = $claim->files()->where('procedure_document_id',$invoiceDocID)
												->select('description','amount')
												->get();
				$totalAmount = 0;
				$invoices = array();
				foreach ($files as $value) {
					$invoices[]=$value['description'];
					$totalAmount+=$totalAmount;
				}
				$result[$index]['total_amount'] = $totalAmount;
				$result[$index]['invoices'] = implode("\n",$invoices);

				$result[$index]['pcd_start_date'] = date('d-m-Y',strtotime($procedure->start_date));

				$result[$index]['num_days_elapsed'] = $today->diffInDays(
											new Carbon($procedure->start_date));

				$result[$index]['buttons'] = $buttons['buttons'];
				$result[$index]['searchingField']=strtoupper(
										$result[$index]['policy_number'].
										$result[$index]['affiliate'].
										$result[$index]['num_claim'].
										$result[$index]['diagnosis'].
										$result[$index]['invoices']
											);
				$index++;
			}
		}

		//search
		$search="";
		if($request->has('search_data')){
			$search=strtoupper($request['search_data']);
		}
		$list=array();
		foreach ($result as $values) {
			if ($search==="" || strpos($values['searchingField'], $search) !== false)
				$list[]=$values;
		}

		if($request->has('withView')){
			$this->novaMessage->setData($this->renderIndex($list));
		}else{
			$this->novaMessage->setData($list);
		}
		return $this->returnJSONMessage();
	}

	private function renderIndex($content){
		$index['display']['title']='Liquidaciones - Trámites Pendientes';
		$index['display']['header']=array(
						array('label' =>'# Póliza',
                              'filterType'=>'text',
                              'fieldName' =>'policy_number'),
                        array('label' =>'# Reclamo GB',
                              'filterType'=>'text',
                              'fieldName' =>'num_claim'),
                        array('label' =>'Afiliado',
                        	  'filterType'=>'text',
                              'fieldName' =>'affiliate'),
                        array('label' =>'Diagnóstico',
                        	  'filterType'=>'text',
                              'fieldName' =>'diagnosis'),
                        array('label' =>'Monto',
                        	  'filterType'=>'text',
                              'fieldName' =>'total_amount'),
                        array('label' =>'Facturas',
                        	  'filterType'=>'text',
                              'fieldName' =>'invoices'),
                        array('label' =>'Inicio Trámite',
                        	  'filterType'=>'date',
                              'fieldName' =>'pcd_start_date'),
                        array('label' =>'# Días Pasados',
                        	  'filterType'=>'number',
                              'fieldName' =>'num_days_elapsed'),
                        array('label' =>'Acciones',
                              'fieldName' =>'buttons')
                    );
        $index['list']=$content;
        return $index;
	}

	private function validateProcess($process_ID, $process_class){
		if( empty($process_ID) ){
			throw new \Exception('invalid process ID');
		}

		//check process exists and not finished
		$class = '\Modules\Claim\Entities\\'.$process_class;
		$process = $class::findProcess($process_ID);
		if( is_null($process) ){
			throw new \Exception('invalid process');
		}

		/*if( !$process->isActive() ){
			throw new \Exception('invalid process');
		}*/

		$procedure = $process->procedureEntryRel;

		//validate claim and procedure association exists
		$cp = ClaimProcedure::where('procedure_entry_id', $procedure->id)->first();

		if( empty($cp) ){
			throw new \Exception('invalid claim-procedure');
		}

		//validate claim exists is not finished
		$claim = $cp->claim;
		/*if($claim->status == 1){
			throw new \Exception('invalid claim');
		}*/

		//validate file ids exist and belong to current procedure

		return array('process'=>$process, 'procedure'=>$procedure, 'cp'=>$cp, 'claim'=>$claim);
	}

	public function getSettlementInfo($process_ID){
		//try{
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRegister');
			extract($resp);

			$data['payments_method'] = PaymentMethod::pluck('display', 'id');
            $data['currencies'] = \App\Currency::pluck("display_name",'id');
            $procedureDocument = \App\ProcedureDocument::select("name","description","id")
            											->get()
            											->keyBy('name');
            $data['categories'] = array();
            $data['categories_settlement'] = array();
            foreach ($procedureDocument as $key => $value) {
            	$data['categories'][$value->id] = $value->description;
            	if($value->name=="claim_settlement_pay" || $value->name=="claim_settlement_eob"){
            		$data['categories_settlement'][$value->id] = $value->description;
            	}
            }

			//general info
			$data['claim']['id'] = $claim->id;
			$ap = $claim->affiliatePolicy;
			$data['claim']['affiliate_name'] = $ap->affiliate->full_name;
			$policy = $ap->policy;
			$policy_info = $policy->readableSummary();
			$data['claim']['policy_num'] = $policy_info['policy_number'];
			$data['claim']['policy_id'] = $policy->id;
			$data['claim']['plan'] = $policy_info['plan_name'];
			$data['claim']['agent_name'] = $policy_info['agente_name'];
			$data['claim']['customer_name'] = $policy_info['customer_name'];
			$data['claim']['effective_date'] = $policy->start_date;
			$data['claim']['claim_date'] = date("m/d/Y",strtotime($claim->created_at));

			//get deduct values to display
			$deducts=$this->getDeductValues($ap);
			foreach ($deducts as $key => $value) {
				$data[$key] = $value;
			}

			//claim files type invoice
			$invoice_type = \App\ProcedureDocument::where('name', 'claim_invoice')->value('id');

			$files = ClaimFile::with('settlement.files')
									->with('supplier')
									->where('claim_id', $claim->id)
									->where('procedure_document_id', $invoice_type)
									->get();
			$settlements = $invoices = $allFiles = array();
			foreach( $files as $file ){
				if( isset($file->supplier) ){
					$file->supplier_name = $file->supplier->name;
				}
				$allFiles[] = $file;

				if($file->procedure_document_id==$invoice_type){
					if( empty($file->settlement) ){
						$tickets = $this->getTicektInfo($file->id,null);
						$file['tickets'] = $tickets;
						$file['refunds'] = array();
						$invoices[] = $file;
					}
					else{
						$tickets = $this->getTicektInfo($file->id,$file->settlement->id);
						$file['tickets'] = $tickets;
						$file['refunds'] = $file->settlement->refunds()->withTrashed()->get();
						$settlements[] = $file;
					}
				}
			}

			//get the claim_letter and claim_form
			$claim_form_letter = \App\FileEntry::select('id', 'description', 'original_filename')
                                  	->where('table_type', 'claim')
                                   	->where('table_id', $claim->id)
                                   	->where(function ($query) {
							                $query->where('description', 'claim_form')
							                      ->orWhere('description', 'claim_letter');
							            })
                                   	->get();
            $index = count($allFiles);
            foreach ($claim_form_letter as $file) {
            	$allFiles[$index] = array();
            	$allFiles[$index]['file_entry_id'] = $file['id'];
            	$allFiles[$index]['procedure_document_id'] =
            								$procedureDocument[$file->description]['id'];
            	$allFiles[$index]['description'] =
            								$procedureDocument[$file->description]['description'];
            	$index++;
            }

			$data['allfiles'] = $allFiles;
			$data['settlements'] = $settlements;
			$data['not_associated'] = $invoices;

			//file uploaded in the settlement
			$up_files = \App\FileEntry::select('id', 'data', 'original_filename')
                                  	->where('table_type', 'procedure_entry')
                                   	->where('table_id', $procedure->id)
                                   	->get();
			$data['uploaded']=array();
			foreach ($up_files as $key => $value) {
				$data['uploaded'][$key]['id'] = $value['id'];
				$data['uploaded'][$key]['name'] = $value['original_filename'];
				$file_data=(array)json_decode($value['data']);
				$data['uploaded'][$key]['description']
							= $file_data['description'];
				if(isset($file_data['procedure_document_id'])){
					$data['uploaded'][$key]['procedure_document_id']
							= $file_data['procedure_document_id'];
				}else{
					$data['uploaded'][$key]['procedure_document_id'] = "";				}

			}

			$this->novaMessage->setData($data);
  			return $this->returnJSONMessage(200);
		//}catch(\Exception $e){
			//show message error
  			$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(404);
		//}
	}//end getSettlementInfo

	private function getTicektInfo($claimFileID, $settlementID=null){
		//get info about the ticket of file
		$tickets = Ticket::where("table_type","claim_file")
								->where("table_id",$claimFileID)
								->get();

		$data= array();
		$key1 = 0;
		foreach ($tickets as $key1 => $ticket) {
			$data[$key1]['id']=$ticket['id'];
    		$data[$key1]['display_name']=$ticket['ticket_cat']['display_name'];
		}

		if($settlementID!=null){
			$tickets = Ticket::where("table_type","claim_settlement")
								->where("table_id",$settlementID)
								->get();
			foreach ($tickets as $key2 => $ticket) {
				$data[$key1+$key2+1]['id']=$ticket['id'];
	    		$data[$key1+$key2+1]['display_name']=$ticket['ticket_cat']['display_name'];
			}
		}

		$ticket_array = [];
		foreach ($data as $value) {
			$ticket_array[] = $value;
		}
		return $ticket_array;
	}

	private function getDeductValues($ap){
		$data = array();

		//deductibles types
		$deductibles_types = PlanDeducibleType::all()->pluck('name', 'id');
  		$data['affiliate_deductibles'] = array();
		$aff_summary = 0;

		$policy = $ap->policy;

		//get affiliate deducibles
		foreach( $ap->deducibles as $d ){
			$type = $d->plan_deducible_type_id;
			$name = $deductibles_types[$type];
			$data['affiliate_deductibles'][$name] = $d->amount;
			$aff_summary += $d->amount;
		}

		//get global acumulated deducibles
		$num_aff_policy = count($policy->affiliates);
		$data['deduct_familiar'] = 0;
		$data['global_deductibles'] = array();
		if( $num_aff_policy > 1 ){
			$data['deduct_familiar'] = 1;
			$globalDeduct = $policy->getDeductiblesTotalsBD();
			foreach ($globalDeduct as $key => $deduct) {
				$data['global_deductibles'][$deductibles_types[$key]]=$deduct;
			}
		}

		//get policy ref deductibles
		$data['policy_deductibles'] = array();
		foreach( $policy->deducibles as $pd ){
			$name = $deductibles_types[$pd->plan_deducible_type_id];
			$data['policy_deductibles'][$name] = $pd->amount;
		}

		return $data;
	}

	public function saveSpecialSettlement(Request $request, $process_ID){
		//echo 'wtf';exit;
		try{
			\DB::beginTransaction();
			$invoice_type = \App\ProcedureDocument::where('name', 'claim_invoice')->value('id');
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRegister');
			extract($resp);
			$description = $request->input('description', '');
			$usa = $request->input('usa', 0);
			$date_invoice = $request->input('date_invoice', NULL);
			$amount = $request->input('amount', 0);
			$currency_id = $request->input('currency_id', 1);

			if( empty($amount) ){
				throw new \Exception('invalid amount');
			}

			$claim_file = new ClaimFile();
			$claim_file->claim_id = $claim->id;
			$claim_file->description = $description;
			$claim_file->usa = $usa;
			$claim_file->date_invoice = $date_invoice;
			$claim_file->amount = $amount;
			$claim_file->currency_id = $currency_id;
			$claim_file->amount = $amount;
			$claim_file->concept = 1;
			$claim_file->procedure_document_id = $invoice_type;
			$claim_file->save();

			$settlement = new ClaimSettlement();
			$settlement->claim_file_id = $claim_file->id;
			$settlement->serv_date = date('Y-m-d H:i:s');
			$settlement->amount = $amount;
			$settlement->uncovered_value = 0;
			$settlement->descuento = 0;
			$settlement->deducible = 0;
			$settlement->coaseguro = 0;
			$settlement->refunded = 0;
			$settlement->ic_num_claim = $description;
			$settlement->notes = $description;
			$settlement->save();

			//deductibles calculation
			$policy = $procedure->policy;
			//$policy->recalculateAffiliatesDeductibles();
			SettlementCalculator::updateDeductibleValuesBD($settlement, $policy);

			\DB::commit();

		}catch( \Exception $e){
			\DB::rollback();
			//show message error
  			$this->novaMessage
              			->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}

		$ap = $claim->affiliatePolicy;
		$deducts = $this->getDeductValues($ap);

		$this->novaMessage->setData(array('id'=>$settlement->id,
										'expected_refund' => $settlement->expected_refund,
										'expected_deduct' => $settlement->expected_deduct,
										'affiliate_deductibles' =>
										$deducts['affiliate_deductibles'],
										'global_deductibles' =>
										$deducts['global_deductibles']));

		return $this->returnJSONMessage(200);
	}

	public function saveSettlement(Request $request, $process_ID){
		try{
			$invoice_type = \App\ProcedureDocument::where('name', 'claim_invoice')->value('id');
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRegister');
			extract($resp);

			ClaimValidator::validateSettlementFormData($request);

			$claim_file = ClaimFile::findOrFail($request->input('cfid', 0));

			//validate claim file belongs to current claim and is an invoice
			if( ($claim_file->claim_id != $claim->id) || ($claim_file->procedure_document_id != $invoice_type) ){
				throw new \Exception('invalid claim');
			}

			//if claim file has an active settlement then update
			$settlement = ClaimSettlement::firstOrNew(['claim_file_id' => $claim_file->id]);

			//validate files exists for this process
			$files = (array)json_decode($request->input('files', ''));
			/*if( empty($files) ){
				throw new \Exception('no associated files');
			}*/

			$sfiles = array();
			foreach($files as $fid){
				$file = \App\FileEntry::findOrFail($fid);
				//does this file belongs to current procedure
				if(!( ($file->table_type == 'procedure_entry') && ($file->table_id == $procedure->id) )){
					throw new \Exception('invalid file to associate');
				}

				$fdata = (array)json_decode($file->data);
				$sfiles[] = array('id'=>$fid, 'paycheck'=>$fdata['paycheck']);
			}

			//calculate and validate amounts
			$amount = floatval($request->input('amount', 0));
			$uncovered = floatval($request->input('uncovered', 0));
			$dscto = floatval($request->input('dscto', 0));
			$deducible = floatval($request->input('deducible', 0));
			$coaseguro = floatval($request->input('coaseguro', 0));
			$refund = floatval($request->input('refund', 0));
			$ic_num_claim = $request->input('claim_num', "");

			$total = $amount - ($uncovered + $dscto + $deducible + $coaseguro + $refund);
			if( $total != 0 ){
				throw new \Exception('values do not match');
			}

			try{
				\DB::beginTransaction();

				if( isset($settlement->id) && !empty($settlement->id) ){
					//delete previous settlement files
					$settlement->files()->detach();
					$local_type = PlanDeducibleType::where('name', 'local')->firstOrFail();
  					$inter_type = PlanDeducibleType::where('name', 'usa')->firstOrFail();
      				$pd_type = ($claim_file->usa) ? $inter_type : $local_type;
      				$ap = $claim_file->claim->affiliatePolicy;
      				$apd_obj = $ap->deducibles()->where('plan_deducible_type_id', $pd_type->id)->first();

      				if( empty($apd_obj) ){
      					throw new \Exception('deducible invalido');
      				}
      				$apd_obj->amount = $apd_obj->amount - $settlement->expected_deduct;
      				$apd_obj->save();
				}
				else{
					//first time
					$settlement->serv_date = date('Y-m-d H:i:s');
				}

				//set data to save/update
				$settlement->claim_file_id = $claim_file->id;
				$settlement->amount = $amount;
				$settlement->uncovered_value = $uncovered;
				$settlement->descuento = $dscto;
				$settlement->deducible = $deducible;
				$settlement->coaseguro = $coaseguro;
				$settlement->refunded = $refund;
				$settlement->ic_num_claim = $ic_num_claim;
				$settlement->notes = $request->input('notes', '');
				$settlement->save();

				//associate files
				foreach($sfiles as $sfile){
					$settlement->files()->attach($sfile['id'], ['paycheck' => $sfile['paycheck']]);

				}

				//deductibles calculation
				$policy = $procedure->policy;
				//$policy->recalculateAffiliatesDeductibles();
				SettlementCalculator::updateDeductibleValuesBD($settlement, $policy);

				\DB::commit();
			}catch(\Exception $e ){
				\DB::rollback();
				//throw new \Exception('cannot create/update settlement');
				throw new \Exception($e->getMessage());
			}

			$ap = $claim->affiliatePolicy;
			$deducts = $this->getDeductValues($ap);

			$this->novaMessage->setData(array('id'=>$settlement->id,
											'expected_refund' => $settlement->expected_refund,
											'expected_deduct' => $settlement->expected_deduct,
											'affiliate_deductibles' =>
											$deducts['affiliate_deductibles'],
											'global_deductibles' =>
											$deducts['global_deductibles']));

  			return $this->returnJSONMessage(200);
		}catch(\Exception $e){
			//show message error
  			$this->novaMessage
              			->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}
	}//end saveSettlement

	public function removeSettlement($id){
		try{
			\DB::beginTransaction();

			$settlement = ClaimSettlement::findOrFail($id);
			$settlement->files()->detach();

			//get policy
			$policy = $settlement->claimFile->claim->affiliatePolicy->policy;
			$settlement->delete();

			//deductibles calculation
			$policy->recalculateAffiliatesDeductibles($id);

			\DB::commit();
			$this->novaMessage->setData(array('id'=>$id));
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			\DB::rollback();
			//show message error
  			$this->novaMessage->addErrorMessage('NOT DELETED',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}
	}//end removeSettlement

	public function viewRefund($settlement_ID,Request $request){
		try{
			$refunds = ClaimSettlementRefund::where('claim_settlement_id',$settlement_ID)
												->get();
			$this->novaMessage->setData($refunds);
			return $this->returnJSONMessage(200);
		}catch( \Exception $e){
			$this->novaMessage->addErrorMessage('Error',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}

	public function saveRefund(Request $request, $process_ID){
		try{
			\DB::beginTransaction();

			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRefund');
			extract($resp);

			//TODO:Add functionality to edit the refund

			ClaimValidator::validatePaymentFormData($request);
			$id = $request->input('set_id', 0);
			$paid = $request->input('paid', 0);
			$supplier = $request->input('to_supplier', 0);
			$pay_method = $request->input('pay_method', 0);
			$pay_date = $request->input('pay_date', NULL);
			$reference_number = $request->input('reference_number', "");

			if( $paid <= 0 ){
				throw new \Exception('invalid paid amount');
			}

			PaymentMethod::findOrfail($pay_method);

			$settlement = ClaimSettlement::findOrFail($id);
			$max_amount = $settlement->expected_refund;
			$register_refunded = $settlement->refunded;
			$refunded = $settlement->calculateRefunded();
			//deleted validations in the refund to allow them, register refund even thougth
			//there is errors in the settlement
			/*$to_refund = $paid + $refunded;
			if( $max_amount <= 0 ){
				throw new \Exception('invalid settlement');
			}
			/*if( $to_refund > $max_amount ){
				throw new \Exception('invalid refund amount');
			}*/

			if($paid > $register_refunded){
				throw new \Exception('invalid refund amount');
			}

			//create refund
			$refund = ClaimSettlementRefund::create([
				'value' => $paid,
				'payment_method_id' => $pay_method,
				'claim_settlement_id' => $settlement->id,
				'to_supplier' => $supplier,
				'pay_date' => date('Y-m-d', strtotime($pay_date)),
				'reference_number' => $reference_number
			]);
			\DB::commit();
			$this->novaMessage->setData($refund);
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			//show message error
			\DB::rollback();
  			$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}
	}//end function saveRefund

	public function createTicket(Request $request){
		try{
			\DB::beginTransaction();
			$input = $request->all();

			$tickeCatList = TicketCat::pluck('name','id');
			$tickeCatListDisplayName = TicketCat::pluck('display_name','id');

			$category = $input['ticket_cat_id'];

			$cf = ClaimFile::find($input['claim_file_id']);

			$policy = $cf->claim->affiliatePolicy->policy;

			$ticket = array();
			if($tickeCatList[$category]=='deductible_not_match'){
				$ticket['table_type']="claim_settlement";
				$ticket['table_id']=$cf->settlement->id;
				$ticket['short_desc']=$input['short_desc'];
			}elseif($tickeCatList[$category]=='value_uncovered'){
				$ticket['table_type']="claim_settlement";
				$ticket['table_id']=$cf->settlement->id;
				$ticket['short_desc']=$input['short_desc'];
			}elseif($tickeCatList[$category]=='invoice_no_settle'){
				$ticket['table_type']="claim_file";
				$ticket['table_id']=$cf->id;
				$ticket['short_desc']=$input['short_desc'];
			}else{
				$ticket['table_type']="claim_file";
				$ticket['table_id']=$cf->id;
				$ticket['short_desc']=$input['short_desc'];
			}

			$ticket['policy_id'] = $policy->id;
			$ticket['type_ticket'] = $category;

			$ticketObj = new Ticket();
			$ticketObj->creationticket($ticket);
			$ticketObj->save();

			//create ticket detail, send an email with the claim
			$tickeDetailObj = new TicketDetail();
	        $user = JWTAuth::parseToken()->authenticate();
	        $tickeDetail['user_id'] = $user->id;
	        $tickeDetail['ticket_id'] = $ticketObj->id;
	        $tickeDetail['type'] = TicketDetail::EMAIL;
	        /*$tickeDetail['email'] = $input['emailto'];
	        $tickeDetail['copy'] = $input['emailcc'];
	        $tickeDetail['comment'] = $input['emailcontent'];
	        $tickeDetail['internallistIds'] = $input['internallistIds'];
	        $tickeDetail['llistIds'] = $input['listIds'];
	    	$tickeDetail->followupticket($tickeDetail);*/
	    	$tickeDetail['email'] = "kaviles@bestdoctorsinsurance.com";
			$tickeDetail['copy'] = "";

			$tickeDetail['internallistIds'] = array();
			$tickeDetail['internallistIds'][0]['id'] =  $cf['file_entry_id'];
			$tickeDetail['internallistIds'][0]['name'] =  "factura ".$cf['description'];

			$dataemail = $this->getDefaultContent($tickeCatList[$ticket['type_ticket']],
													$cf);
			$tickeDetail['comment'] = $dataemail['content'];
			$tickeDetail['subject'] = $dataemail['subject'];
			$tickeDetailObj->followupticket($tickeDetail);
			$tickeDetailObj->save();
	    	\DB::commit();
			$this->novaMessage->setData(array('id'=>$ticketObj->id,
											 'display_name'=>$tickeCatListDisplayName[$category]));
  			return $this->returnJSONMessage(200);
		}catch(\Exception $e){
			\DB::rollback();
  			$this->novaMessage->addErrorMessage('Error',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}

	private function getDefaultContent($ticketCat,$cf){
		if($ticketCat == "deductible_not_match"){
			//applicable only when deducible was already saved
			$email['content'] = "Existen errores en los valores asignados al deducibles en la la factura de número ".$cf['description'];
			$email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
		}else if($ticketCat == "value_uncovered"){
			//applicable only when deducible was already saved
			$email['content'] = "Existen valores no cubiertos en la factura de número ".$cf['description']."\nLos valores no cubiertos corresponden a: ".$notes."\n Por favor indicar porque estos valores no fueron no cubiertos";
			$email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
		}else if($ticketCat == "invoice_no_settle"){
			//applicable only when deducible was already saved
			$email['content'] = "La factura ".$cf['description']." no fue procesada";
			$email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
		}else{
			//applicable only when deducible was already saved
			$email['content'] = "La factura ".$cf['description']." no es válida";
			$email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
		}
		return $email;
	}

	public function getTicketContent(Request $request){
		try{
			\DB::beginTransaction();
			$tickeCatList=TicketCat::where("category","claim")
										->orWhere("category","settlement")
										->pluck('display_name','id');

			$cf = ClaimFile::find($request['claim_file_id']);
			$email = array();
			$email['to'] = "kaviles@bestdoctorsinsurance.com";
			$email['cc'] = "";
			$email['subject'] = "[<TICKET_ID>][<CATEGORIA>]";
			$email['internalAttachments'] = array();
			$email['internalAttachments'][0]['id'] =  $cf['file_entry_id'];
			$email['internalAttachments'][0]['name'] =  "factura ".$cf['description'];
			$email['content'] = "Existen errores en la factura";

			$data['ticketcat'] = $tickeCatList;
			$data['email'] = $email;

			$this->novaMessage->setData($data);
			return $this->returnJSONMessage(200);
		}catch(\Exception $e){
  			$this->novaMessage->addErrorMessage('Error',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}

	public function deleteRefund($id){
		try{
			\DB::beginTransaction();
			$refund = ClaimSettlementRefund::findOrFail($id);
			$refund->delete();
			\DB::commit();
			$this->novaMessage->setData(array('id'=>$id,"deleted_at"=>1));
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			\DB::rollback();
			//show message error
  			$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(404);
		}
	}//end function remove refund

	public function finishSettlement($process_ID){
		try{
			\DB::beginTransaction();

			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRegister');
			extract($resp);

			$claim->status = 1;
			$claim->save();

			//check process exists and not finished
			$class = '\Modules\Claim\Entities\ProcessSettlementRegister';
			$process = $class::findProcess($process_ID);

			$procedure = $process->procedureEntryRel;

			//check all settlement active processes and "finish them!(fatality)" lol
			$processes = $procedure->getCurrentProcesses();
			foreach ($processes as $p) {
				$p->finish();
			}

			\DB::commit();
			$this->novaMessage->setData(array('id'=>$process_ID));
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			\DB::rollback();
			//show message error
  			$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}//end function finish settlement

	public function uploadSettlementFile($process_ID, Request $request){
		try{

			\DB::beginTransaction();

			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRegister');
			extract($resp);

			$category = $request->input('category', false);
			$description = $request->input('description', '');
			$ts = $request->input('ts', false);
			$table_type = 'procedure_entry';
			$old_fid = $request->input('id', false);

			if( empty($ts) || empty($category) ){
				throw new \Exception('some data missing');
			}

			$params = array();
			$params['fieldname'] = 'file';
			$params['subfolder'] = 'settlement/'.$procedure->id;
			$params['table_type'] = $table_type;
			$params['table_id'] = $procedure->id;
			$params['data'] = json_encode(
								array('ts' => $ts,
									'procedure_document_id'=> $category,
									'description' => $description,
									'process_id'  => $process_ID));
			$params['multiple'] = false;

			if( $old_fid ){
				//if get file id try to update
				$updated = $this->updateFile($request, $old_fid, $params);
				$uploadedFiles = array($updated);
			}else{
				$uploadedFiles = $this->uploadFiles($request, $params);
			}

			\DB::commit();
			$this->novaMessage->setData(["ts"=>$ts,"id"=>$uploadedFiles[0]]);
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			//show message error
  			$this->novaMessage->addErrorMessage('Error',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}

	public function deleteSettelementFile($process_ID, $file_ID){
		try{
			\DB::beginTransaction();

			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRegister');
			extract($resp);

	    	if(!isset($file_ID)){
	    		throw new \Exception("Petición es inválida");
	    	}

	    	$fe = \App\FileEntry::find($file_ID);
	    	if(!($fe->table_type=="procedure_entry" &&
	    		$fe->table_id==$procedure->id)){
	    		throw new \Exception("Archivo es inválido");
	    	}

	    	$this->deleteFile($fe->id);

	    	\DB::commit();

		}catch( \Exception $e ){
			\DB::rollback();
			//show message error
  			$this->novaMessage
              ->addErrorMessage('Error',$e->getMessage());
  			return $this->returnJSONMessage(500);
		}
	}
}
