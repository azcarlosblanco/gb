<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\ClaimSettlement;
use Modules\Claim\Entities\ClaimSettlementRefund;
use Modules\Claim\Entities\ClaimFile;
use Modules\Claim\Entities\ClaimProcedure;
use Modules\Claim\Services\Validation\ClaimValidator;
use Modules\Payment\Entities\PaymentMethod;

class ClaimSettlementRefundController extends NovaController {

	function __construct(){
    	parent::__construct();
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

		if( !$process->isActive() ){
			throw new \Exception('invalid process');
		}

		$procedure = $process->procedureEntryRel;

		//validate claim and procedure association exists
		$cp = ClaimProcedure::where('procedure_entry_id', $procedure->id)->first();

		if( empty($cp) ){
			throw new \Exception('invalid claim-procedure');
		}

		//validate claim exists is not finished
		$claim = $cp->claim;
		if($claim->status == 1){
			throw new \Exception('invalid claim');
		}

		//validate file ids exist and belong to current procedure

		return array('process'=>$process, 'procedure'=>$procedure, 'cp'=>$cp, 'claim'=>$claim);
	}

	public function view($process_ID){
		try{
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRefund');
			extract($resp);

			//general info
			$data['claim']['id'] = $claim->id;
			$ap = $claim->affiliatePolicy;
			$data['claim']['affiliate_name'] = $ap->affiliate->getFullNameAttribute();
			$data['claim']['policy_num'] = $ap->policy->policy_number;

			$data['settlements'] = ClaimSettlement::with('refunds')
													 ->join('claim_file', 'claim_settlement.claim_file_id', '=', 'claim_file.id')
													 ->select('claim_settlement.id', 'claim_settlement.uncovered_value')
													 ->addSelect('claim_settlement.descuento', 'claim_settlement.deducible')
													 ->addSelect('claim_settlement.coaseguro', 'claim_settlement.refunded')
													 ->addSelect('claim_settlement.expected_refund')
													 ->addSelect('claim_file.description', 'claim_file.amount')
													 ->where('claim_file.claim_id', $claim->id)
													 ->where('claim_settlement.expected_refund', '>', 0)
													 ->get();

			$data['catalog'] = PaymentMethod::all()->pluck('method', 'id');

			$this->novaMessage->setData($data);
  		return $this->returnJSONMessage(200);

		}catch(\Exception $e){
			//show message error
  		$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  		return $this->returnJSONMessage(404);
		}
	}//end function view

	public function saveRefund(Request $request, $process_ID){
		try{
			$resp = $this->validateProcess($process_ID, 'ProcessSettlementRefund');
			extract($resp);

			ClaimValidator::validatePaymentFormData($request);
			$id = $request->input('set_id', 0);
			$paid = $request->input('paid', 0);
			$supplier = $request->input('to_supplier', 0);
			$pay_method = $request->input('pay_method', 0);
			$pay_date = $request->input('pay_date', NULL);
			$reference_number = $request->input('reference_number', NULL);

			if( $paid <= 0 ){
				throw new \Exception('invalid paid amount');
			}

			PaymentMethod::findOrfail($pay_method);

			$settlement = ClaimSettlement::findOrFail($id);
			$max_amount = $settlement->expected_refund;
			$refunded = $settlement->calculateRefunded();
			$to_refund = $paid + $refunded;

			if( $max_amount <= 0 ){
				throw new \Exception('invalid settlement');
			}

			if( $to_refund > $max_amount ){
				throw new \Exception('invalid refund amount');
			}

			//create refund
			$refund = ClaimSettlementRefund::create([
				'value' => $paid,
				'payment_method_id' => $pay_method,
				'claim_settlement_id' => $settlement->id,
				'to_supplier' => $supplier,
				'pay_date' => date('Y-m-d', strtotime($pay_date))
			]);

			$this->novaMessage->setData(array('id'=>$refund->id));
  		return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			//show message error
  		$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  		return $this->returnJSONMessage(404);
		}
	}//end function store

	public function deleteRefund($id){
		try{
			$refund = ClaimSettlementRefund::findOrFail($id);
			$refund->delete();

			$this->novaMessage->setData(array('id'=>$id));
  			return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			//show message error
  			$this->novaMessage
              ->addErrorMessage('NOT FOUND',$e->getMessage());
  		return $this->returnJSONMessage(404);
		}
	}//end function remove

}
