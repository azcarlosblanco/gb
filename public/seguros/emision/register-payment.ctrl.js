angular
.module('ngNova')
.controller('NewPolicyRegisterPaymentCtrl', ['response','Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification', 'Upload', '$timeout',
	function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification, Upload, $timeout){

	console.log(response); 

	var process_ID = response.data.data.process_ID;

	$scope.confirmSendApp = false;
	$scope.catalog = response.data.data.catalog;
	$scope.form = {}
	$scope.focused = [];
	$scope.tmp_data = response.data.data.payment_data

	$scope.attachments = [];

	$scope.descriptionFiles={
				"cheque":"Cheque",
				"desposit":"Papeleta Depósito",
				"transfer":"Papeleta Transferencia",
				"creditcard":"Formulario Autorización",
					}

	if($scope.tmp_data != undefined){
		for(x in $scope.tmp_data){
			if(x!='files'){
				$scope.form[x] = ""+$scope.tmp_data[x];
			}else{
				var uploadFiles = $scope.tmp_data[x];
				for(y in uploadFiles){
					var file = uploadFiles[y];
					console.log(file);
					$scope.attachments.push(file);
				}
			}
		}
	}

	if($scope.attachments.length==0){
		var attachment = {} ;
		attachment.description = $scope.descriptionFiles[$scope.catalog.payment_name[$scope.form.payment_method_id]];
		$scope.attachments.push(attachment);
	}

	$scope.form.value = $scope.catalog.costs[0]['total'];
	$scope.ntoken = localStorage.getItem(__env.tokenst);

	$scope.addFileLine = function(file,item){
        $scope.attachments.push({});
    };

	$scope.registerFile= function(file,item){
        item.file = file;
        item.ts = Math.floor(Date.now() / 1000);
        item.name = file.name;
        $scope.uploadFile(item,$scope.errFiles);
    };

    $scope.deleteFile = function(item){
    	var index=$scope.attachments.indexOf(item);
    	if(index > -1){
    		if($scope.attachments[index]['file_id']!=undefined){
    			//we upload a file here, we have to delete the file from the server
    			//delete file in the back
				var	url = "emission/newPolicy/registerPayment/"+process_ID+"/file";

				Restangular
	            .one(url)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customDELETE(undefined, {"file_id":$scope.attachments[index]['file_id']}, {'Content-Type': undefined})
	            .then(
	                function (response) {
	                    $scope.attachments.splice(index,1);
	                    notificacion.title = "Se elimino el archivo del servidor";
	                    notificacion.body = "Archivo "+$scope.attachments[index]['name'];
						$scope.showNotification(notificacion);
	                },
	                function (response) { 
	                    var notificacion = {};
	                    notificacion.title = "Error al eliminar archivo del servidor";
	                    notificacion.body = "Archivo "+$scope.attachments[index]['name'];
						$scope.showNotification(notificacion);
	            	}
	            );
    		}else{
    			$scope.attachments.splice(index,1);
    		}
    	}
    }	

    $scope.uploadFile = function(item, errFiles) {
        var token = localStorage.getItem(__env.tokenst);
        var postUrl = "emission/newPolicy/registerPayment/"+process_ID+"/file";
        var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

        if(item['description']==undefined){
        	var notificacion = {};
            notificacion.title = "Debe llenar el campo descripcion para subir el archivo";
            notificacion.body = "Archivo "+item.name;
            item={};
			$scope.showNotification(notificacion);
        }

       	$scope.errFile = errFiles && errFiles[0];
       	file = item['file'];
       	if (file) {
           file.upload = Upload.upload({
               url: urlWithToken,
               data: item
           });
           file.upload.then(function (response) {
               $timeout(function () {
                   file.progress = 0;
                   file.error = 0;
                   item.file_id = response.data.data.file_id;
                   $scope.attachments.push({});
               });
           }, function (response) {
               	if (response.status > 0){
                   	file.error = 1
                   	file.progress = 0;
                   	notificacion.title = "Error a subir el archivo al servidor";
	                notificacion.body = "Archivo "+$scope.attachments[index]['name'];
					$scope.showNotification(notificacion);
               	}
           }, function (evt) {
               file.progress = Math.min(100, parseInt(100.0 * 
                                        evt.loaded / evt.total));
           });
       	}   
    }

	$scope.submit = function(data){
		if ($scope.form) {

			if(Upload.isUploadInProgress()){
				$scope.checking = false;
				alert("Espere a que todos los archivos terminen de subirse");
				return;
			}

			$scope.confirmSendApp = false;
			var data = new FormData();
			var token = localStorage.getItem(__env.tokenst);
            for(x in $scope.form){
            	data.append(x,$scope.form[x]);
            }

            var files = [];
            for(x in $scope.attachments){
            	if($scope.attachments[x]['file_id']!=undefined){
            		var file = {}
	            	file.file_id = $scope.attachments[x]['file_id'];
	            	file.name = $scope.attachments[x]['name'];
	            	file.description = $scope.attachments[x]['description'];
	            	files.push(file);
            	}
            }

            data.append("files",JSON.stringify(files));

			var	postUrl = "emission/newPolicy/registerPayment/"+process_ID;

			Restangular
            .one(postUrl)
            .withHttpConfig({transformRequest: angular.identity})
            .customPOST(data, '', undefined, {'Content-Type': undefined})
			.then(
				function successCallback(response) {
					console.log(response);
					var notificacion = {};
					notificacion.title = "La respuesta dle cliente fue registrada";
					notificacion.body = "";
					msg = response.data.message.Success;
                    for(key in msg){
                        notificacion.body = notificacion.body + key+' '+msg[key]+"\n";
                    }
                    $scope.showNotification(notificacion);
                    $state.go('^', {}, {reload: true});
				},
				function errorCallback(response) {
					$scope.checking = false;
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
	        })
	    }

}])
;