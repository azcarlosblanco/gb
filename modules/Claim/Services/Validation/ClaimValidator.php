<?php
namespace Modules\Claim\Services\Validation;

use Validator;

/**
 * Base Validation class. All entity specific validation classes inherit
 * this class and can override any function for respective specific needs
 */
class ClaimValidator {

	public static function validateGenerateClaimFormData( array $data ) {
		if( !is_array($data) ){
			throw new \Exception('invalid data');
		}

		if( count($data) < 1 ) throw new \Exception('no affiliates assigned');

		//validate affiliates - diagnosis - files associations format
		foreach( $data as $key => $val ){
			if( intval($key) < 1 ) throw new \Exception('invalid affiliate id #SERIAL00'.$key);

			$val = (array)$val;
			if( count($val) < 1 ) throw new \Exception('no diagnosis assigned #SERIAL00'.$key);

			foreach( $val as $i => $j ){
				if( intval($i) < 1 ) throw new \Exception('invalid diagnosis id #SERIAL00'.$key);
				$associations = (array)$j;

				foreach( $associations as $x=>$af ){
					$af = (array)$af;
					if( !isset($af['file_entry_id']) || (intval($af['file_entry_id']) < 1) ) throw new \Exception('invalid \'file id\' field #SERIAL00'.$key);
				}
			}//end foreach
		}//end foreach

		//all good and shiny
		return true;
	}

	public static function validateSettlementFormData( \Illuminate\Http\Request $data ) {
    $rules = array(
			'cfid' => 'required|numeric',
		      'amount' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
		      'uncovered' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
		      'dscto' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
		      'deducible' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
		      'coaseguro' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
		      'refund' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
		      'msg' => 'string'
      //'files' => 'required|json'
		);
    $custom_errors = array();

		//use Laravel's Validator and validate the data
		$validation = Validator::make( $data->all(), $rules, $custom_errors );

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages() );
		}

		//all good and shiny
		return true;
	}

	public static function validatePaymentFormData( \Illuminate\Http\Request $data ) {
    $rules = array(
			'set_id' => 'required|numeric',
      		'paid' => array('required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})*$/'),
			'to_supplier' => 'required|boolean',
      		'pay_method' => 'required|numeric',
			'pay_date' => 'required|date',
			'reference_number' => 'required'
		);
    $custom_errors = array();

		//use Laravel's Validator and validate the data
		$validation = Validator::make( $data->all(), $rules, $custom_errors );

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages() );
		}

		//all good and shiny
		return true;
	}

} //end of class

//EOF
