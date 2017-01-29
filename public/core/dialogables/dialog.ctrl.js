angular
.module('ngNova')
.controller('DialogCtrl', ['response','Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification' ,
                 function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification){

    $scope.message = response.message;

	$scope.delete = function () {
            var token = localStorage.getItem(__env.tokenst);
            var url = response.uri + "/" + response.id + "?token=" + token;  

            Restangular
            .one(url)
            .withHttpConfig({transformRequest: angular.identity})
            .customDELETE(undefined, undefined, {'Content-Type': undefined})
            .then(
                function (response) {
                    successCallback(response); 
                },
                function (response) { 
                    errorCallback(response); 
            });
			
            function successCallback(response) {
					console.log(response);
                    var notificacion = {};
                
                    notificacion.title = "Operación exitosa";

                    var msg = response.data.message.Success;
                    var data = "";
                    for(key in msg){
                        data = data + key+' '+msg[key];
                    }

                    notificacion.body = data;
                    $scope.showNotification(notificacion);
                    $state.go('^', {}, {reload: true});
            }
            function errorCallback(response) {
                var notificacion = {};
                var data = "";
                if(response.data.message == undefined){
                    for(key in response.data){
                        data = data + key+' : '+response.data[key].join(',')+"\n";
                    }
                }else{
                    errors = response.data.message.Error;
                    console.log(errors);
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

}])
