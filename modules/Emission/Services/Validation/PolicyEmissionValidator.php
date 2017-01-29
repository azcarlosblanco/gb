<?php
namespace Modules\Emission\Services\Validation;

use Validator;

class PolicyEmissionValidator {

	public static function validateCreateFromTempData( array $data ) {
    	$rules = array(
			'customer_obj' => 'required',
			'plan_obj' => 'required',
			//'aff_obj' => 'required',
			'quiz_obj' => 'required',
			'payment_obj' => 'required'
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

	public static function validateEffectiveDatePrevInsurance ( array $data ){
		
		$rules = array(
            'effective_date'  => 'required|date',
        );

        $custom_errors = array();

		//use Laravel's Validator and validate the data
		$validation = Validator::make( $data, $rules, $custom_errors );

		if ( $validation->fails() ) {
			//validation failed, throw an exception
			throw new \Exception( $validation->messages() );
		}
		
		return true;
	}

} //end of class

//EOF
