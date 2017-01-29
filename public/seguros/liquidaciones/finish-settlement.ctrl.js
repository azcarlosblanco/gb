angular
.module('ngNova')
.controller('FinishSettlementCtrl', ['response','Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification' ,
                 function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification){

    $scope.message = "Confirme que desea finalizar el proceso de liqudación";
    $scope.title_dialog = "Finalizar Proceso";
    $scope.button_name = "Finalizar";
    $scope.name_function = "submit";
    $scope.ntoken = localStorage.getItem(__env.tokenst);


    $scope.submit = function () {
        var postUrl = response.uri;  
        var data = new FormData();
        
        var token = localStorage.getItem(__env.tokenst);
        data.append("token",token); // API TOKEN

        var urlWithToken = postUrl+"?token="+token;
        
        Restangular
        .one(urlWithToken)
        .withHttpConfig({transformRequest: angular.identity})
        .customPOST(data, '', undefined, {'Content-Type': undefined})
        .then(
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
