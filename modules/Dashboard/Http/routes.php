<?php

Route::group(['middleware' => 'jwt.auth', 
				'prefix' => 'dashboard', 
				'namespace' => 'Modules\Dashboard\Http\Controllers'], function()
{
	Route::get('/', 'DashboardController@index');
	Route::get('/procedureTime','DashboardController@procedureTime');
	Route::get('/policiesSales','DashboardController@policiesSales');
	Route::get('/agentSales','DashboardController@agentSales');
});