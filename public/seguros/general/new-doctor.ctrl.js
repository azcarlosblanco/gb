angular
.module('ngNova')
.controller('NewDoctorCtrl', 
		['Restangular','$rootScope', '$scope','hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification', 'myShareService',
		function(Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification, myShareService){
		

		$scope.catalog = {};
		$scope.form = {};
        $scope.doctorObject = {} 

        $scope.typeNum = [
                          'Cedula',
                          'Ruc',
                          'Pasaporte'
  ];

        
        $scope.closeAddDoctor = function(){
        	myShareService.broadcastCancelDoctor();
        }

        $scope.transmitDoctor = function(doctorObject) {
        	myShareService.prepForBroadcastDoctor(doctorObject);
	    };
	        
		$scope.submit = function(errFiles){
            var	postUrl = "doctor";
			var token = localStorage.getItem(__env.tokenst);
			var urlWithToken = __env.apiUrl+postUrl+"?token="+token;
			var data = new FormData();		
			for(x in $scope.form){
				data.append(x,$scope.form[x]);
				if ($scope.form.pid_type==0){
					data.append("pid_type",1);
				} 
				if ($scope.form.pid_type==1){
					data.append("pid_type",2)
				} 
				if ($scope.form.pid_type==2){
					data.append("pid_type",3)
				} 
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
					var doctor = {"id":id,
									"name":$scope.form.name,
									"pid_type":$scope.form.pid_type,
									"pid_num":$scope.form.pid_num,
									"showmodal":false}
					notificacion = {'title':'Creación exitosa',
									'body':'El Doctor fue creado exitosamente'}
				    $scope.closeAddDoctor();
					$scope.showNotification(notificacion);
					$scope.transmitDoctor(doctor);
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