<?php

Route::group(['middleware' => 'jwt.auth',
			  'prefix' => 'guiaenvio', 
			  'namespace' => 'Modules\GuiaEnvio\Http\Controllers'], function()
{
	Route::get('/', 'GuiaEnvioController@index');
});

Route::group(['middleware' => 'jwt.auth', 
	'prefix' => 'carrier', 
	'namespace' => 'Modules\GuiaEnvio\Http\Controllers'], function()
{
	Route::get('/', 'CarrierController@index');
	Route::get('/form', 'CarrierController@form');
	Route::post('/','CarrierController@store');
	Route::post('/{id}','CarrierController@update');
	Route::get('/{id}','CarrierController@view');
	Route::delete('/{id}','CarrierController@delete');
});