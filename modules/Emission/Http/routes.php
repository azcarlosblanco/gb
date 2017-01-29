<?php

Route::group(['middleware' => 'jwt.auth',
 			  'prefix' => 'emission', 
 			  'namespace' => 'Modules\Emission\Http\Controllers'], function()
{
	Route::group(['prefix'=>'/newPolicy'], function ()
	{
		/*Route::get('/uploadPolicyRequest/{process_ID}/form',
		[
			'uses' => 'UploadPolicyRequestController@uploadPolicyRequestForm'
	 	]);

		Route::post('/uploadPolicyRequest/{process_ID}',
		[
			'uses' => 'UploadPolicyRequestController@uploadPolicyRequest'
		]);*/


		Route::get('/changeEffectiveDate/{process_ID}/form',
		[
			'uses' => 'ChangeEffectiveDateController@form'
	 	]);

		Route::post('/changeEffectiveDate/{process_ID}',
		[
			'uses' =>
				'ChangeEffectiveDateController@change'
		]);

		Route::get('/requestAppNewPolicyBD/{process_ID}/form',
		[
			'uses' => 'RequestAppNewPolicyBDController@form'
	 	]);

		Route::post('/requestAppNewPolicyBD/{process_ID}',
		[
			'uses' =>
				'RequestAppNewPolicyBDController@requestAppNewPolicyBD'
		]);

		Route::get('/reviewProspectPolicy/{process_ID}/form',
		[
			'uses' => 'ReviewProspectPolicyController@form'
	 	]);	 	

	 	Route::post('/reviewProspectPolicy/{process_ID}',
		[
			'uses' => 'ReviewProspectPolicyController@review'
	 	]);

		Route::get('/registerCustomerResponse/{process_ID}/form',
		[
			'uses' => 'RegisterCustomerResponseController@form'
	 	]);

		Route::post('/registerCustomerResponse/{process_ID}',
		[
			'uses' =>
				'RegisterCustomerResponseController@registerResponse'
		]);

		Route::get('/registerInvoice/{process_ID}/form',
		[
			'uses' => 'RegisterInvoiceController@form'
	 	]);

		Route::post('/registerInvoice/{process_ID}',
		[
			'uses' =>
				'RegisterInvoiceController@registerInvoice'
		]);

		Route::get('/registerPayment/{process_ID}/form',
		[
			'uses' => 'RegisterCustomerPaymentController@form'
	 	]);

		Route::post('/registerPayment/{process_ID}',
		[
			'uses' =>
				'RegisterCustomerPaymentController@registerPayment'
		]);

		Route::post('/registerPayment/{process_ID}/file',
		[
			'uses' =>
				'RegisterCustomerPaymentController@reUploadFiles'
		]);

		Route::delete('/registerPayment/{process_ID}/file',
		[
			'uses' =>
				'RegisterCustomerPaymentController@deteleFileProcess'
		]);

		Route::get('/sendDocsRec/{process_ID}/form',
		[
			'uses' => 'SendDocsReceptionController@form'
	 	]);

	 	Route::get('/sendDocsRec/{process_ID}/printLetter',
		[
			'uses' => 'SendDocsReceptionController@printLetter'
	 	]);

		Route::post('/sendDocsRec/{process_ID}',
		[
			'uses' =>
				'SendDocsReceptionController@process'
		]);

		Route::group(['middleware' => 'web'/*'permission:np_upload_policy_request'*/], function ()
		{
			Route::get('/uploadPolicyRequest/form/{process_ID}',
			[
				'uses' => 'UploadPolicyRequestController@uploadPolicyRequestForm'
		 	])->where('process_ID', '[0-9]+');

		 	Route::get('/uploadPolicyRequest/formRegistered/{process_ID}',
			[
				'uses' => 'UploadPolicyRequestController@review2'
		 	])->where('process_ID', '[0-9]+');

			Route::post('/uploadPolicyRequest/form/{process_ID}/step/{step}',
			[
				'uses' => 'UploadPolicyRequestController@uploadPolicyRequestSteps'
		 	])->where('step', '[0-9]+');

			Route::get('/uploadPolicyRequest/form/{process_ID}/summary',
			[
				'uses' => 'UploadPolicyRequestController@uploadPolicyRequestSummary'
		 	])->where('step', '[0-9]+');

			Route::post('/uploadPolicyRequest/form/{process_ID}/summary',
			[
				'uses' => 'UploadPolicyRequestController@uploadPolicyRequestSummaryProcess'
		 	])->where('step', '[0-9]+');

			Route::get('/uploadPolicyRequest/{process_ID}/emaildata',
			[
				'uses' => 'UploadPolicyRequestController@emailData'
			])->where('process_ID', '[0-9]+');
			
			Route::post('/uploadPolicyRequest/{process_ID}',
			[
				'uses' => 'UploadPolicyRequestController@uploadPolicyRequest'
			])->where('process_ID', '[0-9]+');
		});
	});

	Route::group(['prefix'=>'/pending'], function ()
	{
		Route::get('',
		[
			'uses' => 'EmissionController@pendingProcedures'
	 	]);
		Route::get('',
		[
			'uses' => 'EmissionController@pendingProcedures'
	 	]);
		Route::delete('cancelProcedure/{procedure_id}',
		[
			'uses' => 'EmissionController@cancelProcedure'
		]);
	});

	Route::group(['prefix'=>'/report'], function ()
	{
		Route::get('tramitesActuales',
		[
			'uses' => 'ReporteTramitesController@tramitesActuales'
	 	]);

	 	Route::get('historialTramites',
		[
			'uses' => 'ReporteTramitesController@historialTramites'
	 	]);

	 	Route::get('historialTramitesUser',
		[
			'uses' => 'ReporteTramitesController@historialTramitesByOperator'
	 	]);

	 	Route::get('averageTimeProccess',
		[
			'uses' => 'ReporteTramitesController@averageTimeByProcedure'
	 	]);

	 	Route::get('averageTimeProccess/{user_ID}',
		[
			'uses' => 'ReporteTramitesController@averageTimeByUser'
	 	]);
	});

});

Route::group(['prefix' => 'emission_email', 
 			  'namespace' => 'Modules\Emission\Http\Controllers'], function()
{
		Route::get('/email_template',
		[
			'uses' => 'ReviewProspectPolicyController@viewEmailTemplate'
	 	]);
});
