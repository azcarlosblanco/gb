<?php

Route::group(['middleware' => 'jwt.auth', 
	          'prefix' => 'quotation', 
	          'namespace' => 'Modules\Quotation\Http\Controllers'], function()
{
	Route::get('/form', 'QuotationController@getDataForm');
	Route::get('/plansQuotation', 'QuotationController@getPlansQuotation');
	Route::get('/calculatePremium', 'QuotationController@calculatePremium');
	Route::post('/quototationToEmission/{id}', 'QuotationController@convertQuotationIntoEmission');
	Route::post('/saveQuotation', 'QuotationController@saveQuotation');
	Route::get('/sendEmail', 'QuotationController@sendQuotationByEmail');
	Route::get('/', 'QuotationController@listQuotation');
	Route::get('/{id}', 'QuotationController@viewQuotation');
});