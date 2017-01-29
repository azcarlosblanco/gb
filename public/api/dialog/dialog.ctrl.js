angular
.module('ngNova')
.controller('ApiDialogCtrl', ['response', 'Restangular', '$scope', '$state', '$stateParams', '__env', 'webNotification',
function(response, Restangular, $scope, $state, $stateParams, __env, webNotification) {
	
	// Type
	if (response.type == 'delete') {
		$scope.type = 'delete';
		$scope.title = 'Eliminar';
		$scope.message = 'Desea eliminar el registro?';
	} else {
		$scope.type = 'calculate';
		$scope.title = 'Calcular';
		$scope.message = 'Desea realizar los calculos de comisiones?';
	}

	// Delete
	$scope.onDelete = function() {
		var token = localStorage.getItem(__env.tokenst);
		var url = 'nova/sales/api/v1/' + response.uri + '/' + response.id + '?token=' + token;
		
		Restangular
		.one(url)
		.withHttpConfig({transformRequest: angular.identity})
		.customDELETE(undefined, undefined, {'Content-Type': undefined})
		.then(
			function(response) {
				successCallback(response, 'El registro fue eliminado.');
			},
			function(response) { 
				errorCallback(response, '');
			}
		);
	};
	
	// Calculate
	$scope.onCalculate = function() {
		// Token
		var token = localStorage.getItem(__env.tokenst);
		
		// ID
		var id = response.id;
		
		// URL Endpoint
		var urlCompany = 'nova/sales/api/v1/commissions/companies?token=' + token;
		var urlSeller = 'nova/sales/api/v1/commissions/sellers?token=' + token;
		
		// Commission Calculate: Company / Seller
		callApi(urlCompany, id, 'Compañia');
		callApi(urlSeller, id, 'Vendedor');
	};

	function callApi(url, id, module) {
		var postData = new FormData();
		postData.append('sale_id', id);
		Restangular.one(url).withHttpConfig({transformRequest: angular.identity})
		.customPOST(postData, '', undefined, {'Content-Type': undefined}).then(
			function(response) {
				successCallback(response, 'Los calculos de comisiones fueron realizados.');
			},
			function(response) { 
				errorCallback(response, module);
			}
		);
	}
	
	function successCallback(response, message) {
		var notificacion = {};
		notificacion.title = "Operación exitosa";
		notificacion.body = message;
		$scope.showNotification(notificacion);
		$state.go('^', {}, {reload: true});
	}
	
	function errorCallback(response, module) {
		console.log('errorCallback', response);
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
			notificacion.title = "Error al enviar los dato: " + module;
			notificacion.body = data;
			$scope.showNotification(notificacion);
		}
	}
	
    $scope.showNotification = function(data){
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
}]);
