angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('CancelProcedureCtrl', ['response','Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'novaNotification' ,
                 function(response, Restangular, $scope, hotkeys, $state, $http, __env, novaNotification){

    $scope.message = response.message;
    $scope.id = response.id;

    console.log(response);

	$scope.cancelProcedure = function () {
            var url = response.uri + "/" + response.id ;  


            Restangular
            .one(url)
            .withHttpConfig({transformRequest: angular.identity})
            .customDELETE(undefined, {'reason':$scope.reason}, {'Content-Type': undefined})
            .then(
                function (response) {
                    successCallback(response); 
                },
                function (response) { 
                    errorCallback(response); 
            });
			
            function successCallback(response) {
                $scope.acceptCancel=false;
				console.log(response);
                var notificacion = {};
            
                notificacion.title = "Trámite ha sido cancelado";

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
                $scope.acceptCancel=false;
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

}])
