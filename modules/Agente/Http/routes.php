<?php

Route::group(['middleware' => 'jwt.auth',
			'prefix' => 'agente',
			'namespace' => 'Modules\Agente\Http\Controllers'], function()
{

	Route::get('/', 'AgenteController@index');
	Route::get('/form', 'AgenteController@form');
	Route::post('/','AgenteController@store');
	Route::get('/{id}','AgenteController@view');
	Route::post('/{id}','AgenteController@update');
	Route::delete('/{id}','AgenteController@delete');

    });