var sdbr = {
    "message": [],
    "data": {
        "sections": [{
            "label": "Recepción",
            "groups": [{
                "label": "Nueva Póliza",
                "notifications": 0,
                "icon": "glyphicon glyphicon-pencil",
                "url": ".recepcion-emisiones.nueva-poliza"
            }, {
                "label": "Nuevo Reclamo",
                "notifications": 0,
                "icon": "glyphicon glyphicon-pencil",
                "url": ".recepcion-reclamos.nuevo-reclamo"
            }, {
                "label": "Emisiones Pendientes",
                "notifications": 0,
                "icon": "glyphicon glyphicon-th-list",
                "url": ".recepcion-emisiones"
            }, {
                "label": "Reclamos Pendientes",
                "notifications": 0,
                "icon": "glyphicon glyphicon-th-list",
                "url": ".recepcion-reclamos"
            }, {
                "label": "Liquidaciones Pendientes",
                "notifications": 0,
                "icon": "glyphicon glyphicon-th-list",
                "url": ".recepcion-liquidaciones"
            }, {
                "label": "Envío Documentos",
                "notifications": 0,
                "icon": "glyphicon glyphicon-th-list",
                "url": ".envio-documentos({receiver: null})"
            }]
        }, {
            "label": "Emisiones",
            "groups": [{
                "label": "Trámites pendientes",
                "notifications": 0,
                "icon": "glyphicon glyphicon-th-list",
                "url": ".emision"
            }, {
                "label": "Trámites Actuales",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".emision-actuales"
            }, {
                "label": "Trámites Tiempos",
                "notifications": 0,
                "icon": "glyphicon glyphicon-time",
                "url": ".emision-tiempos"
            }]
        }, {
            "label": "Renovación",
            "groups": [{
                "label": 'Listado Renovaciones',
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".renovacion-list"
            }]

        }, {
            "label": "General",
            "groups": [{
                "label": "Pólizas",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".polizas"
            }, {
                "label": "Afiliados",
                "notifications": 0,
                "icon": "glyphicon glyphicon-th-list",
                "url": ".afiliados"
            }, {
                "label": "Reclamos",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".reclamos-historial"
            }, {
                "label": "Compania Seguros",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".companiasegeguros"
            }, {
                "label": "Planes",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".planes"
            }, {
                "label": "Mensajeros",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".mensajeros"
            }, {
                "label": "Especialidades",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".especialidades"
            }, {
                "label": "Proveedor Servicios",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".proveedorservicios"
            }, {
                "label": "Hospitales",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".hospitales"
            }, {
                "label": "Medicos",
                "notifications": 0,
                "icon": "glyphicon glyphicon-tasks",
                "url": ".medicos"
            }, ]
        }, {
            "label": "RRHH",
            "groups": [{
                "label": "Empleados",
                "notifications": 0,
                "icon": "glyphicon glyphicon-user",
                "url": ".empleados"
            }]
        }, ]
    },
    "route": "#"
};

var addSection = {
		"label": "Configuracion Ventas",
		"groups": [{
				"label": "Compañias",
				"notifications": 0,
				"icon": "glyphicon glyphicon-tasks",
				"url": ".companias"
		}, {
				"label": "Vendedores",
				"notifications": 0,
				"icon": "glyphicon glyphicon-tasks",
				"url": ".vendedores"
		}, {
				"label": "Ventas",
				"notifications": 0,
				"icon": "glyphicon glyphicon-tasks",
				"url": ".ventas"
		}, {
				"label": "Comisiones Compañias",
				"notifications": 0,
				"icon": "glyphicon glyphicon-tasks",
				"url": ".ccompanias"
		}, {
				"label": "Comisiones Vendedores",
				"notifications": 0,
				"icon": "glyphicon glyphicon-tasks",
				"url": ".cvendedores"
		}]
};

angular
    .module('ngNova')
    .controller('SidebarController', ['response', 'Restangular', '$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification',
        function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification) {
            //var container = document.getElementById('sidebar');
            //Ps.initialize(container);
            //sidebar data comes from back
            var menuSection = response.data.data.sections;
			menuSection.push(addSection);
			
			this.sections = menuSection;
			
			console.log("listado ... ", menuSection);
        }
    ])
    .directive('sidebarSection', function() {
        return {
            restrict: 'E',
            templateUrl: 'core/layout/sidebar-section.html'
        };
    });
