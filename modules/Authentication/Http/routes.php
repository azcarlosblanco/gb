<?php

Route::group(['middleware' => 'web', 'prefix' => 'authentication', 'namespace' => 'Modules\Authentication\Http\Controllers'], function()
{
	Route::get('/', 'AuthenticationController@index');
});