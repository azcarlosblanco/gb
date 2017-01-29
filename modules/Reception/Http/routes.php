<?php

Route::group(['middleware' => 'jwt.auth',
			  'prefix' => 'reception',
			  'namespace' => 'Modules\Reception\Http\Controllers'], function()
{
	Route::group(['prefix'=>'/pending'], function ()
	{		Route::get('',
		[
			'uses' => 'ReceptionController@pendingProcedures'
	 	]);
	});

	Route::group(['prefix'=>'/newPolicy'], function ()
	{

	 	Route::get('/initialDocumentation/form',
		[
			'uses' => 'InitialDocumentationController@form'
	 	]);

	 	Route::get('/initialDocumentation/{process_ID}/form',
		[
			'uses' => 'InitialDocumentationController@view'
	 	]);

		Route::post('/initialDocumentation',
		[
			'uses' => 'InitialDocumentationController@initialDocumentation'
		]);

		Route::post('/initialDocumentation/{process_ID}/file',
		[
			'uses' => 'InitialDocumentationController@uploadDocumentationFile'
	 	]);


		Route::get('/sendCheckIC/{process_ID}/form',
		[
			'uses' => 'SendCheckICController@form'
	 	]);

	 	Route::get('/sendCheckIC/{process_ID}/printGuia',
		[
			'uses' => 'SendCheckICController@printGuia'
	 	]);

		Route::post('/sendCheckIC/{process_ID}',
		[
			'uses' => 'SendCheckICController@uploadGuia'
		]);

		Route::get('/receiveDocsBD/{process_ID}/form',
		[
			'uses' => 'ReceivePolicyBDController@form'
	 	]);

		Route::post('/receiveDocsBD/{process_ID}',
		[
			'uses' => 'ReceivePolicyBDController@process'
		]);

		Route::get('/sendPolicyCustomer/{process_ID}/form',
		[
			'uses' => 'SendPolicyCustomerController@form'
	 	]);

		Route::get('/sendPolicyCustomer/{process_ID}/printGuia',
		[
			'uses' => 'SendPolicyCustomerController@printGuia'
		]);

		Route::post('/sendPolicyCustomer/{process_ID}',
		[
			'uses' => 'SendPolicyCustomerController@uploadGuia'
	 	]);

		Route::get('/uploadSignedPolicy/{process_ID}/form',
		[
			'uses' => 'UploadSignedPolicyController@form'
	 	]);

		Route::post('/uploadSignedPolicy/{process_ID}',
		[
			'uses' => 'UploadSignedPolicyController@upload'
		]);

		Route::get('/sendPolicyBD/{process_ID}/form',
		[
			'uses' => 'SendDocumentsBDController@form'
	 	]);

		Route::get('/sendPolicyBD/{process_ID}/printGuia',
		[
			'uses' => 'SendDocumentsBDController@printGuia'
		]);

		Route::post('/sendPolicyBD/{process_ID}',
		[
			'uses' => 'SendDocumentsBDController@uploadGuia'
	 	]);

		Route::get('/uploadReceipt/{process_ID}/form',
		[
			'uses' => 'UploadReceiptController@form'
	 	]);

		Route::post('/uploadReceipt/{process_ID}',
		[
			'uses' => 'UploadReceiptController@upload'
		]);
	});


	Route::group(['prefix'=>'/guiaremision'], function ()
	{
		Route::get('',
		[
			'uses' => 'DispachingDocsController@reportSendDocuments'
	 	]);

	 	Route::post('',
		[
			'uses' => 'DispachingDocsController@createGuide'
	 	]);

	 	Route::get('form',
		[
			'uses' => 'DispachingDocsController@createGuideForm'
	 	]);

		Route::get('printGuide',
		[
			'uses' => 'DispachingDocsController@printGuide'
	 	]);
	});

	Route::group(['prefix'=>'/newClaims'], function ()
	{
		Route::get('{id}/form',
		[
			'uses' => 'ClaimsInitController@view'
	 	]);

		Route::get('form',
		[
			'uses' => 'ClaimsInitController@form'
	 	]);

	 	Route::post('',
		[
			'uses' => 'ClaimsInitController@claimsInit'
	 	]);

		Route::post('{id}/file',
		[
			'uses' => 'ClaimsInitController@uploadClaimFile'
	 	]);

	 	Route::get('sendDocsBD/{id}/form',
		[
			'uses' => 'ClaimsSendDocsDBController@form'
	 	]);

	 	Route::get('sendDocsBD/{id}/printGuia',
		[
			'uses' => 'ClaimsSendDocsDBController@printGuia'
	 	]);

	 	Route::post('sendDocsBD/{id}',
		[
			'uses' => 'ClaimsSendDocsDBController@uploadGuia'
	 	]);

	 	Route::get('/uploadReceipt/{process_ID}/form',
		[
			'uses' => 'ClaimsReceiveReceiptController@form'
	 	]);

		Route::post('/uploadReceipt/{process_ID}',
		[
			'uses' => 'ClaimsReceiveReceiptController@upload'
		]);
	});

	Route::group(['prefix'=>'/settlements'], function ()
	{
		Route::get('/{id}',
		[
			'uses' => 'SettlementController@view'
	 	]);

	 	Route::post('/start/{id}',
		[
			'uses' => 'SettlementController@initSettlement'
	 	]);
	 	Route::post('/initUpload/{process_ID}',
		[
			'uses' => 'SettlementController@initUpload'
	 	]);

	 	Route::get('/form/{process_ID}',
		[
			'uses' => 'SettlementController@listUploadedFiles'
	 	]);

		Route::post('/upload/{process_ID}',
		[
			'uses' => 'SettlementController@uploadSettlementFile'
	 	]);
	});

});
