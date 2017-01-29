<?php

Route::group(['middleware' => 'web', 'prefix' => 'supplier', 'namespace' => 'Modules\Supplier\Http\Controllers'], function()
{
	Route::get('/', 'SupplierController@index');
});

Route::group(['middleware' => 'jwt.auth', 
				'prefix' => 'supplier', 
				'namespace' => 'Modules\Supplier\Http\Controllers'], function()
{
	Route::get('/', 'SupplierController@index');
	Route::get('/form', 'SupplierController@form');
	Route::post('/','SupplierController@store');
	Route::post('/{id}','SupplierController@update');
	Route::get('/{id}','SupplierController@view');
	Route::delete('/{id}','SupplierController@delete');
});