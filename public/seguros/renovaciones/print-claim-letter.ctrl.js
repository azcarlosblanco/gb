angular
.module('ngNova')
.controller('ClaimPrintLetterCtrl', 
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification){

		$scope.form = {};
		$scope.claims = [];

		//var temp_data = JSON.parse(response.data.data);
		$scope.claims = response.data.data.claims;
		$scope.policy_num=response.data.data.policy_num;
		$scope.processID=response.data.data.processID;
		$scope.ntoken = localStorage.getItem(__env.tokenst);

		//email preview data
		$scope.emailcontent = response.data.data.emailcontent;
		$scope.emailto = response.data.data.emailto;
		$scope.emailcc = response.data.data.emailcc;

		$scope.imprimirCarta = function (ref) {
	        console.log('entro');

	        var token = localStorage.getItem(__env.tokenst);
			$scope.form.token=token;

            $http.get(__env.apiUrl+ref, { 
                params: $scope.form, 
                responseType: 'arraybuffer'
                            })
                  .success(function (data) {
                      var file = new Blob([data], {type: 'application/pdf'});
                      var fileURL = URL.createObjectURL(file);
                      window.open(fileURL);
               });
	    }

	    $scope.submit = function () {
	    	var data = new FormData();
			data.append("emailcontent", $scope.emailcontent);
			data.append("emailto", $scope.emailto);
			data.append("emailcc", $scope.emailcc);
			var ref = 'claims/printLetter/'+$scope.processID;
			
    		//peticion http
    		Restangular
	            .one(ref)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customPOST(data, '', undefined, {'Content-Type': undefined})
				.then(
					function successCallback(response) {
						console.log(response);
	                    var notificacion = {};
	                    switch(response.status) {
	                        default:
	                            notificacion.title = "Formulario enviado";
	                            notificacion.content = "El formulario se envió exitosamente";

	                            for(key in response.data.data){
	                                notificacion.content +=key+' : '+response.data[key];
	                            }

	                            webNotification.showNotification(notificacion.title, {
	                                body: response.status + ' - ' + notificacion.content,
	                                icon: 'assets/img/GB.ico',
	                                onClick: function onNotificationClicked() {
	                                    console.log('Notification clicked.');
	                                },
	                                autoClose: 4000 //auto close the notification after 2 seconds (you manually close it via hide function)
	                            }, function onShow(error, hide) {
	                                if (error) {
	                                    console.log('Unable to show notification: ' + error.message);
	                                } else {
	                                    console.log('Notification Shown.');
	                                    setTimeout(function hideNotification() {
	                                        console.log('Hiding notification....');
	                                        hide(); //manually close the notification (or let the autoClose close it)
	                                    }, 5000);
	                                }
	                            });
	                            $state.go('^', {}, {reload: true});
	                            break;
	                    }
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
		};

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