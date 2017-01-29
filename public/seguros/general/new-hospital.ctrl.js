angular
.module('ngNova')
.controller('NewHospitalCtrl', 
		['Restangular','$rootScope', '$scope','hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification', 'myShareService',
		function(Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification, myShareService){
		
		$scope.catalog = {};
		$scope.form = {};
        $scope.hospitalObject = {} 

        $scope.closeAddHospital = function(){
        	myShareService.broadcastCancelHospital();
        }

        $scope.transmitHospital = function(hospitalObject) {
        	myShareService.prepForBroadcastHospital(hospitalObject);
	    };
	        
		$scope.submit = function(errFiles){
            var	postUrl = "hospital";
			var token = localStorage.getItem(__env.tokenst);
			var urlWithToken = __env.apiUrl+postUrl+"?token="+token;
			var data = new FormData();			
			for(x in $scope.form){
				data.append(x,$scope.form[x]);
			}			
			$http.post(
				urlWithToken,
				data, 
				{
					transformRequest: angular.identity,
					headers: {'Content-Type': undefined}
				}
			)
			.then(
				function successCallback(response) {
					var id = response.data.data.id;
					var hospital = {"id":id,
									"address":$scope.form.address,
									"name":$scope.form.name,
									"showmodal":false}
					notificacion = {'title':'Creación exitosa',
									'body':'El Hospital fue creado exitosamente'}
				    $scope.closeAddHospital()
					$scope.showNotification(notificacion);
					$scope.transmitHospital(hospital);
				},
				function errorCallback(response) {
					var notificacion = {};
                    var data = "";
                    if(response.data.message == undefined){
                        for(key in response.data){
                            data = data + key+' : '+response.data[key].join(',')+"\n";
                        }
                    }else{
                        errors = response.data.message.Error;
                        for(key in errors){
                            data = data + key+' '+errors[key];
                        }
                        dataErrors = response.data.data;    
                        for(key in dataErrors){
                            data = data + key+' '+dataErrors[key];
                        }
                    }
                    notificacion.title = "Error al enviar el formulario";
                    notificacion.body = data;
					$scope.showNotification(notificacion);
				}
			);
		}
		
		$scope.showNotification = function(data){
			webNotification.showNotification(data.title, {
	            body: data.body,
	            icon: 'assets/img/GB.ico',
	            onClick: function onNotificationClicked() {
	                console.log('Notification clicked.');
	            },
	            autotoClose: 4000 //auto close the notification after 4 seconds (you can manually close it via hide function)
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
}])