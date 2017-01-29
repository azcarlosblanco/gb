<?php

Route::group(['middleware' => 'jwt.auth',
			 'prefix' => 'renovations',
			 'namespace' => 'Modules\Renovation\Http\Controllers'], function()
{
			Route::get('/', 'RenovationController@index');

            Route::get('/add-file', 'RenovationController@addRenovations');

            Route::post('/upload-renovation', 'RenovationController@uploadRenovation');
});
