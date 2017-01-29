angular
.module('ngNova')
.controller('WizPaso7Ctrl', ['response','Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification', 'Upload', '$timeout', 'myEmailShareService',
	function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification, Upload, $timeout, myEmailShareService){

	$scope.confirmSendApp = false;
	$scope.data = response.data.data;
	var process_ID = response.data.data.process_ID;

	var temp_data = response.data.data.temp_data ? JSON.parse(response.data.data.temp_data) : {};

	//wizard_steps obj used to create the links in the top of modal
	$scope.current_step = 7;
	$scope.wizard_steps = [];
	$scope.max_step=parseInt(temp_data.max_step);
	var step_data = {"step":1,
					"link":"^.paso-1",
					"icon":"glyphicon-pencil",
					"label":"Solicitante"};
	$scope.wizard_steps.push(step_data);
	//plan
	var step_data2 = {}
	step_data2['icon']="glyphicon-cog";
	step_data2['label']="Plan";
	step_data2['step']=2;
	step_data2['link']="^.paso-2";
	$scope.wizard_steps.push(step_data2);
	//dependiente
	var step_data3 = {}
	step_data3['icon']="glyphicon-user";
	step_data3['label']="Dependientes";
	step_data3['step']=3;
	step_data3['link']="^.paso-3";
	$scope.wizard_steps.push(step_data3);
	//Médico
	var step_data4 = {}
	step_data4['icon']="glyphicon-plus";
	step_data4['label']="Médico";
	step_data4['step']=4;
	step_data4['link']="^.paso-4";
	$scope.wizard_steps.push(step_data4);
	//Pago
	var step_data5 = {}
	step_data5['icon']="glyphicon-usd";
	step_data5['label']="Pago";
	step_data5['step']=5;
	step_data5['link']="^.paso-5";
	$scope.wizard_steps.push(step_data5);
	//Inicio Cobertura
	var step_data6 = {}
	step_data6['icon']="glyphicon-ok-circle";
	step_data6['label']="Inicio Cobertura";
	step_data6['step']=6;
	step_data6['link']="^.paso-6";
	$scope.wizard_steps.push(step_data6);
	//Documentos
	var step_data7 = {}
	step_data7['icon']="glyphicon-file";
	step_data7['label']="Documentos";
	step_data7['step']=7;
	step_data7['link']="^.paso-7";
	$scope.wizard_steps.push(step_data7);
	//end wizard_steps

	
	$scope.focused = [];
	
	$scope.form = {};
	$scope.form.files = [];
	$scope.form.errFiles = [];
	$scope.registers = [];
	$scope.viewprocess=false;

	$scope.ntoken = localStorage.getItem(__env.tokenst);

	/**variables used to show email**/
	$scope.emailObject = {};
	$scope.emailObject.urlgetdata = "emission/newPolicy/uploadPolicyRequest/"+process_ID+"/emaildata";
	$scope.emailObject.firstTimeEmail = true;
	$scope.emailObject.showPreviewEmail = false;

	if($scope.data.files != undefined){
		var files = $scope.data.files;
		for(ts in files){
			//check if the file has failed to allow him re-upload the file
			var ferror=localStorage.getItem($scope.keyStorage+'/'+$scope.process_id
													+"/"+ts);
			if(ferror==1){
				files[ts].error =1;
			}
			files[ts].ts=ts;
			files[ts].fid=files[ts].id;
			$scope.registers.push({});
			$scope.form.files.push(files[ts]);
		}
	}

	//get file that are in process of been updated or have failed
	$scope.addRegister = function () {
		$scope.registers.push({});
		$scope.form.files.push({});
    };

    $scope.deleteRegister = function (item) {
    	var index=$scope.registers.indexOf(item);
    	console.log($scope.form.files[index]);
    	if($scope.form.files[index]['id']!=undefined){
    		//delete file in the back
    		var token = localStorage.getItem(__env.tokenst);
			var	postUrl = "emission/newPolicy/uploadPolicyRequest/form/"+process_ID+"/step/7";
			var urlWithToken = postUrl+"?token="+token;
			
			var data = new FormData();
			data.append("deleteFile",true);
			data.append("fid",$scope.form.files[index]['id']);
			
			Restangular
            .one(urlWithToken)
            .withHttpConfig({transformRequest: angular.identity})
            .customPOST(data, '', undefined, {'Content-Type': undefined})
			.then(
				function successCallback(response) {
					$scope.form.files.splice(index,1);
    				$scope.registers.splice(index,1);
				},
				function errorCallback(response) {
					var notificacion = {};
                    notificacion.title = "Error al eliminar archivo del servidor";
                    notificacion.body = "Archivo "+$scope.form.files[index]['name'];
					$scope.showNotification(notificacion);
				}
			);
    	}else{
    		$scope.form.files.splice(index,1);
    		$scope.registers.splice(index,1);
    	}
    };

	$scope.registerFile = function(file,item){
		var index=$scope.registers.indexOf(item);
		if($scope.form.files[index]==undefined){
			$scope.form.files[index]={};
		}
		if($scope.form.files[index]['ts']==undefined){
			var ts=Math.floor(Date.now() / 1000);
			$scope.form.files[index]['ts'] = ts;
		}
		$scope.form.files[index]['file']=file;
		$scope.form.files[index]['name']=file.name;
		$scope.form.files[index]['progress']=0;
		$scope.uploadFile($scope.form.files[index],$scope.form.errFiles);
	};

	$scope.uploadFile = function(register_file, errFiles) {
		var token = localStorage.getItem(__env.tokenst);
		var	postUrl = "emission/newPolicy/uploadPolicyRequest/form/"+process_ID+"/step/7";
		var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

        $scope.errFile = errFiles && errFiles[0];
        file = register_file['file'];
        if (file) {
            file.upload = Upload.upload({
                url: urlWithToken,
                data: register_file
            });
            file.upload.then(function (response) {
                $timeout(function () {
                    file.progress = 0;
                    file.error = 0;
                    $reg = $scope.searchByTSFile(response.data.data.ts);
                    $reg['id']  = response.data.data.id;
                    $reg['fid'] = response.data.data.id;
                });
            }, function (response) {
                if (response.status > 0){
                    file.error = 1
                    file.progress = 0;
                }
            }, function (evt) {
                file.progress = Math.min(100, parseInt(100.0 * 
                                         evt.loaded / evt.total));
            });
        }   
    }

    $scope.searchByTSFile = function (ts){
    	for(x in $scope.form.files){
    		if(ts == $scope.form.files[x]['ts']){
    			return $scope.form.files[x];
    		}
    	}
    }

	$scope.next = function () {
		if(Upload.isUploadInProgress()){
			$scope.checking = false;
			alert("Espere a que todos los archivos terminen de subirse");
			return;
		}else{
			$scope.confirmSendApp = true;
		}
	};

	$scope.sendApplicationBD = function(data){
		if ($scope.form) {
			$scope.confirmSendApp = false;
			var data = new FormData();

			var token = localStorage.getItem(__env.tokenst);
			var	postUrl = "emission/newPolicy/uploadPolicyRequest/"+process_ID;
			var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

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
					console.log(response);
					$state.go('^.^',{},{reload: true});
				},
				function errorCallback(response) {
					$scope.checking = false;
					var notificacion = {};
                    var data = $scope.readResponse(response);
                    notificacion.title = "Error al enviar el formulario";
                    notificacion.body = data;
					$scope.showNotification(notificacion);
				}
			);
		}
	};

	$scope.readResponse = function(response_data){
		var data = "";
		if(response_data.data.message == undefined){
            for(key in response_data.data){
                data = data + key+' : '+response.data[key].join(',')+"\n";
            }
        }else{
            errors = response_data.data.message.Error;
            for(key in errors){
                data = data + key+' '+errors[key];
            }
            dataErrors = response_data.data.data;    
            for(key in dataErrors){
                data = data + key+' '+dataErrors[key];
            }
        }
        return data;
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
        })
    }

	$scope.previewEmail = function(){
		$scope.emailObject.showEmailConfirm = true
		console.log("function preview email");
		if($scope.emailObject.firstTimeEmail == true){
			Restangular
	        .one($scope.emailObject.urlgetdata)
	        .get('')
	        .then(
	            function successCallback(response) {
	            	$scope.emailObject.firstTimeEmail = false;
	            	$scope.emailObject.showPreviewEmail = true;
	            	var data = response.data.data;
	                $scope.emailObject.emailto = data.to;
	                $scope.emailObject.emailcc = data.cc;
	                $scope.emailObject.emailcontent = data.content;
	                $scope.emailObject.eamilsubject = data.subject;
	                $scope.emailObject.attachlist = [];
	                $scope.emailObject.attachments = [];
	                for(x in data.attachments){
	                	var file = {};
	                	file.name = data.attachments[x].name;
	                	file.id = data.attachments[x].id;
	                	$scope.emailObject.attachments.push(file);
	                	$scope.emailObject.attachlist.push(file.id);
	                }
	                for(x in data.internalattachments){
	                	var file = {};
	                	file.name = data.attachments[x].name;
	                	file.id = data.attachments[x].id;
	                	$scope.emailObject.attachments.push(file);
	                	$scope.emailObject.attachlist.push(file.id);
	                }
	                $scope.transmistemailInfo($scope.emailObject);
	            },
	            function errorCallback(response) {
	            	console.log(response.data);
	            }
        	);
		}else{
			$scope.emailObject.showPreviewEmail = true;
			$scope.transmistemailInfo($scope.emailObject);
		}
	}

	$scope.transmistemailInfo = function(emailObject) {
		console.log(myEmailShareService.emailObject);
        myEmailShareService.prepForBroadcast(emailObject);
    };
        
    $scope.$on('handleEmailBroadcast', function() {
        $scope.emailObject = myEmailShareService.emailObject;
    });

    $scope.$on('handleCloseEmailBroadcast', function() {
        $scope.emailObject.showPreviewEmail = false;
    });

}])
.directive('filesUpload', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/emision/wizard/upload-file.view.html'
    };
});