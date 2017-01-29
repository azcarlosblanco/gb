<?php
//jwt.auth
Route::group(['middleware' => 'web',
	'prefix' => 'policy',
	'namespace' => 'Modules\Policy\Http\Controllers'], function()
{
	Route::get('/', 'PolicyController@index');
	Route::get('/{id}', 'PolicyController@view');
	Route::get('/quote/{id}', 'PolicyController@calculatePremiums');
	Route::get('/{id}/files', 'PolicyController@policyListFiles');
});
