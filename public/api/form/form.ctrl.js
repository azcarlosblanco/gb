
angular
.module('ngNova')
.controller('ApiFormCtrl', ['response', 'endPoint', 'Restangular', '$filter', '$scope', '$state', '$stateParams', 'hotkeys', '__env', 'webNotification',
function(response, endPoint, Restangular, $filter, $scope, $state, $stateParams, hotkeys, __env, webNotification) {
	
	// Module
	var modul = endPoint.modul;
	var title = endPoint.title;
	var idm = $stateParams.idm;
	var token = localStorage.getItem(__env.tokenst);

	//console.log('idm', idm);
	
	/* Details Sales */
	$scope.formSale = false;
	if (modul == 'sales') {
		$scope.formSale = true;
	}
	
	var allItems = [];
	
	// Data Value
	this.data_fields = {};
	var dataFields = {};
	
	// Data response
	$scope.data = [];
	if (response.data !== undefined) {
		var rs = response.data.data;
		$scope.data = rs;
		angular.forEach(rs, function(value, key) {
			if (
				key == 'fixed_config' || 
				key == 'sale_type_config' || 
				key == 'plan_config' || 
				key == 'plan_and_sale_type_config' ||
				key == 'service_type_config'
			) {
				if (typeof value == 'object') {
					for (var i in value) {
						if (i == 'company_id') {
							if (typeof dataFields['company_id'] == 'undefined') {
								dataFields[i] = value[i]+'';
							}
						} else {
							dataFields[i+'_'+key] = value[i]+'';
						}
					}
				} else {
					dataFields[key] = value+'';
				}
			} else {
				if (key == 'details' && typeof value == 'object') {
					allItems = value;
				} else {
					dataFields[key] = value+'';
				}
			}
		});
		this.data_fields = dataFields;
	}
	
	$scope.resetAll = function() {
        this.allList = allItems;
        this.concept = '';
        this.amount = '';
        this.apply_commission = '';
    }

    $scope.add = function() {
		var item = new Array();
		
		item.concept = this.concept;
		item.amount = this.amount;
		item.apply_commission = this.apply_commission;
		
		allItems.push(item);
		
		//console.log('allItems:', allItems);
		
		$scope.resetAll();
    }

	$scope.remove = function(item) {
		for (i = 0; i < allItems.length; i++) {
			if (allItems[i] == item) {
				allItems.splice(i, 1);
				$scope.resetAll();
			}
		}
	}
  
    $scope.resetAll();
	
	console.log('dataFields', dataFields);
	
	var optionCompanies = {};
	
	// Sellers select
	var optionSellers = {};
	if (modul == 'sales') {
		urlWithToken = 'nova/sales/api/v1/companies?token='+token;
		var api = Restangular
			.one(urlWithToken)
			.withHttpConfig({transformRequest: angular.identity})
			.doGET('', {'token': token}).then(function(results) {
				if (results) {
					var rs = results.data.data;
					angular.forEach(rs, function(value, key) {
						optionCompanies[value.id+''] = value.name+'';
					});
				}
			});
	}
	
	// Sellers select
	var optionSellers = {};
	if (modul == 'sales') {
		urlWithToken = 'nova/sales/api/v1/sellers?token='+token;
		var api = Restangular
			.one(urlWithToken)
			.withHttpConfig({transformRequest: angular.identity})
			.doGET('', {'token': token}).then(function(results) {
				if (results) {
					var rs = results.data.data;
					angular.forEach(rs, function(value, key) {
						optionSellers[value.id+''] = value.name+'';
					});
				}
			});
	}
	
	var optionCommission = {
		'1': "Comisión fija",
		'2': "Comisión por tipo de venta",
		'3': "Comisión por plan vendido",
		'4': "Comisión por plan vendido y tipo de venta",
		'5': "Comisión por el tipo de servicio vendido"
	};
	
	var optionSales = {
		'1': 'Contracto'
	};
	
	var optionPlans = {
		'1': 'Contracto'
	};
	
	var optionServices = {
		'1': 'Contracto'
	};
	
	var modulSection = [];
	
	//console.log('module:', modul);
	
	// Sales
	if (modul == 'sales') {
		$scope.title = 'DATOS DE VENTA';
		modulSection.push({
			"label":"Datos de Ventas",
			"fields":[
				{"label":"Compañia","name":"company_id","type":"select","options":optionCompanies},
				{"label":"Vendedor","name":"seller_id","type":"select","options":optionSellers},
				{"label":"Número de cotización","name":"quote_number","type":"text"},
				{"label":"Cantidad total","name":"total_amount","type":"text"},
				{"label":"Fecha de venta","name":"sale_date","type":"date"},
				{"label":"Fecha de pago","name":"payment_date","type":"date"},
				{"label":"Fecha de confirmación","name":"confirmation_date","type":"date"},
				{"label":"Tipo de Plan","name":"plan_id","type":"select","options":optionPlans},
				{"label":"Tipo de Servicio","name":"service_type_id","type":"select","options":optionServices},
				{"label":"Tipo de Venta","name":"sale_type_id","type":"select","options":optionSales}
			]
		});
	}
	// Sellers
	if (modul == 'sellers') {
		$scope.title = 'DATOS DE VENDEDOR';
		modulSection.push({
			"label":"Datos de Vendedor",
			"fields":[
				{"label":"Nombre","name":"name","type":"text"},
				{"label":"Cuota mensual","name":"monthly_fee","type":"text"},
				{"label":"Porcentaje extra","name":"percentage_extra","type":"text"},
				{"label":"Tipo de comisión","name":"commission_type_id","type":"select","options":optionCommission}
			]
		});
	}
	// Companies
	if (modul == 'companies') {
		$scope.title = 'DATOS DE COMPAÑIA';
		modulSection.push({
			"label":"Datos de Compañia",
			"fields":[
				{"label":"Nombre","name":"name","type":"text"},
				{"label":"Tipo de comisión","name":"commission_type_id","type":"select","options":optionCommission}
			]
		});
	}
	// Config: Companies / Sellers
	if (modul == 'companies/config' || modul == 'sellers/config') {
		if (modul == 'companies/config') {
			$scope.title = 'CONFIGURACION DE COMPAÑIA';
		}
		// Compañia
		if (modul == 'sellers/config') {
			$scope.title = 'CONFIGURACION DE VENDEDOR';
			modulSection.push({
				"label":"Compañia",
				"fields":[
					{"label":"Seleccionar compañia","name":"company_id","type":"select","options":optionCompanies}
				]
			});
		}
		// Comision Fija
		if (idm !== undefined && idm == '1') {
			modulSection.push({
				"id":"id_fixed_config",
				"label":"Comision Fija",
				"fields":[
					{"name":"id_fixed_config","type":"hidden"},
					{"label":"Porcentaje","name":"percentage_fixed_config","type":"text"}
				]
			});
		}
		// Tipo de Venta
		if (idm !== undefined && idm == '2') {
			modulSection.push({
				"label":"Comision Tipo de Venta",
				"fields":[
					{"label":"Tipo de venta","name":"sale_type_id_sale_type_config","type":"select","options":optionSales},
					{"label":"Porcentaje","name":"percentage_sale_type_config","type":"text"}
				]
			});
		}
		// Plan
		if (idm !== undefined && idm == '3') {
			modulSection.push({
				"label":"Comision por Plan",
				"fields":[
					{"label":"Tipo de Plan","name":"plan_id_plan_config","type":"select","options":optionPlans},
					{"label":"Porcentaje","name":"percentage_plan_config","type":"text"}
				]
			});
		}
		// Plan y Tipo de Venta
		if (idm !== undefined && idm == '4') {
			modulSection.push({
				"label":"Comision por Plan y Tipo de Venta",
				"fields":[
					{"label":"Tipo de Plan","name":"plan_id_plan_and_sale_type_config","type":"select","options":optionPlans},
					{"label":"Tipo de Venta","name":"sale_type_id_plan_and_sale_type_config","type":"select","options":optionSales},
					{"label":"Porcentaje","name":"percentage_plan_and_sale_type_config","type":"text"}
				]
			});
		}
		// Tipo de Servicio
		if (idm !== undefined && idm == '5') {
			modulSection.push({
				"label":"Comision Tipo de Servicio",
				"fields":[
					{"label":"Tipo de Servicio","name":"service_type_id_service_type_config","type":"select","options":optionServices},
					{"label":"Porcentaje","name":"percentage_service_type_config","type":"text"}
				]
			});
		}
	}
	// Commission: Companies / Sellers
	if (modul == 'commissions/companies' || modul == 'commissions/sellers') {
		if (modul == 'commissions/companies') {
			$scope.title = 'Comision de Compañia';
		}
		if (modul == 'commissions/sellers') {
			$scope.title = 'Comision de Vendedor';
		}
		modulSection.push({
			"label":"Comision de Venta",
			"fields":[
				{"label":"Seleccionar venta","name":"sale_id","type":"select","options":optionSales}
			]
		});
	}
	
	// Module Section
	this.sections = modulSection;
	
	// Button Action
	this.actions = [
		{"display":"Guardar","type":"submit"},
		{"display":"Cancelar","type":"href"}
	];
	
	$scope.form = {};
	$scope.focused = [];

	$scope.submit = function() {
		// Validate Form
		if ($scope.form) {
			// Call Api
			if (modul == 'companies/config' || modul == 'sellers/config') {
				// Comision Fija
				if (idm !== undefined && idm == '1') {
					callApi(data, modul+'/fixed', '_fixed_config');
				}
				// Tipo de Venta
				if (idm !== undefined && idm == '2') {
					callApi(data, modul+'/sale-type', '_sale_type_config');
				}
				// Plan
				if (idm !== undefined && idm == '3') {
					callApi(data, modul+'/plan', '_plan_config');
				}
				// Plan y Tipo de Venta
				if (idm !== undefined && idm == '4') {
					callApi(data, modul+'/plan-and-sale-type', '_plan_and_sale_type_config');
				}
				// Tipo de Servicio
				if (idm !== undefined && idm == '5') {
					callApi(data, modul+'/service-type', '_service_type_config');
				}
			} else {
				callApi(data, modul, '');
			}
		}
	};

	function callApi(data, endpoint, exp) 
	{
		// URL
		var id = $stateParams.id;
		var idc = $stateParams.idc;
		var urlWithToken = '';
		
		// Form Data - PUT / POST
		var putcData = {};
		var postData = new FormData();
		
		for (var i in $scope.form) {
			var dt = $scope.form[i];
			if (i.indexOf('_date') > 0) {
				dt = $filter('date')(dt, 'yyyy-MM-dd');
			}
			if (exp !== '') {
				if (i.indexOf(exp) > 0) {
					i = i.replace(exp, '');
				}
				if (i == 'id') {
					id = dt;
				}
				putcData[i] = dt;
				postData.append(i, dt);
			} else {
				putcData[i] = dt;
				postData.append(i, dt);
			}
		}
		
		// ID
		if (id !== undefined) {
			urlWithToken = 'nova/sales/api/v1/'+endpoint+'/'+id+'?token='+token;
		} else {
			urlWithToken = 'nova/sales/api/v1/'+endpoint+'?token='+token;
		}
		
		// Sales
		if (modul == 'sales') {
			for (i = 0; i < allItems.length; i++) {
				postData.append("details["+i+"][concept]", allItems[i].concept);
				postData.append("details["+i+"][amount]", allItems[i].amount);
				postData.append("details["+i+"][apply_commission]", allItems[i].apply_commission);
			}
		}
		
		// ID
		if (id !== undefined) {
			console.log('ID', id);
			putcData['id'] = id;
			putcData['_method'] = 'PUT';
		}
		
		// Company ID
		if (modul == 'companies/config' && idc !== undefined && id == undefined) {
			postData.append('company_id', idc);
		}
		
		// Seller ID
		if (modul == 'sellers/config' && idc !== undefined && id == undefined) {
			postData.append('seller_id', idc);
		}
		
		// Api Token
		putcData['token'] = token;
		postData.append('token', token);
		
		// API - Headers
		var headers = {'Content-Type': undefined};
		
		// API - Config
		var api = Restangular
			.one(urlWithToken, id)
			.withHttpConfig({transformRequest: angular.identity});
		
		// API - Method
		if (id !== undefined) {
			var callApi = api.put(putcData);
			console.log('putcData:', putcData);
		} else {
			var callApi = api.customPOST(postData, '', undefined, headers);
			console.log('postData:', postData);
		}
		
		// API - Call
		callApi.then(
			function successCallback(response) {
				setSuccess(response);
			},
			function errorCallback(response) {
				setError(response);
			}
		);
	}
	
	function setSuccess(response) {
		var notificacion = {};
		switch(response.status) {
			case 201:
				notificacion.title = "Formulario enviado";
				notificacion.body = "El formulario se envió exitosamente";
				$scope.showNotification(notificacion);
				$state.go('^', {}, {reload: true});
			break;
			default:
				notificacion.title = "Formulario enviado";
				notificacion.body = "El formulario se envió exitosamente";
				$scope.showNotification(notificacion);
				$state.go('^', {}, {reload: true});
			break;
		}		
	}
	
	function setError(response) {
		var notificacion = {};
		var data = "";
		if (response.data !== null && response.data !== undefined) {
			if (response.data.error !== undefined) {
				console.log('response error', response.data.error);
				var errors = response.data.error;
				angular.forEach(errors, function(value, key) {
					data = data + key + ': ' + value + "\n";
				});
			} else {
				data = 'Error interno del servidor';
			}
			notificacion.title = "Error al enviar el formulario";
			notificacion.body = data;
			$scope.showNotification(notificacion);
		}
	}
	
    $scope.showNotification = function(data) {
        webNotification.showNotification(data.title, {
            body: data.body,
            icon: 'assets/img/GB.ico',
            onClick: function onNotificationClicked() {
                console.log('Notification clicked.');
            },
            autoClose: 4000 //auto close the notification after 4 seconds (you can manually close it via hide function)
        }, function onShow(error, hide) {
            if (error) {
                window.alert('Unable to show notification: ' + error.message);
            } else {
                console.log('Notification Shown.');
                setTimeout(function hideNotification() {
                    console.log('Hiding notification....');
                    hide(); //manually close the notification (you can skip this if you use the autoClose option)
                }, 5000);
            }
        });
    }

    hotkeys.bindTo($scope)
    .add({
        combo: 'pagedown',
        allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
        description: 'blah blah',
        callback: function() {
            var focused = angular.element(document.activeElement);
            var id = '#section-'+(Number(focused.attr('section'))+1);
            var target = angular.element(document.querySelector(id));
            var field = angular.element(target[0].querySelector('.first'));
            window.scrollTo(0, field[0].offsetTop + 100);
            field[0].focus();
        }
    })
    .add({
        combo: 'pageup',
        allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
        description: 'blah blah',
        callback: function() {
            var focused = angular.element(document.activeElement);
            var id = '#section-'+(Number(focused.attr('section'))-1);
            var target = angular.element(document.querySelector(id));
            var field = angular.element(target[0].querySelector('.first'))[0].focus();
        }
    });
}]);