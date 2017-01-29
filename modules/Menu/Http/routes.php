<?php

Route::group(['middleware' => 'jwt.auth',
			'prefix' => 'menu', 
			'namespace' => 'Modules\Menu\Http\Controllers'], function()
{
	Route::get('/sidebar', 'MenuController@sideBar');
});