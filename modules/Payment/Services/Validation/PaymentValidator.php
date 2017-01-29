<?php
namespace Modules\Payment\Services\Validation;

use Validator;
use Modules\Payment\Entities\PolicyCost;
use Modules\Payment\Entities\PaymentMethod;

/**
 * Base Validation class. All entity specific validation classes inherit
 * this class and can override any function for respective specific needs
 */
class PaymentValidator {

	public static function validateCreatePolicyFormData( array $data ) {
	    $rules = array(
				'payment_method'      => 'required|exists:payment_method,id',
				'payment_number'      => 'required|exists:number_payments,id',
				'request_discount'    => 'required',
				'request_invoice'     => 'required',
				'discount_percentage' => 'required_if:request_discount,true|numeric'
			);
	    $custom_errors = array();

		//use Laravel's Validator and validate the data
		$validation = Validator::make( $data, $rules, $custom_errors );

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages() );
		}

		//all good and shiny
		return true;
	}

	public static function validateRegisterPaymentFormData( array $data ){
		$rules = array(
				"payment_method_id"  => 'required|exists:payment_method,id',
				"policy_cost_id"     => 'required|exists:policy_cost,id',
				"payment_date"       => 'required|date',
				"value"              =>  array('required', 
												'numeric', 
												'regex:/^[0-9]+(\.[0-9]{1,2})*$/')
			);

		$pm = PaymentMethod::find($data["payment_method_id"])
								->pluck("method","id");
		
		switch ($pm[$data['payment_method_id']]) {
			case 'cheque':
				$rules['cheque_num'] = 'required';
				$rules['bank_name']  = 'required';
				break;
			case 'transfer':
				$rules['transfer_num']      	= 'required';
				$rules['bank_name']             = 'required';
				$rules['bank_account_type_id']  = 'required|exists:bank_account_type,id';
				$rules['titular_account']       = 'required';
				$rules['account_num_from']      = 'required';
				break;
			case 'deposit':
				$rules['desposit_num']          = 'required';
				$rules['bank_name']             = 'required';
				$rules['account_num']           = 'required|integer';
				break;
			case 'creditcard':
				$rules['credit_card_type_id']    = 'required|exists:credit_card_type,id';
				$rules['credit_card_brand_id']   = 'required|exists:credit_card_brand,id';
				$rules['credit_card_way_pay_id'] = 'required|exists:credit_card_way_pay,id';
				break;
		}

		$custom_errors = array();

		//use Laravel's Validator and validate the data
		$validation = Validator::make( $data, $rules, $custom_errors );

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages() );
		}

		//all good and shiny
		return true;
	}

} //end of class

//EOF
