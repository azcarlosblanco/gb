<?php

Route::group(['middleware' => 'jwt.auth', 'prefix' => 'file', 'namespace' => 'Modules\FileManager\Http\Controllers'], function()
{

	Route::group(['prefix'=>'/download'], function ()
	{
		Route::get('{id}',
		[
			'uses' => 'FileManagerController@download'
	 	]);

	});

	Route::group(['prefix'=>'/view'], function ()
	{
		Route::get('{id}',
		[
			'uses' => 'FileManagerController@view'
	 	]);

	});

});
