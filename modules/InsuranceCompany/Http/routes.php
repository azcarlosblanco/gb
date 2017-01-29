<?php

Route::group(['middleware' => 'jwt.auth',
			 'prefix' => 'insurancecompany', 
			 'namespace' => 'Modules\InsuranceCompany\Http\Controllers'], function()
{

	Route::group(['middleware' => 'permission:insuranceCompany_create'], function ()
	{
		Route::get('/create',
		[	
			'as' => 'insurance_company_create', 
			'uses' => 'InsuranceCompanyController@create'
	 	]);
		
		Route::post('/', 
		[
			'as' => 'insurance_company_store', 
			'uses' => 'InsuranceCompanyController@store'
		]);
	});


	Route::group(['middleware' => 'permission:insuranceCompany_access'], function ()
	{
		Route::get('',
		[
			'as' => 'insurance_company',
			'uses' => 'InsuranceCompanyController@index'
		]);
		Route::get('/{id}',
		[
			'as' => 'insurance_company_view',
			'uses' => 'InsuranceCompanyController@view'
		]);
		Route::get('/{id}/oficinas/{id_office}', 
		[
			'as' => 'insurance_company_office_view',
			'uses' => 'InsuranceCompanyOfficeController@view'
		]);
		Route::get('/{id}/emails',
		[
			'as' => 'insurance_company_emails_view',
			'uses' => 'InsuranceCompanyController@viewEmails',
		]);
	});

	Route::group(['middleware' => 'permission:insuranceCompany_edit'], function ()
	{
		Route::patch('/{id}',
		[
			'as'=> 'insurance_company_update',
			'uses' => 'InsuranceCompanyController@update'
		]);
	});

	Route::delete('/{id}/delete', 
		[
			'middleware'	=> 'permission:insuranceCompany_delete',
			'as'            => 'insurance_company_delete',
			'uses'			=> 'InsuranceCompanyController@delete'
		]
	);

	Route::group(['middleware' => 'permission:insuranceCompany_createOffice'], function ()
	{
		Route::get('/{id}/oficinas/form/create', 
		[	
			'as' => 'insurance_company_office_create', 
			'uses' => 'InsuranceCompanyOfficeController@create'
		]);
		Route::post('/{id}/oficinas', 
		[
			'as' => 'insurance_company_office_store', 
			'uses' => 'InsuranceCompanyOfficeController@store'
		]);
	});

	Route::group(['middleware' => 'permission:insuranceCompany_editOffice'], function ()
	{
		Route::patch('/{id}/oficinas/{id_office}', 
			[ 
			  'as' => 'insurance_company_office_update',
			  'uses' => 'InsuranceCompanyOfficeController@update'
			]
		);
	});

	Route::delete('/{id}/oficinas/{id_office}/delete', 
		[
			'middleware'	=> 'permission:insuranceCompany_deleteOffice',
			'as'            => 'insurance_company_office_delete',
			'uses'			=> 'InsuranceCompanyOfficeController@delete'
		]
	);

	Route::group(['middleware' => 'permission:insuranceCompany_manageEmails'], function ()
	{
		Route::post('/{id}/emails/manage', 
			[ 
				'as'   => 'insurance_company_emails_manage',
		  		'uses' => 'InsuranceCompanyController@manageEmails'
			]
		);
	});
});