<?php

Route::group(['middleware' => 'jwt.auth', 
	'prefix' => 'affiliate', 
	'namespace' => 'Modules\Affiliate\Http\Controllers'], function()
{
	Route::get('/', 'AffiliateController@index');
	Route::get('/{id}', 'AffiliateController@view');
});