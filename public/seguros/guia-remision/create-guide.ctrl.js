angular
.module('ngNova')
.controller('CreateGuideCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification', 
	function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification){

	console.log(response); //      //      //      //      //      //      //      //      //
	var temp_data = response.data.data.data_fields;
	console.log(temp_data); //      //      //      //      //      //      //      //      //


	var process_ID = response.data.data.process_ID;

	$scope.carrier_opt = response.data.data.display.carrier_opt;
	$scope.form = temp_data;
	//$scope.form['role'] = 1;

	$scope.focused = [];
	$scope.filefields = [];

	$scope.myFunction = function (ref) {
        console.log('entro');

        var token = localStorage.getItem(__env.tokenst);
        $scope.form.token=token; //token
        
        if($scope.form.carrier_id === undefined){
            $window.alert('Seleccione mensajero');
        }else{
        	var i=0;

			$scope.form['groupDocuments_text']=JSON.stringify($scope.form['groupDocuments']);

            $http.get(ref, { 
                params: $scope.form, 
                responseType: 'arraybuffer'
                            })
                  .success(function (data) {
                      var file = new Blob([data], {type: 'application/pdf'});
                      var fileURL = URL.createObjectURL(file);
                      window.open(fileURL);
               });
        }
    }

	$scope.submit = function () {
		if ($scope.form) {
			
			if($scope.filefields.length==0){
				alert("Debe subir la guia firmada");
				return;
			}

			var data = new FormData();
			
			var token = localStorage.getItem(__env.tokenst);
            data.append("token",token); // API TOKEN

			for (var i in $scope.form) {
				if(i == "groupDocuments"){
					data.append("groupDocuments_text",JSON.stringify($scope.form[i]));
				}else{
					data.append(i,$scope.form[i]);
				}
			}

			if ($scope.filefields.length >0 ) {
				for (var i in $scope.filefields) {
					data.append("filefields[]",$scope.filefields[i]);
				}
			}
			

			var	postUrl = "reception/guiaremision";
			var urlWithToken = postUrl+"?token="+token;

			Restangular
            .one(urlWithToken)
            .withHttpConfig({transformRequest: angular.identity})
            .customPOST(data, '', undefined, {'Content-Type': undefined})
			.then(
				function successCallback(response) {
					var notificacion = {};
					notificacion.title = "El formulario se envió con éxito";
                    notificacion.body = "OK";
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
.directive('dispatchDoc', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/guia-remision/guide-items.html'
    };
});