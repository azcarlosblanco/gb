<?php

/*Route::group(['middleware' => 'jwt.auth',
				'prefix' => 'plan',
				'namespace' => 'Modules\Plan\Http\Controllers'], function()
{
	Route::group(['middleware' => 'jwt.auth','permission:plan_create'], function ()
	{
		Route::get('/form',
		[	
			'as' => 'plan_create', 
			'uses' => 'PlanController@create'
	 	]);

	 	Route::get('/{id}/createPlanDeducible',
		[	
			'as' => 'plan_create_deducible', 
			'uses' => 'PlanDeducibleController@create'
	 	]);

	 	Route::get('/{id}/createPlanCost',
		[	
			'as' => 'plan_create_cost', 
			'uses' => 'PlanCostController@create'
	 	]);
		
		Route::post('/store', 
		[
			'as' => 'plan_store', 
			'uses' => 'PlanController@store'
		]);

		Route::post('/{id}/storePlanDeducible',
		[	
			'as' => 'plan_deducible_store', 
			'uses' => 'PlanDeducibleController@store'
	 	]);

	 	Route::post('/{id}/storePlanCost',
		[	
			'as' => 'plan_cost_store', 
			'uses' => 'PlanCostController@store'
	 	]);
	});*/

/*	Route::group(['middleware' => 'jwt.auth'/*'permission:plan_edit'*//*], function ()
/*	{
		Route::patch('/{id}/update',
		[
			'as'=> 'plan_update',
			'uses' => 'PlanController@update'
		]);
	});

	Route::delete('/{id}/delete', 
		[
			//'middleware'	=> 'permission:plan_delete',
			'as'            => 'plan_delete',
			'uses'			=> 'PlanController@delete'
		]
	);

	Route::group(['middleware' => 'jwt.auth'/*'permission:plan_access'*//*], function ()
	{
		Route::get('/getPlansByInsuranceCompany/{insuranceCompanyID}',
		[
			'as' => 'plan_by_insurance_company',
			'uses' => 'PlanController@getPlansByInsuranceCompany'
		]);

		Route::get('/{id}/view',
		[
			'as' => 'plan_view',
			'uses' => 'PlanController@view'
		]);

		/*Route::get('/{id?}',
		[
			'as' => 'plan',
			'uses' => 'PlanController@index'
		]);*/

/*		Route::get('/',
		[
			'as' => 'infoplan',
			'uses' => 'PlanController@getplaninfo'
		]);
	});*/

Route::group(['middleware' => 'jwt.auth', 
				'prefix' => 'plan', 
				'namespace' => 'Modules\Plan\Http\Controllers'], function()
	{
		Route::get('/', 'PlanController@getplaninfo');
	 	//Route::get('/', 'PlanController@view');
	    Route::get('/{id}', 'PlanController@getdetail');	
	});
	
//});