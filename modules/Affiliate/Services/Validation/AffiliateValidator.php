<?php
namespace Modules\Affiliate\Services\Validation;

use Validator;

/**
 * Base Validation class. All entity specific validation classes inherit
 * this class and can override any function for respective specific needs
 */
class AffiliateValidator {

	public static function validateCreatePolicyFormData( array $data ) {
    	$rules = array(
		    'name' => 'required|string',
		    'lastname' => 'required|string',
		    'height' => 'required|numeric',
		    'weight' => 'required|numeric',
		    'dob' => 'required|date',
			'sex' => 'required|numeric',
			'role' => 'required|exists:affiliate_role,id',
			'heightu' => 'required',
			'weightu' => 'required'
		);
    	$custom_errors = array(
			'heightu.required' => 'Debe selccionar una unidad para el campo altura',
			'weightu.required' => 'Debe selccionar una unidad para el campo peso'
		);

    	if($data['role']==1){
    		$rules['pid_num'] = 'required|numeric';
      		$rules['pid_type'] = 'required|numeric';
    	}

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
