<?php

Route::group(['middleware' => 'web', 'prefix' => 'renewal', 'namespace' => 'Modules\Renewal\Http\Controllers'], function()
{
	Route::get('/', 'RenewalController@index');
});