<?php

Route::group(['middleware' => 'web', 'prefix' => 'authorization', 'namespace' => 'Modules\Authorization\Http\Controllers'], function()
{
	Route::get('/', 'AuthorizationController@index');
});