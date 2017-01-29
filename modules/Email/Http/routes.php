<?php

Route::group(['middleware' => ['jwt.auth'], 
	'prefix' => 'email', 
	'namespace' => 'Modules\Email\Http\Controllers'], function()
{
	Route::get('/', 'EmailController@sendEmail');
	Route::get('/uploadAttachament', 'EmailController@sendEmail');
});