<?php

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'clientservice',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'ClientServiceController@index');
});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'specialty',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'SpecialtyController@index');
	Route::get('/form', 'SpecialtyController@form');
	Route::post('/','SpecialtyController@store');
	Route::post('/{id}','SpecialtyController@update');
	Route::get('/{id}','SpecialtyController@view');
	Route::delete('/{id}','SpecialtyController@delete');

});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'doctor',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'DoctorController@index');
	Route::get('/form', 'DoctorController@form');
	Route::post('/','DoctorController@store');
	Route::post('/{id}','DoctorController@update');
	Route::get('/{id}','DoctorController@view');
	Route::delete('/{id}','DoctorController@delete');
	Route::get('/form_catalog', 'DoctorController@form_catalog');

});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'hospital',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'HospitalController@index');
	Route::get('/form', 'HospitalController@form');
	Route::post('/','HospitalController@store');
	Route::post('/{id}','HospitalController@update');
	Route::get('/{id}','HospitalController@view');
	Route::delete('/{id}','HospitalController@delete');

});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'ticket',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'TicketController@index');
	Route::post('/','TicketController@store');
	Route::get('/csQuestion', 'TicketController@questions');
	Route::get('/csEmail/{id}', 'TicketController@email');
	Route::post('/{id}/endTicket', 'TicketController@closeTicket');
	Route::post('/uploadFile', 'TicketController@uploadFile');
	Route::post('/deleteFile', 'TicketController@deleteFile');
	Route::post('/csQuestion', 'TicketController@SaveQuestions');
	Route::get('/form', 'TicketController@form');
	Route::get('/form/{id}', 'TicketController@getType');
	Route::get('/form/type/{id}','TicketController@getTypeTicket');
	Route::post('/{id}/detail','TicketController@storedetail');
	Route::get('/{id}', 'TicketController@viewdetail');
	Route::get('/claim/{id}' , 'TicketController@getClaim');
	Route::post('/claim/{id}/update' , 'TicketController@editClaim');
});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'ticketCat',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'TicketController@getTicketCat');
});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'diagnosis',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::post('/', 'DiagnosisController@store');
});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'pending',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'ClientServiceController@index');
	Route::get('/create','ClientServiceController@form');
	Route::get('/warranty_letter/{id}','ClientServiceController@formWarranty');
	Route::post('/uploadFile', 'ClientServiceController@uploadFile');
	Route::post('/deleteFile', 'ClientServiceController@deleteFile');
	Route::post('/observations', 'ClientServiceController@storeObservation');
	Route::get('/preview/{id}', 'ClientServiceController@preView');
	Route::get('view/{id}', 'ClientServiceController@view');
	Route::post('/email', 'ClientServiceController@sendEmail');
	Route::post('/warrantyEmail', 'ClientServiceController@sendEmail');
	Route::get('emailAgent/{id}','ClientServiceController@emailAgent');
	Route::get('emailAgentEmergency/{id}','ClientServiceController@emailAgentEmergency');
	Route::post('closeTicket/{id}','ClientServiceController@closeTicket');

});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'emergency',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/form', 'EmergencyController@form');
    Route::post('/','EmergencyController@store');

});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'type_hospitalization',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/', 'TypeHospitalizationController@index');
	Route::get('/form', 'TypeHospitalizationController@form');
	Route::post('/','TypeHospitalizationController@store');
	Route::post('/{id}','TypeHospitalizationController@update');
	Route::get('/{id}','TypeHospitalizationController@view');
	Route::delete('/{id}','TypeHospitalizationController@delete');

});

Route::group(['middleware' => 'jwt.auth',
	'prefix' => 'hospitalization',
	'namespace' => 'Modules\ClientService\Http\Controllers'], function()
{
	Route::get('/form', 'HospitalizationController@form');
    Route::post('/','HospitalizationController@store');

});
