angular
.module('ngNova')
.provider("novaNotification", novaNotification)
.config(['$stateProvider', '$urlRouterProvider', 'RestangularProvider', '__env', 'novaNotificationProvider',
	function($stateProvider, $urlRouterProvider, RestangularProvider, __env, novaNotificationProvider) {

	//novaNotification.showNotification(notification);
	var token = localStorage.getItem(__env.tokenst);

	RestangularProvider
    .setBaseUrl(__env.apiUrl)
    .setFullResponse(true)
    .addFullRequestInterceptor(function(element, operation, what, url, headers, params, httpConfig) {
       if (url !== 'auth/authenticate') {
           var token = localStorage.getItem(__env.tokenst);
           params.token = token
       }
       return { params: params };
	})
    .setErrorInterceptor(function (response) {
       if ( response.status == 401 ) {
           if(response.data.error == "token_expired"){
               //try to refresh the token

           }
           window.location.href=__env.frontUrl+"#/login";
           return false;
       } else if( response.status == 403 ) {
           //show forbidden page
           alert("No autorizado");
           return false;
       } else if ( response.status == 400 ) {
           if( response.data.error == "token_not_provided"){
               window.location.href=__env.frontUrl+"#/login";
               return false;
           }else if( response.data.error == "token_invalid"){
               window.location.href=__env.frontUrl+"#/login";
               return false;
           }else{
               return true;
           }
       }else {
           return true;
       }
   });

	/* SET Router - Api */
	function setRouter(type, url, module, title, resolve)
	{
		var controller = '';
		var templateUrl = '';
		var restConfig = {
			withView: true,
			token: localStorage.getItem(__env.tokenst)
		};

		// Type Controller
		if (type == 'LIST') {
			controller = 'ApiListCtrl as listable';
			templateUrl = 'api/list/list.view.html';
		}
		else if (type == 'FORM') {
			controller = 'ApiFormCtrl as formidable';
			templateUrl = 'api/form/formidables.view.html';
		}
		else if (type == 'ALERT') {
			controller = 'ApiDialogCtrl as dialog';
            templateUrl = 'api/dialog/dialog.view.html';
		}

		// Router Config
		return {
			url: url,
			views: {
				'': {
					controller: controller,
					templateUrl: templateUrl,
					resolve: {
						endPoint: function() {
							return {modul:module, title:title};
						},
						response: function(Restangular, $stateParams) {
							var urls = 'nova/sales/api/v1/'+module;
							var id = $stateParams.id;
							var ids = $stateParams.ids;
							var idc = $stateParams.idc;
							var idm = $stateParams.idm;
							var moduls = $stateParams.moduls;
							var types = $stateParams.types;

							if (ids !== undefined) {
								urls = 'nova/sales/api/v1/'+moduls+'/'+ids+'/'+types;
							} else if (id !== undefined) {
								urls = 'nova/sales/api/v1/'+module+'/'+id;
							} else if (idc !== undefined) {
								if (module == 'companies/config') {
									urls = 'nova/sales/api/v1/companies/'+idc+'?with='+idm;
								}
								if (module == 'sellers/config') {
									urls = 'nova/sales/api/v1/sellers/'+idc+'?with='+idm;
								}
							}

							//console.log('urls', urls);

							if (type == 'ALERT') {
								return {id: $stateParams.id, type: $stateParams.type, uri: $stateParams.uri};
							} else if (resolve == true) {
								return Restangular.all(urls).doGET('', restConfig);
							} else {
								return {};
							}
						}
					}
				}
			}
		};
	}

	/*$locationProvider.html5Mode(true);*/
	$urlRouterProvider.otherwise('/login');

	$stateProvider
	.state('root', {
		abstract: true,
		views: {
			'': {
				templateUrl: 'core/layout/headbar.view.html'
			}
		}
	})
	.state('root.home', {
		url: '/'
	})
	.state('login', {
		url: '/login',
		views: {
			'': {
				controller: 'loginCtrl as login',
				templateUrl: 'core/login/login.view.html'
			}
		}
	})
	.state('root.seguros', {
		url: '/seguros',
		views: {
			'': {
				controller: 'SidebarController as sidebar',
				templateUrl: 'core/layout/sidebar.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('menu/sidebar').doGET('',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})

	/* Companies */
	.state('root.seguros.companias', setRouter('LIST', '/companias', 'companies', 'Compañia', true))
	.state('root.seguros.companias.create', setRouter('FORM', '/crear', 'companies', 'Compañia', false))
	.state('root.seguros.companias.edit', setRouter('FORM', '/editar/:id', 'companies', 'Compañia', true))
	.state('root.seguros.companias.delete', setRouter('ALERT', '/eliminar/:id?uri&type', 'companies', 'Compañia', true))
	.state('root.seguros.companias.config', setRouter('FORM', '/config/:idc/:idm', 'companies/config', 'Compañia Configuracion', true))

	/* Sellers */
	.state('root.seguros.vendedores', setRouter('LIST', '/vendedores', 'sellers', 'Vendedor', true))
	.state('root.seguros.vendedores.create', setRouter('FORM', '/crear', 'sellers', 'Vendedor', false))
	.state('root.seguros.vendedores.edit', setRouter('FORM', '/editar/:id', 'sellers', 'Vendedor', true))
	.state('root.seguros.vendedores.delete', setRouter('ALERT', '/eliminar/:id?uri&type', 'sellers', 'Vendedor', true))
	.state('root.seguros.vendedores.config', setRouter('FORM', '/config/:idc/:idm', 'sellers/config', 'Vendedor Configuracion', true))

	/* Sales */
	.state('root.seguros.ventas', setRouter('LIST', '/ventas?ids&moduls&types', 'sales', 'Venta', true))
	.state('root.seguros.ventas.create', setRouter('FORM', '/crear', 'sales', 'Venta', false))
	.state('root.seguros.ventas.edit', setRouter('FORM', '/editar/:id', 'sales', 'Venta', true))
	.state('root.seguros.ventas.delete', setRouter('ALERT', '/eliminar/:id?uri&type', 'sales', 'Venta', true))
	.state('root.seguros.ventas.calculate', setRouter('ALERT', '/comision/:id?uri&type', 'sales', 'Venta', true))

	/* Commissions companies / sellers */
	.state('root.seguros.comisiones', setRouter('LIST', '/comisiones?ids&moduls&types', 'commissions/companies', 'Comisiones', true))
	.state('root.seguros.ccompanias', setRouter('LIST', '/comisiones/companias', 'commissions/companies', 'Comisiones: compañias', true))
	.state('root.seguros.cvendedores', setRouter('LIST', '/comisiones/vendedors', 'commissions/sellers', 'Comisiones: vendedores', true))

	.state('root.seguros.recepcion-emisiones', {
		url: '/recepcion-emisiones',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('reception').doGET('pending',
							{
								withView: 'true',
								'token': localStorage.getItem(__env.tokenst),
								'type': 'emission'
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.nueva-poliza', {
		url: '/nueva-poliza',
		views: {
			'': {
				controller: 'EmissionInitDocumentation as formidable',
				templateUrl: 'seguros/emision/initial-documentation.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('reception/newPolicy/initialDocumentation').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.nueva-poliza-ver', {
		url: '/nueva-poliza-ver/:process_ID',
		views: {
			'': {
				controller: 'EmissionInitDocumentation as formidable',
				templateUrl: 'seguros/emision/initial-documentation.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/initialDocumentation/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.enviar-cheque', {
		url: '/enviar-cheque/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/sendCheckIC/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.recibir-documentos-bd', {
		url: '/recibir-documentos-bd/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/receiveDocsBD/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.enviar-poliza-cliente', {
		url: '/enviar-poliza-cliente/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/sendPolicyCustomer/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.cargar-poliza-firmada', {
		url: '/cargar-poliza-firmada/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/uploadSignedPolicy/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.enviar-poliza-bd', {
		url: '/enviar-poliza-bd/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/sendPolicyBD/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-emisiones.cargar-acuse-recibido', {
		url: '/cargar-acuse-recibido/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newPolicy/uploadReceipt/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-reclamos', {
		url: '/recepcion-reclamos',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('reception').doGET('pending',
							{
								withView: 'true',
								'token': localStorage.getItem(__env.tokenst),
								'type': 'claim'
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-reclamos.nuevo-reclamo', {
		url: '/nuevo-reclamo',
		views: {
			'': {
				controller: 'NuevoReclamoCtrl as formidable',
				templateUrl: 'seguros/reclamos/nuevo-reclamo.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('reception/newClaims/').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-reclamos.nuevo-reclamo-ver', {
		url: '/nuevo-reclamo/:process_ID',
		views: {
			'': {
				controller: 'NuevoReclamoCtrl as formidable',
				templateUrl: 'seguros/reclamos/nuevo-reclamo.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newClaims/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-reclamos.envio-reclamo-bd', {
		url: '/envio-reclamo-bd/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newClaims/sendDocsBD/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-reclamos.registrar-acuse-recibido', {
		url: '/registrar-acuse-recibido/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/newClaims/uploadReceipt/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-liquidaciones', {
		url: '/recepcion-liquidaciones',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('reception').doGET('pending',
							{
								withView: 'true',
								'token': localStorage.getItem(__env.tokenst),
								'type': 'settlement'
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-liquidaciones.nueva-liquidacion', {
		url: '/nueva-liquidacion',
		views: {
			'': {
				controller: 'NuevoReclamoCtrl as formidable',
				templateUrl: 'seguros/liquidaciones/nueva-liquidacion.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/settlements/').doGET('',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.recepcion-liquidaciones.upload-files', {
		url: '/upload-files/:process_ID',
		views: {
			'': {
				controller: 'LiquidacionesSubirArchivos as formidable',
				templateUrl: 'seguros/liquidaciones/subir-archivos.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception/settlements/form').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.envio-documentos', {
		url: '/envio-documentos?receiver',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('reception').doGET('guiaremision',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst),
							'receiver': $stateParams.receiver
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.envio-documentos.create-guide', {
		url: '/create-guide?receiver&selected',
		views: {
			'': {
				controller: 'CreateGuideCtrl as formidable',
				templateUrl: 'seguros/guia-remision/create-guide.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						console.log($stateParams);
						return Restangular.all('reception/guiaremision').doGET('form',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst),
							'registerid[]': $stateParams.selected,
							'receiver': $stateParams.receiver
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision', {
		url: '/emision',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('emission').doGET('pending',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.cancel-procedure', {
		url: '/cancel-procedure/:id?uri&message',
		views: {
			'': {
				controller: 'CancelProcedureCtrl',
				templateUrl: 'seguros/cancel-procedure/cancel-procedure.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		url: '/revisar-formulario/:process_ID',
		views: {
			'': {
				templateUrl: 'seguros/emision/wizard/wizard-modal.view.html'/*,
				resolve: {
					response: function(Restangular){
						return Restangular.all('emission/newPolicy/uploadPolicyRequest').doGET('form',{'api_token': toqen});
					}
				}*/
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-1', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso1Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-1.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-2', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso2Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-2.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-3', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso3Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-3.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-4', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso4Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-4.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-5', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso5Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-5.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-6', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso6Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-6.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-formulario.paso-7', { // temporal.state('root.seguros.emision.crear-poliza', { /// WIZARD
		views: {
			'': {
				controller: 'WizPaso7Ctrl as formidable',
				templateUrl: 'seguros/emision/wizard/paso-7.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
						return Restangular.all('emission/newPolicy/uploadPolicyRequest/form/').doGET($stateParams.process_ID,
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.enviar-solicitud', {
		url: '/enviar-solicitud/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('emission/newPolicy/changeEffectiveDate/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.revisar-poliza', {
		url: '/revisar-poliza/:process_ID',
		views: {
			'': {
				controller: 'CheckPolicyDataCtrl as formidable',
				templateUrl: 'seguros/emision/check-policy-data.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular, $stateParams){
						return Restangular.all('emission/newPolicy/reviewProspectPolicy/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});/**/
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.registrar-respuesta', {
		url: '/registrar-respuesta/:process_ID',
		views: {
			'': {
				controller: 'NewPolicyCustomerResponseCtrl',
				templateUrl: 'seguros/emision/customer-response.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('emission/newPolicy/registerCustomerResponse/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.registrar-pago', {
		url: '/registrar-pago/:process_ID',
		views: {
			'': {
				controller: 'NewPolicyRegisterPaymentCtrl',
				templateUrl: 'seguros/emision/register-payment.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('emission/newPolicy/registerPayment/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.enviar-factura', {
		url: '/cargar-factura/:process_ID',
		views: {
			'': {
				controller: 'NewPolicyConfirmPaymentCtrl',
				templateUrl: 'seguros/emision/confirm-payment-and-invoice.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('emission/newPolicy/registerInvoice/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision.enviar-docs-recepcion', {
		url: '/enviar-docs-recepcion/:process_ID',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', '$stateParams', function(Restangular, $stateParams){
						return Restangular.all('emission/newPolicy/sendDocsRec/'+$stateParams.process_ID).doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision-tiempos', {
		url: '/emision/emision-tiempos',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('emission/report').doGET('historialTramites',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.emision-actuales', {
		url: '/emision/emision-actuales',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('emission/report').doGET('tramitesActuales',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos', {
		url: '/reclamos/pending',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('claims').doGET('pending',
							{
								withView: 'true',
								'token': localStorage.getItem(__env.tokenst)
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.revision-clasificacion', {
		url: '/revision-clasificacion/:process_ID',
		views: {
			'': {
				controller: 'PendingClaimCtrl as pendClaimCtrl',
				templateUrl: 'seguros/reclamos/pending-claim.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('claims/reviewDocuments/'+$stateParams.process_ID).doGET('form',
							{
								withView: 'true',
								'token': localStorage.getItem(__env.tokenst)
							});
					}]
				}
			}
		}
	})
	/*.state('root.seguros.reclamos.revision-clasificacion', {
		url: '/revision-clasificacion/:process_ID',
		views: {
			'': {
				controller: 'ClaimReviewDocsCtrl as formidable',
				templateUrl: 'seguros/reclamos/wizard-review-docs/wizard-modal.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('claims/reviewDocuments/'+$stateParams.process_ID).doGET('form',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos.revision-clasificacion.paso-1', {
		views: {
			'': {
				templateUrl: 'seguros/reclamos/wizard-review-docs/step1.view.html',
			}
		}
	})
	.state('root.seguros.reclamos.revision-clasificacion.paso-2', {
		views: {
			'': {
				templateUrl: 'seguros/reclamos/wizard-review-docs/step2.view.html',
			}
		}
	})
	.state('root.seguros.reclamos.revision-clasificacion.paso-3', {
		views: {
			'': {
				templateUrl: 'seguros/reclamos/wizard-review-docs/step3.view.html',
			}
		}
	})*/
	.state('root.seguros.reclamos.impresion-carta', {
		url: '/impresion-carta/:process_ID',
		views: {
			'': {
				controller: 'ClaimPrintLetterCtrl as formidable',
				templateUrl: 'seguros/reclamos/print-claim-letter.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('claims/printLetter/'+$stateParams.process_ID).doGET('form',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos-liquidaciones', {
		url: '/liquidaciones/pending',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('settlements').doGET('pending',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.registrar-liquidacion', {
		url: '/registrar-liquidacion/:process_ID',
		views: {
			'': {
				controller: 'PendingSettlementCtrl',
				templateUrl: 'seguros/liquidaciones/pending-settlement.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('settlements/registerForm/'+$stateParams.process_ID).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos-liquidaciones.registrar-valores', {
		url: '/registrar-valores/:process_ID',
		views: {
			'': {
				controller: 'PendingLiquidationCtrl as PendingLiq',
				templateUrl: 'seguros/liquidaciones/pending-liquidation.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('settlements/registerForm/'+$stateParams.process_ID).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos-liquidaciones.registrar-pago', {
		url: '/registrar-pago/:process_ID',
		views: {
			'': {
				controller: 'RefundSettlementCtrl as RefundSettlement',
				templateUrl: 'seguros/liquidaciones/refund-settlement.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('settlements/registerPayment/'+$stateParams.process_ID).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos-liquidaciones.terminar-liquidacion', {
        url: '/finalizar/:process_ID',
        views: {
            '': {
                controller: 'FinishSettlementCtrl as fsdialog',
                templateUrl: 'seguros/liquidaciones/finish-settlement.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.process_ID,
								"message": "Esta seguro desea cerrar el proceso de liquidación",
								"uri": "settlements/finish/"+$stateParams.process_ID
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.reclamos-tiempos', {
		url: '/reclamos/reclamos-tiempos',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('claims/times').doGET('',
							{
								withView: 'true',
								'token': localStorage.getItem(__env.tokenst)
							});
					}]
				}
			}
		}
	})
	.state('root.seguros.afiliados', {
		url: '/afiliados',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('affiliate').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.afiliados.view', {
		url: '/:process_ID',
		views: {
			'': {
				controller: 'AffiliateCtrl as AffiliateCtrl',
				templateUrl: 'seguros/afiliado/affiliate.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('affiliate/'+$stateParams.process_ID).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.polizas', {
		url: '/poliza',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('policy').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.polizas.view', {
		url: '/:process_ID',
		views: {
			'': {
				controller: 'PolicyCtrl as PolicyCtrl',
				templateUrl: 'seguros/policy/policy.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('policy/'+$stateParams.process_ID).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.polizas.list_files', {
		url: '/:id/files',
		views: {
			'': {
				controller: 'PolicyFileCtrl as PolicyFileCtrl',
				templateUrl: 'seguros/policy/policy_files.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('policy/'+$stateParams.id).doGET('files',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos-historial', {
		url: '/reclamos-historial',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('claim').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.reclamos-historial.view', {
		url: '/:id',
		views: {
			'': {
				controller: 'ClaimCtrlView',
				templateUrl: 'seguros/claims/claim_view.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('claim/'+$stateParams.id).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
						);
					}]
				}
			}
		}
	})
	/*.state('root.seguros.planes', {
		url: '/planes',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				/*resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('carrier').doGET('',
							{withView: 'true', 'api_token': toqen});
					}]
				}*/
			/*}
		}
	})*/
	.state('root.seguros.plan-detalle', {
		url: '/plan-detalle',
		views: {
			'': {
				controller: 'PlanDetailsCtrl as planDetails',
				templateUrl: 'seguros/planes/plan.details.view.html',
				/*resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('carrier').doGET('',
							{withView: 'true', 'api_token': toqen});
					}]
				}*/
			}
		}
	})
	.state('root.seguros.mensajeros', {
		url: '/mensajeros',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('carrier').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.mensajeros.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('carrier').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.mensajeros.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('carrier').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.mensajeros.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.proveedorservicios', {
		url: '/proveedorservicios',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('supplier').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.proveedorservicios.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('supplier').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.proveedorservicios.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('supplier').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.proveedorservicios.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.empleados', {
		url: '/empleados',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('rrhh/employee').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.empleados.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('rrhh/employee').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.empleados.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('rrhh/employee/'+$stateParams.id).doGET('form',
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.empleados.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })

	.state('root.seguros.planes', {
		url: '/planes',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('plan').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.planes.view', {
		url: '/:id',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular,$stateParams){

						return Restangular.all('plan/'+$stateParams.id).doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.health_specialities', {
		url: '/especialidades',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('specialty').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.health_specialities.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('specialty').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.health_specialities.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('specialty').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})

	.state('root.seguros.health_specialities.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.doctor', {
		url: '/doctor',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('doctor').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.doctor.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('doctor').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.doctor.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('doctor').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.doctor.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.sales', {
		url: '/agente',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('agente').doGET('',
							{withView: 'true'}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.sales.create', {
		url: '/create',
		views: {
			'': {
				controller: 'AgenteCtrl',
				templateUrl: 'seguros/ventas/agente.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('agente').doGET('form',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.sales.edit', {
		url: '/edit/:id',
		views: {
			'': {
				controller: 'AgenteCtrl',
				templateUrl: 'seguros/ventas/agente.view.html',
				resolve:  {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('agente').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.sales.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.hospital', {
		url: '/hospital',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('hospital').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.hospital.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('hospital').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.hospital.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('hospital').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.hospital.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
	.state('root.seguros.costo-poliza', {
		url: '/costos-poliza',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('payment').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.costo-poliza.pay_policy', {
		url: '/pagar/:id',
		views: {
			'': {
				controller: 'PayPolicyCtrl',
				templateUrl: 'seguros/payment/paypolicy.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('payment').doGET('form',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.ticket', {
		url: '/ticket',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('ticket').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})

	.state('root.seguros.ticket.create', {
		url: '/create',
		views: {
			'': {
				controller: 'TicketCtrl',
				templateUrl: 'seguros/ticket/ticket.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('ticket').doGET('form',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
						}]
					}
			    }
		     }
	     })
	.state('root.seguros.ticket.view', {
		url: '/:id',
		views: {
			'': {
				controller: 'TicketDetailCtrl',
				templateUrl: 'seguros/ticket/ticketdetail.view.html',
				resolve:  {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('ticket').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.new-quotation', {
		url: '/nueva-cotizacion',
		views: {
			'': {
				controller: 'NewQuotationCtrl as newQuatation',
				templateUrl: 'seguros/cotizaciones/new-quotation-bd.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('quotation').doGET('form',
							{
								'token': localStorage.getItem(__env.tokenst)
							}
						);

					}]
				}
			}
		}
	})
	.state('root.seguros.quotation', {
		url: '/quotation',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('quotation').doGET('',
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);

					}]
				}
			}
		}
	})
	.state('root.seguros.view-quotation', {
		url: '/quotation/view/:id',
		views: {
			'': {
				controller: 'QuotationViewCtrl',
				templateUrl: 'seguros/cotizaciones/view-quotation.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('quotation').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);

					}]
				}
			}
		}
	})
	.state('root.seguros.reporte', {
		url: '/reporte',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('claim').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.reporte.view', {
		url: '/:id',
		views: {
			'': {
				controller: 'ReportDetailCtrl',
				templateUrl: 'seguros/claim_report/reportdetail.view.html',
				resolve:  {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('claim').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.dashboard-emission', {
		url: '/dashboardEmision',
		views: {
			'': {
				controller: 'DashboardCtrl',
				templateUrl: 'seguros/dashboard/dashboard.view.html',
				resolve: {
					emissionavgtimeresp: ['Restangular', function(Restangular){
						return Restangular.all('dashboard/procedureTime').doGET('',
							{
								'procedure':'newPolicy'
							}
						);
					}],
					employeesresp: ['Restangular', function(Restangular){
						return Restangular.all('rrhh/employee').doGET('',{});
					}],
					salesbymonthresp: ['Restangular', function(Restangular){
						return Restangular.all('dashboard/policiesSales').doGET('',
							{
								'type':'emision'
							}
						);
					}],
					typedashboard: ['$stateParams', function($stateParams){
						return {
								"type":"emission"
								}
					}],
					agentsalesresp: ['Restangular', function(Restangular){
						return Restangular.all('dashboard/agentSales').doGET('',
							{
								'type':'emision'
							}
						);
					}],
				}
			}
		}
	})
	.state('root.seguros.dashboard-claim', {
		url: '/dashboardClaim',
		views: {
			'': {
				controller: 'DashboardCtrl',
				templateUrl: 'seguros/dashboard/dashboard.view.html',
				resolve: {
					typedashboard: ['$stateParams', function($stateParams){
						return {
								"type":"emission"
								}
					}],
				}
			}
		}
	})
	.state('root.seguros.clientservice_pending', {
		url: '/pending',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('pending').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
					}]
				}
			}
		}
	})
	.state('root.seguros.clientservice_pending.create', {
		url: '/create',
		views: {
			'': {
				controller: 'ProcessedPendingCtrl',
				templateUrl: 'seguros/servicio_cliente/processed.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('pending/create').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)}
							);
						}]
					}
			    }
		     }
	     })
	.state('root.seguros.clientservice_pending.carta-garantia', {
		url: '/warranty_letter/:procedure_ID',
		views: {
			'': {
				controller: 'WarrantyLetterCtrl',
				templateUrl: 'seguros/servicio_cliente/warranty_letter.view.html',
				resolve:  {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('pending').doGET('warranty_letter/'+$stateParams.procedure_ID,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.clientservice_pending.ingreso-datos', {
		url: '/:procedure_ID',
		views: {
			'': {
				controller: 'EmergencyViewCtrl',
				templateUrl: 'seguros/servicio_cliente/emergency-view.view.html',
				resolve:  {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('pending/view').doGET($stateParams.procedure_ID,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.type_hospitalization', {
		url: '/tipo_hospitalizacion',
		views: {
			'': {
				controller: 'ListCtrl as listable',
				templateUrl: 'core/listables/listables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('type_hospitalization').doGET('',
							{withView: 'true',
							'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.type_hospitalization.create', {
		url: '/crear',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular', function(Restangular){
						return Restangular.all('type_hospitalization').doGET('form',
							{'token': localStorage.getItem(__env.tokenst)});
					}]
				}
			}
		}
	})
	.state('root.seguros.type_hospitalization.edit', {
		url: '/editar/:id',
		views: {
			'': {
				controller: 'FormCtrl as formidable',
				templateUrl: 'core/formidables/formidables.view.html',
				resolve: {
					response: ['Restangular','$stateParams', function(Restangular,$stateParams){
						return Restangular.all('type_hospitalization').doGET($stateParams.id,
							{
								'token': localStorage.getItem(__env.tokenst),
								'withView': true
							}
						);
					}]
				}
			}
		}
	})
	.state('root.seguros.type_hospitalization.delete', {
        url: '/eliminar/:id?uri&message',
        views: {
            '': {
                controller: 'DialogCtrl as dialog',
                templateUrl: 'core/dialogables/dialog.view.html',
                resolve: {
                    response: ['$stateParams', function($stateParams) {
                        return {"id": $stateParams.id,
								"message": $stateParams.message,
								"uri": $stateParams.uri
								};
                    }]
                }
            }
        }
    })
    .state('root.seguros.renovations', {
        url: '/renovaciones',
        views: {
            '': {
                controller: 'ListCtrl as listable',
                templateUrl: 'core/listables/listables.view.html',
                resolve: {
                    response: ['Restangular', function(Restangular){
                        return Restangular.all('renovations').doGET('',
                            {
                                withView: 'true',
                                'token': localStorage.getItem(__env.tokenst),
                                'type': 'emission'
                            });
                    }]
                }
            }
        }
    })
    .state('root.seguros.renovations.add-new-file', {
        url: '/cagar-renovaciones',
        views: {
            '': {
                controller: 'RenovacionesCtrl as renovable',
                templateUrl: 'seguros/renovaciones/cargar-renovaciones.html',
                resolve: {
                    response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
                        return Restangular.all('reception/newClaims/').doGET('form',
                            {'token': localStorage.getItem(__env.tokenst)});
                    }]
                }
                // controller: 'NuevoReclamoCtrl as formidable',
                // templateUrl: 'seguros/reclamos/nuevo-reclamo.html',
                // resolve: {
                // 	response: ['Restangular', '$stateParams', function(Restangular, $stateParams){ //gb.dev/emission/newPolicy/uploadPolicyRequest/form/
                // 		return Restangular.all('reception/newClaims/').doGET('form',
                // 			{'token': localStorage.getItem(__env.tokenst)});
                // 	}]
                // }
            }
        }
    })

}]);
