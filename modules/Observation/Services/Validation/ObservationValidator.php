<?php
namespace Modules\Observation\Services\Validation;

use Validator;

/**
 * Base Validation class. All entity specific validation classes inherit
 * this class and can override any function for respective specific needs
 */
class ObservationValidator {

	public static function validateCreatePolicyFormData( array $data ) {
    $rules = array(
			'item_id' => 'sometimes|required|numeric',
      'content' => 'required|string',
			'type_id' => 'required|exists:observation_type,id'
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

} //end of class

//EOF
