angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('NuevoReclamoCtrl',
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification){
		$scope.f = [];
		$scope.form = [];
		$scope.categories = {};
		$scope.cat_names = {};
		$scope.policies = {};
		$scope.registers = [];
		$scope.uregisters = [];
		$scope.errorMsg = {};
		$scope.process_id = null;
		$scope.cron_id = null;
		$scope.showSubmit = true;
		$scope.keyStorage = "newClaims";
		$scope.cat_placeholder = [];


		//data for catalog
		var temp_data = response.data.data;
		for (x in temp_data.categories) {
			$scope.categories[temp_data.categories[x]['id']]=temp_data.categories[x]['description'];
			$scope.cat_names[temp_data.categories[x]['id']]=temp_data.categories[x]['name'];
			if(temp_data.categories[x]['name']=="claim_invoice"){
				$scope.cat_placeholder[temp_data.categories[x]['id']] = "# Factura";
			}else if(temp_data.categories[x]['name']=="claim_laborder"){
				$scope.cat_placeholder[temp_data.categories[x]['id']] = "# Orden";
			}else if(temp_data.categories[x]['name']=="claim_labresult"){
				$scope.cat_placeholder[temp_data.categories[x]['id']] = "# Resultado";
			}
		}
		$scope.currency = temp_data.currency;
		for (x in temp_data.policies) {
			$scope.policies[x]=temp_data.policies[x];
		}
		//////

		//function call when we want to add other file
		$scope.addRegister = function () {
			$scope.registers.push({});
			var current_index = $scope.form.length;
			console.log(current_index);
			$scope.form[current_index] = {};
			$scope.form[current_index]['currency'] = "1";
	    };


	    //files that have been already uploaded
		$scope.view=false;
		if(temp_data.process_id!==undefined){
			$scope.view=true;
			$scope.process_state=temp_data.state;

			if(temp_data.policy_number !== undefined){
				$scope.policies[temp_data.policy_id]=temp_data.policy_number;
				$scope.form['policy_id']=temp_data.policy_id+"";
			}

			$scope.process_id=temp_data.process_id;

			if(temp_data.files !== undefined){
				var index=0;
				for(x in temp_data.files){
					$scope.form[index]={}
					$scope.form[index]['name']=temp_data.files[x].name;
					$scope.form[index]['description']=temp_data.files[x].description;
					$scope.form[index]['category']=temp_data.files[x].category;
					if($scope.cat_names[$scope.form[index]['category']]=="claim_invoice"){
						$scope.form[index]['currency']=temp_data.files[x].currency;
						$scope.form[index]['amount']=temp_data.files[x].amount;
					}
					$scope.form[index]['fid']=temp_data.files[x].fid;
					$scope.form[index]['cron_id']=temp_data.files[x].cron_id;
					$scope.form[index]['ts']=x;
					var error=localStorage.getItem($scope.keyStorage+'/'+$scope.process_id
													+"/"+x);

					$scope.form[index]['status']=1;
					if(error==1){
						if($scope.process_state==0){ //pending
							$scope.form[index]['status']=0; //file uploading
						}
						$scope.form[index]['status']=2; //error
						$scope.process_state=2;
					}
					index++;
					$scope.uregisters.push({});
				}
			}
			if($scope.process_state!=2){
				$scope.showSubmit = false;
			}
		}else{
			$scope.actionName='submit';
			$scope.addRegister();
		}
		/////

	    $scope.deleteRegister = function (item) {
	    	var index=$scope.registers.indexOf(item);
	    	$scope.form.splice(index,1);
	    	$scope.f.splice(index,1);
	    	$scope.registers.splice(index,1);
	    };

		$scope.registerFile = function(file,item){
			if($scope.view){
				var index=$scope.uregisters.indexOf(item);
			}else{
				var index=$scope.registers.indexOf(item);
			}

			if($scope.f[index] === 'undefined'){
				$scope.f.push(file);
				$scope.form[index]['name']=file.name;
			}else{
				$scope.f[index]=file;x
				$scope.form[index]['name']=file.name;
			}
			console.log(index);
		};

	    $scope.submit = function (errFiles) {
	    	if($scope.view && $scope.process_state==2){
	    		$scope.resubmit(errFiles);
	    	}else{
		    	var key = [1];
		    	var i=0;
		    	var dataForm=$scope.form;

		    	var name_files = {};
		    	var ts=Math.floor(Date.now() / 1000);
		    	for(x in $scope.f){
		    		ts++;
		    		$scope.form[x]['ts']=ts;
		    		name_files[ts]={
		    						'name':$scope.f[x].name,
		    						'description': dataForm[x]['description'],
		    						'category':    dataForm[x]['category'],
		    						'currency':    dataForm[x]['currency'],
		    						'amount':      dataForm[x]['amount'],
		    						};
				}

				if ($scope.form) {
					notification = {};
					if($scope.f.length == 0){
						notification.body = "Debe subir al menos un archivo";
					}
					if($scope.f.length != $scope.form.length){
						notification.body = "Debe subir los archivos de los registros creados";
					}
					if(notification.body!=undefined){
						notification.title = "Error ";
	        			novaNotification.showNotification(notification);
	        			return;
					}

					var data = new FormData();

					var token = localStorage.getItem(__env.tokenst);
            		data.append("token",token); // API TOKEN

					data.append("pid", $scope.form.policy_id);
					data.append("num_files", $scope.f.length);
					data.append("files", JSON.stringify(name_files));

					var	postUrl = "reception/newClaims";
					var urlWithToken = postUrl+"?token="+token;

					Restangular
			        .one(urlWithToken)
			        .withHttpConfig({transformRequest: angular.identity})
			        .customPOST(data, '', undefined, {'Content-Type': undefined})
			        .then(
						function successCallback(response) {
							console.log(response);
							var process_id = response.data.data.process_id;

							for(x in $scope.form){
								$scope.form[x]['cron_id']=response.data.data.cron_id;
							}
							//send the files
							$state.go('^', {}, {reload: true});
							var urlUploadFile = __env.apiUrl+'reception/newClaims/'+process_id+'/file';
							$rootScope.uploadFiles($scope.f,
													errFiles,
													process_id,
													$scope.form,
													urlUploadFile,
													$scope.keyStorage);
						},
						function errorCallback(response) {
							var notification = {};
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
		                    notification.title = "Error al enviar el formulario";
		                    notification.body = data;
	        				novaNotification.showNotification(notification);
						}
					);
				}
			}
		};

		$scope.resubmit = function(errFiles){
			//hacer las paetciones para resubir los archivos que fallaron
			$state.go('^', {}, {reload: true});
			var urlUploadFile = __env.apiUrl+'reception/newClaims/'+$scope.process_id+'/file';
			$rootScope.uploadFiles($scope.f,
									errFiles,
									$scope.process_id,
									$scope.form,
									urlUploadFile,
									$scope.keyStorage);
		};

}])
.directive('newRegister', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/reclamos/registro-nuevo-reclamo.html'
    };
})
.directive('viewRegister', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/reclamos/view-file-reclamo.html'
    };
});
