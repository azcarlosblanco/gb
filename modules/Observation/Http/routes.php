<?php

Route::group(['middleware' => 'web', 'prefix' => 'observation', 'namespace' => 'Modules\Observation\Http\Controllers'], function()
{
	Route::get('/', 'ObservationController@index');
});