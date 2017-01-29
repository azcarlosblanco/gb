<?php
namespace Modules\Customer\Services\Validation;

use Validator;

class CustomerValidator {

	public static function validateCreatePolicyFormData( array $data ) {
    $rules = array(
      'pid_num' => 'required|numeric',
      'pid_type' => 'required|numeric',
      'name' => 'required|string',
      'lastname' => 'required|string',
      'address' => 'required|string',
      'phone' => 'required|numeric',
      'mobile' => 'required|numeric',
      'email' => 'required|email',
      'country_id' => 'required|numeric',
      'state_id' => 'required|numeric',
      'city_id' => 'required|numeric',
      'height' => 'required|numeric',
      'weight' => 'required|numeric',
      'civil_status' => 'required|numeric',
      'dob' => 'required|date',
			'sex' => 'required|numeric',
      "weightu" => 'required|in:kg,lb',
      "heightu" => "required|in:m,cm"
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
