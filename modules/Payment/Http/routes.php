<?php


Route::group(['middleware' => 'jwt.auth', 
	          'prefix' => 'payment', 
	          'namespace' => 'Modules\Payment\Http\Controllers'], function()
{
	Route::get('/', 'PaymentController@index');
	Route::get('/{id}', 'PaymentController@form');
});