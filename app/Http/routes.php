<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get("test",function(){return "hola";});

Route::post('auth/authenticate',
    	'Nova\AuthenticateController@authenticate');

Route::post('auth/logout',
    	'Nova\AuthenticateController@logout');

Route::post('auth/refresh-token', ['middleware' => 'jwt.refresh', function() {}]);

Route::get('auth/user',
		'Nova\AuthenticateController@getAuthenticatedUser');


/**
 * Ruta de test de pdf para tecnicos de liquidweb
 */
Route::get('pdf', function(){
	$pdf = PDF::loadHTML('<h1>PDF Test</h1>');
	return response($pdf->download('test.pdf'))->header('Content-Type', 'application/pdf');
});
