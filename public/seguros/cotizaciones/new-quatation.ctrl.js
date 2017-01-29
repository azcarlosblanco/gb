angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('NewQuatationCtrl', 
		['Restangular','response','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification',
		function(Restangular, response, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification){
			$scope.form = {};
			$scope.form.affiliates = [];
			$scope.form.ics = [];

			var dataReq = response.data.data;
			$scope.typeInsurance = dataReq.typeInsurance;
			$scope.gender = dataReq.gender;
			$scope.pmethod = dataReq.pmethod;
			$scope.ics = dataReq.ics;
			$scope.pmethod = dataReq.pmethod;
			$scope.agentList = dataReq.agentList;	
			
			$scope.listPlansIC = {};
			$scope.form.deductibles = {};

			$scope.showSaveQuotation = false;

			$scope.gettingCosts = {};
			$scope.sendQuotationParam = {};
			
			$scope.tonos = [
				"#8BABA6","#F7E8E1","#8BABA6","#F7E8E1","#8BABA6","#F7E8E1","#8BABA6","#F7E8E1","#8BABA6","#F7E8E1","#8BABA6","#F7E8E1","#8BABA6","#F7E8E1"
			];

			var arole_titular = "";
			$scope.arole = {};
			for(x in dataReq.arole){
				if(dataReq.arole[x]=='titular'){
					arole_titular = ""+x;
					$scope.aroletitular = {};
					$scope.aroletitular[x] = dataReq.arole[x];
				}else{
					$scope.arole[x] = dataReq.arole[x];
				}
			}
			$scope.form.affiliates.push({'role':arole_titular,'gender':'male','age':18});

			$scope.addQuotation = function () {
				$scope.form.affiliates.push({});
			}

			$scope.deleteQuotation = function (item) {
				var index=$scope.form.affiliates.indexOf(item);
	    		$scope.form.affiliates.splice(index,1);
			}

			//Obtencion de Planes

			$scope.getListPlan = function(){
				var notification = {};

				//calulate data is complete, before send request
				if($scope.form.ics.lenght==0){
            		notification.body = "Debe seleccionar al menos una compania de seguros";
				}

				if($scope.form.typeInsurance==undefined || $scope.form.typeInsurance==""){
            		notification.body = "Debe seleccionar el tipo de seguro";
				}

				if($scope.form.pmethod==undefined || $scope.form.pmethod==""){
            		notification.body = "Debe seleccionar un método de pago";
				}

				if(notification.body!=undefined){
					$scope.gettingPlans = false;
					notification.title = "Error ";
        			novaNotification.showNotification(notification);
        			return;
				}

				var urlReq = "quotation/plansQuotation";

				Restangular
	            .one(urlReq)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customGET("", {"token":localStorage.getItem(__env.tokenst),
	            				"ins_comp_ids[]":$scope.form.ics,
	            				"type_ins":$scope.form.typeInsurance})
				.then(
					function successCallback(response) {
						$scope.gettingPlans = false;
	                    $scope.listPlansIC = response.data.data;
	                    for(x in $scope.listPlansIC){
	                    	for(y in $scope.listPlansIC[x]){
	                    		var plans = $scope.listPlansIC[x][y];
	                    		for(z in plans){
	                    			var plan = plans[z];
	                    			var inUsa = [];
	                    			var outUsa = [];
	                    			var deduciblesNames = [];
	                    			var deduciblesIDs = [];
	                    			var premiuns = {};
	                    			$scope.form.deductibles[plan.plan_id] = [];
	                    			$scope.gettingCosts[plan.plan_id] = false;
	                    			for(d in plan.deductibles){
	                    				deductible = plan.deductibles[d];
	                    				inUsa.push(deductible["1"]);
	                    				outUsa.push(deductible["2"]);
	                    				deduciblesNames.push(deductible["deduct_name"]);
	                    				deduciblesIDs.push(deductible["deduct_id"]);
	                    				premiuns[deductible["deduct_id"]]={"deducible_id":deductible["deduct_id"],"total":0,"quotes":[]};
	                    			}
	                    			$scope.listPlansIC[x][y][z].deductibles_rows = [];
	                    			$scope.listPlansIC[x][y][z].deductibles_rows.push({'name':"En USA",'values':inUsa});
	                    			$scope.listPlansIC[x][y][z].deductibles_rows.push({'name':"Fuera USA",'values':outUsa});
	                    			$scope.listPlansIC[x][y][z].deduciblesIDs = deduciblesIDs;
	                    			$scope.listPlansIC[x][y][z].premiuns = premiuns;
	                    		}
	                    	}
	                    }
	                    console.log($scope.listPlansIC);
	                },
	                function errorCallback(response) {
	                	$scope.gettingPlans = false;
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
	        			novaNotification.showNotification(notificacion);
		            }
	            );
			}

			$scope.toggleSelection = function toggleSelection(item,list) {
	    		var idx = list.indexOf(item);
			    // is currently selected
			    if (idx > -1) {
			      	list.splice(idx, 1);
			    }
			    // is newly selected
			    else {
			      	list.push(item);
			    }
			};

			$scope.calculatePremiun = function(plan,ic){
				var notificacion = {};
				if($scope.form.deductibles[plan.plan_id].length==0){
					$scope.gettingCosts[plan.plan_id] = false;
					notificacion.title = "Error ";
            		notificacion.body = "Debe seleccionar al menos una deducción de deducible";
        			novaNotification.showNotification(notificacion);
        			return;
				}
				
				var urlReq = "quotation/calculatePremium";

				Restangular
	            .one(urlReq)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customGET("", {"token":localStorage.getItem(__env.tokenst),
	            				"deductibles":JSON.stringify($scope.form.deductibles[plan.plan_id]),
	            				"number_payment_id":$scope.form.pmethod,
	            				"ic":ic,
	            				"affiliates":JSON.stringify($scope.form.affiliates)})
				.then(
					function successCallback(response) {
						$scope.gettingCosts[plan.plan_id] = false;
						listQuotation = response.data.data;
	                    for(x in listQuotation){
	                    	var deduc_id = parseInt(listQuotation[x].pland_deducible_id);
	                    	var total = 0
	                    	for(y in listQuotation[x].premium){
	                    		total = total+listQuotation[x].premium[y].total;
	                    	}
	                    	plan.premiuns[deduc_id].total = total;
	                    	plan.premiuns[deduc_id].quotes = listQuotation[x].premium;
	                    	console.log(plan.premiuns);
	                    }
	                },
	                function errorCallback(response) {
	                	$scope.gettingCosts[plan.plan_id] = false;
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
	        			novaNotification.showNotification(notificacion);
		            }
	            );
			}

			$scope.cancelSaveQuotation = function(){
				$scope.showSaveQuotation = false;
				$scope.sendQuotationParam.type = "";
				$scope.sendQuotationParam.item = null;
			}

			$scope.saveQuotation = function(type,item){
				$scope.showSaveQuotation = true;
				$scope.sendQuotationParam.type = type;
				$scope.sendQuotationParam.item = item;
			}

			$scope.sendSaveQuotation = function(){
				var notificacion = {};
				var premiuns = [];
				if($scope.sendQuotationParam.type=="ic"){
					ic = $scope.sendQuotationParam.item;
					for(x in ic){
	            		var plans = ic[x];
	            		for(y in plans){
	            			var plan = plans[y];
	            			//list deduct select from that plan
	            			for(z in $scope.form.deductibles[plan.plan_id]){
	            				premiuns.push(plan.premiuns[$scope.form.deductibles[plan.plan_id][z]]);
	            			}
	            		}
	            	}
				}else if($scope.sendQuotationParam.type="plan"){
					plan = $scope.sendQuotationParam.item;
					console.log(plan);
					//list deduct select from that plan
        			for(z in $scope.form.deductibles[plan.plan_id]){
        				premiuns.push(plan.premiuns[$scope.form.deductibles[plan.plan_id][z]]);
        			}
				}

				//send quotation to be saved and send by email in the back
				$scope.form.premiuns = premiuns;
				var data = new FormData();
				var token = localStorage.getItem(__env.tokenst);
	            data.append("token",token); // API TOKEN
	            data.append("obj_quotation",JSON.stringify($scope.form));

	            urlWithToken =  "quotation/saveQuotation?token="+token;

	            Restangular
	            .one(urlWithToken)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customPOST(data, '', undefined, {'Content-Type': undefined})
				.then(
					function successCallback(response) {
						$scope.showSaveQuotation = false;
						notificacion.title = "Envio exitoso";
	            		notificacion.body = "La cotización se envió con exito";
	        			novaNotification.showNotification(notificacion);
					},
					function errorCallback(response) {
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
	        			novaNotification.showNotification(notificacion);
					}
				);
			}


}])
.directive('quotationAffiliate', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/cotizaciones/add-quatation.view.html'
    };
});