<?php

Route::group(['middleware' => 'web', 'prefix' => 'utilities', 'namespace' => 'Modules\Utilities\Http\Controllers'], function()
{
	Route::get('/', 'UtilitiesController@index');
});