angular
.module('ngNova')
.controller('PendingLiquidationCtrl', 
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification){ 
			
			$scope.claim = response.data.data.claim;

			$scope.settlements = response.data.data.settlements;
			$scope.not_associated = response.data.data.not_associated;
			$scope.uploaded = response.data.data.uploaded;
			$scope.idForPost = response.data.route.substring(25);
			$scope.adeductibles = response.data.data.affiliate_deductibles;
			$scope.gdeductibles = response.data.data.global_deductibles;
			$scope.pdeductibles = response.data.data.policy_deductibles;
			console.log("not ass", $scope.not_associated);

			$scope.currentSettlementEdit = null;
			$scope.form = {};
			$scope.ntoken = localStorage.getItem(__env.tokenst);
			
			$scope.noLiqSelected = function (item) {
				$scope.resetLiqFields();
				$scope.form.obj = item;
				$scope.form.cfid = item.id;
				$scope.amountLiqSelected = item.amount;
				$scope.form.amount = $scope.amountLiqSelected;
			}
			
			$scope.setFormData = function(){
				$scope.amountLiqSelected = 0;

				var token = localStorage.getItem(__env.tokenst); // API TOKEN

				var endpoint = 'settlements/registerForm/'+$scope.idForPost
									+"?token="+token;
          		$http.get(
					__env.apiUrl+endpoint,
					{
						transformRequest: angular.identity,
						headers: {'Content-Type': undefined}
					}
				).then(
					function successCallback(response) {
						$scope.claim = response.data.data.claim;
						$scope.settlements = response.data.data.settlements;
						$scope.not_associated = response.data.data.not_associated;
						$scope.uploaded = response.data.data.uploaded;
						$scope.adeductibles = response.data.data.affiliate_deductibles;
						$scope.gdeductibles = response.data.data.global_deductibles;
						$scope.pdeductibles = response.data.data.policy_deductibles;
						$scope.resetLiqFields();
						$scope.settlements.forEach(function (elem) {
							elem.disabled = true;
							console.log(elem);
						});
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

			$scope.addFiles = false;
			$scope.addNLFiles = false;
			
			$scope.settlements.forEach(function (elem) {
				elem.disabled = true;
				console.log(elem);
			});

			$scope.sumLiqTotal = function () {
				$scope.sumLiquidadas = 0;
				$scope.settlements.forEach(function (elem) {
					$scope.sumLiquidadas += elem.total;
				});
				return $scope.sumLiquidadas;
			}

			$scope.resetLiqFields = function() {
				$scope.form = {};
				$scope.form.files = [];
				$scope.form.amount = 0.00;
				$scope.form.uncovered = 0.00;
				$scope.form.dscto = 0.00;
				$scope.form.deducible = 0.00;
				$scope.form.coaseguro = 0.00;
			}

			$scope.resetLiqFields();
			$scope.$watch($scope.sumLiqTotal);

			$scope.addLiquidation = function() {
				//check user has associated files to the claims
				if($scope.form.files.length == 0){
					alert("Debe asociar archivos a la liquidación");
					return;
				}

				var data = new FormData();
				var refund = $scope.form.amount - $scope.form.uncovered - $scope.form.coaseguro - $scope.form.deducible - $scope.form.dscto;
	            data.append('cfid', $scope.form.cfid);
	            data.append('amount', $scope.form.amount);
	            data.append('uncovered', $scope.form.uncovered);
	            data.append('dscto', $scope.form.dscto);
	            data.append('deducible', $scope.form.deducible);
	            data.append('coaseguro', $scope.form.coaseguro);
	            data.append('refund', refund);
	            data.append('files', JSON.stringify($scope.form.files));

	            var token = localStorage.getItem(__env.tokenst); // API TOKEN
	            data.append('token',token); // API TOKEN

	            $scope.submitRegister(data);
			}

			$scope.submitLiqChanges = function (liq) {
				
				if(liq.editfiles == 0){
					alert("Debe asociar archivos a la liquidación");
					return;
				}

				var data = new FormData();
	            
	            var refund = liq.amount - liq.settlement.uncovered_value - liq.settlement.descuento - liq.settlement.expected_deduct - liq.settlement.coaseguro
	            data.append('cfid', liq.id);
	            data.append('amount', liq.amount);
	            data.append('uncovered', liq.settlement.uncovered_value);
	            data.append('dscto', liq.settlement.descuento);
	            data.append('deducible', liq.settlement.expected_deduct);
	            data.append('coaseguro', liq.settlement.coaseguro);
	            data.append('refund', refund);
	            data.append('files', JSON.stringify(liq.editfiles));

	            var token = localStorage.getItem(__env.tokenst); // API TOKEN
	            data.append('token',token); // API TOKEN

	            $scope.submitRegister(data);
			}

			$scope.submitRegister = function(content) {
				//$scope.form.total = (($scope.form.monto + $scope.form.noCubierto + $scope.form.coasegurado) - ($scope.form.monto * $scope.form.dscto) / 100);
				//$scope.form.id = $scope.counterId;
				
				var token = localStorage.getItem(__env.tokenst); // API TOKEN
				var postUrl = "settlements/registerForm/"+$scope.idForPost;
        		var urlWithToken = postUrl+"?token="+token;

				if (content) {
			        Restangular
			        .one(urlWithToken)
			        .withHttpConfig({transformRequest: angular.identity})
			        .customPOST(content, undefined, undefined, {'Content-Type': undefined})
			        .then(
			            function successCallback(response) {
			            	$scope.setFormData();
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
			}

			$scope.editLiquidation = function (id) {
				console.log("edit settlement");
				$scope.settlements.filter(function (elem) {
					if (elem.id == id) {
						$scope.currentSettlementEdit = elem; 
						$scope.currentSettlementEdit.editfiles = [];
						var lfiles = $scope.currentSettlementEdit.settlement.files;
						for( x in lfiles){
							$scope.currentSettlementEdit.editfiles.push(lfiles[x].id);
						}
						elem.disabled = false;
					} else {
						elem.disabled = true;
					}
					return elem.id == id;
				});
			}

			$scope.addFilesNLLiquidation = function (){
				$scope.addNLFiles = true;
			}

			$scope.selectedFilesNLiq = function (id) {
				console.log(id, $scope.uploaded);
				$scope.uploaded.filter(function (elem) {
					if (elem.id == id) {
						elem.selected = !elem.selected;
						if (elem.selected && $scope.form.files.indexOf(id)) {
							$scope.form.files.push(id);
						}
						if (elem.selected == false && $scope.form.files.indexOf(id)) {
							$scope.form.files.splice($scope.form.files.indexOf(id), 1);
						}
					} 
					return elem.id;
				});
			}

			$scope.cancelSelFilesNLiq = function () {
				$scope.addNLFiles = false;
				$scope.uploaded.forEach(function (elem) {
					if ($scope.form.files.indexOf(elem.id) ) {
						elem.selected = false;
						$scope.form.files.splice($scope.form.files.indexOf(elem.id), 1);
					}	
				});
				console.log($scope.form.files);
			}

			$scope.addNLFilesSuccess = function () {
				if($scope.form.files.length != 0){
					$scope.addNLFiles = false;
				}else{
					alert("Debe seleccionar al menos un archivo");
				}
			}

			$scope.addFilesLiquidation = function (item) {
				$scope.addFiles = true; 
				$scope.currentFiles = [];
				var indexs = $scope.settlements.indexOf(item);
				var lfiles = $scope.settlements[indexs].settlement.files;
				var index = 0;
				for( x in $scope.uploaded){
					$scope.currentFiles[index] = {};
					$scope.currentFiles[index].id = $scope.uploaded[x].id;
					$scope.currentFiles[index].name = $scope.uploaded[x].name;
					$scope.currentFiles[index].description = $scope.uploaded[x].descrip;
					$scope.currentFiles[index].paycheck = $scope.uploaded[x].paycheck;
					for(y in lfiles){
						if($scope.uploaded[x].id == lfiles[y].id){
							$scope.currentFiles[index].selected = true;
						}
					}
					index++;
				} 
			}

			$scope.selectedFilesLiq = function (id) {
				$scope.currentFiles.filter(function (elem) {
					if (elem.id == id) {
						elem.selected = !elem.selected;
					} 
					return elem.id;
				});
			}

			$scope.addFilesSuccess = function () {
				var flag = false;
				$scope.currentSettlementEdit.editfiles = [];
				for ( x in $scope.currentFiles ){
					if($scope.currentFiles[x].selected){
						flag = true;
						$scope.currentSettlementEdit.editfiles.push($scope.currentFiles[x].id);
					}
				}
				if(flag == false){
					alert("Usted debe seleccionar al menos un archivo");
				}else{
					$scope.addFiles = false; 
				}
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

	}]);
	