<?php

Route::group(['middleware' => 'jwt.auth',
			 'prefix' => 'rrhh', 
			 'namespace' => 'Modules\RRHH\Http\Controllers'], function()
{
	Route::group(['prefix'=>'/employee'], function ()
		{
			Route::get('/', 'EmployeeController@index');
			Route::get('/form', 'EmployeeController@form');
			Route::post('/', 'EmployeeController@store');
			Route::get('/{id}', 'EmployeeController@view');
			Route::get('/{id}/form', 'EmployeeController@formView');
			Route::post('/{id}', 'EmployeeController@update');
			Route::delete('/{id}', 'EmployeeController@delete');
		}
	);
});