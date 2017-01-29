angular
.module('ngNova')
.controller('AgenteCtrl', 
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification){
		

		$scope.editProcess = false;
		
		var temp_data = response.data.data;

		$scope.catalog = {};
		$scope.form = {};

		$scope.catalog = temp_data.catalog;

		if(temp_data.agent != undefined){
			$scope.editProcess = true;
			
			for(x in temp_data.agent){
				$scope.form[x] = temp_data.agent[x];
			}

			if(temp_data.agent.subagent=="1"){
				$scope.form.subagent = true;
			}else{
				$scope.form.subagent = false;
			}

			$scope.form.country_id = ""+$scope.form.country_id;
			$scope.form.city_id = ""+$scope.form.city_id;
			$scope.form.province_id = ""+$scope.form.province_id; 
			

			$scope.form.dob = new Date(Date.parse($scope.form.dob));

			if($scope.form.leader!=undefined)
				$scope.form.leader = ""+$scope.form.leader;
		}

		$scope.submit = function(errFiles){

			if($scope.editProcess){
				var	postUrl = "agente/"+temp_data.agent.id;
				console.log(1);
			}else{
				var	postUrl = "agente";
				console.log(2);
			}
			
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
					var notificacion = {};
					notificacion.title = "Formulario enviado";
					notificacion.body = "";
					var msg = response.data.message.Success;
                    for(key in msg){
                        notificacion.body = notificacion.body + key+' '+msg[key];
                    }
					$scope.showNotification(notificacion);
					$state.go('^', {}, {reload: true});
					console.log(response.data);
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
}])