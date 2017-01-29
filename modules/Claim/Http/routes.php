<?php

Route::group(['middleware' => 'jwt.auth',
			'prefix' => 'claims',
			'namespace' => 'Modules\Claim\Http\Controllers'], function()
{
	Route::get('/pending', 'ClaimController@pendingProcedures');

	Route::get('/times', 'ClaimController@timeProcess');

	Route::group(['prefix'=>'/reviewDocuments'], function ()
	{
		Route::get('/{id}/form',
		[
			'uses' => 'ClaimsReviewDocsController@view'
	 	]);

	 	Route::post('/{id}/files',
		[
			'uses' => 'ClaimsReviewDocsController@updateFiles'
	 	]);

		Route::post('/{processId}',
		[
			'uses' => 'ClaimsReviewDocsController@generateClaims'
	 	]);

		Route::get('/previousOrders/{id}',
		[
			'uses' => 'ClaimsReviewDocsController@getPreviousOrders'
	 	]);

	});

	Route::group(['prefix'=>'/printLetter'], function ()
	{
		Route::get('/{processId}/form',
		[
			'uses' => 'ClaimsPrintLetterController@form'
	 	]);

		Route::get('/{processId}/printLetter/{claimId}',
		[
			'uses' => 'ClaimsPrintLetterController@printLetter'
	 	]);

	 	Route::get('/{processId}/printClaimForm/{claimId}',
		[
			'uses' => 'ClaimsPrintLetterController@printClaimForm'
	 	]);

		Route::post('/{processId}',
		[
			'uses' => 'ClaimsPrintLetterController@process'
	 	]);

	});

});

Route::group(['middleware' => 'jwt.auth',
			  'prefix' => 'settlements', 
			  'namespace' => 'Modules\Claim\Http\Controllers'], function()
{
	Route::get('/pending', 'ClaimSettlementController@pendingSettlements');

	Route::group(['prefix'=>'/registerForm'], function ()
	{
		
		Route::post('/createTicket',
		[
			'uses' => 'ClaimSettlementController@createTicket'
	 	]);

	 	Route::get('/getTicketContent',
		[
			'uses' => 'ClaimSettlementController@getTicketContent'
	 	]);
	 	
		Route::get('/{process_ID}',
		[
			'uses' => 'ClaimSettlementController@getSettlementInfo'
	 	]);

		Route::post('/{process_ID}',
		[
			'uses' => 'ClaimSettlementController@saveSettlement'
	 	]);

	 	Route::post('/add/{process_ID}',
		[
			'uses' => 'ClaimSettlementController@saveSpecialSettlement'
	 	]);

		Route::delete('/{id}',
		[
			'uses' => 'ClaimSettlementController@removeSettlement'
	 	]);

	 	Route::post('/{process_ID}/file',
		[
			'uses' => 'ClaimSettlementController@uploadSettlementFile'
	 	]);

		Route::delete('/{process_ID}/file/{file_ID}',
		[
			'uses' => 'ClaimSettlementController@deleteSettelementFile'
	 	]);
	});

	Route::group(['prefix'=>'/registerPayment'], function ()
	{
		Route::get('/{settlement_ID}',
		[
			'uses' => 'ClaimSettlementController@viewRefund'
	 	]);

		Route::post('/{process_ID}',
		[
			'uses' => 'ClaimSettlementController@saveRefund'
	 	]);

		Route::delete('/{id}',
		[
			'uses' => 'ClaimSettlementController@deleteRefund'
	 	]);
	});

	Route::post('/finish/{process_ID}', 'ClaimSettlementController@finishSettlement');

});

Route::group(['middleware' => 'jwt.auth', 
	'prefix' => 'claim', 
	'namespace' => 'Modules\Claim\Http\Controllers'], function()
{
	Route::get('/', 'ClaimController@getReport');
    Route::get('/{id}', 'ClaimController@getDetailReport');
    Route::post('/','ClaimConceptController@store');	
});

Route::group(['middleware' => 'jwt.auth', 
	'prefix' => 'concept', 
	'namespace' => 'Modules\Claim\Http\Controllers'], function()
{
	Route::post('/','ClaimConceptController@store');	
});
