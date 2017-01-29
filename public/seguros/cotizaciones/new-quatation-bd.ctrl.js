angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('NewQuotationCtrl', 
		['Restangular','response','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification',
		function(Restangular, response, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification){
			$scope.form = {};
			$scope.form.affiliates = [];
			$scope.form.ics = [];

			var dataReq = response.data.data;
			//$scope.typeInsurance = dataReq.typeInsurance;
			//$scope.gender = dataReq.gender;
			$scope.pmethod = dataReq.pmethod;
			//$scope.ics = dataReq.ics;
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

			$scope.today = function(){
				var today = new Date();
				var dd = today.getDate();
				var mm = today.getMonth()+1; //January is 0!
				var yyyy = today.getFullYear();
				if(dd<10) {
				    dd='0'+dd
				} 
				if(mm<10) {
				    mm='0'+mm
				} 
				today = mm+'/'+dd+'/'+yyyy;
				return today;
			}

			//init data
			$scope.form.owner_age = 40;
			$scope.form.spouse_age = 0;
			$scope.form.number_kid = 0;
			$scope.form.effective_date = new Date();

			$scope.addcovers = {};
			$scope.addcoversSelected = {};
			

			//Obtencion de Planes
			$scope.formatListPlans = function(){
				$scope.listPlans = response.data.data.listPlans;
				console.log($scope.listPlans);
            	for(z in $scope.listPlans){
        			var plan = $scope.listPlans[z];
        			var inUsa = [];
        			var outUsa = [];
        			var deduciblesNames = [];
        			var deduciblesIDs = [];
        			var premiuns = {};
        			var addCoverPlans = [];
        			$scope.form.deductibles[plan.plan_id] = [];
        			$scope.gettingCosts[plan.plan_id] = false;
        			for(d in plan.deductibles){
        				deductible = plan.deductibles[d];
        				inUsa.push(deductible["1"]);
        				outUsa.push(deductible["2"]);
        				deduciblesNames.push(deductible["deduct_name"]);
        				deduciblesIDs.push(deductible["deduct_id"]);
        				premiuns[deductible["deduct_id"]]={"deducible_id":deductible["deduct_id"],"total":0,"quotes":[]};

        				for(ad in deductible.add_covers){
        					var acp = {'name':deductible.add_covers[ad]['name'],'selected':false}
        					//check if option exist
        					var exist = false;
        					for(tt in addCoverPlans){
        						if(deductible.add_covers[ad]['name'] == addCoverPlans[tt]['name']){
        							exist = true;
        						}
        					}
        					if(!exist){
        						addCoverPlans.push(acp);
        					}
        				}
        			}
        			$scope.listPlans[z].deductibles_rows = [];
        			$scope.listPlans[z].deductibles_rows.push({'name':"En USA",'values':inUsa});
        			$scope.listPlans[z].deductibles_rows.push({'name':"Fuera USA",'values':outUsa});
        			$scope.listPlans[z].deduciblesIDs = deduciblesIDs;
        			$scope.listPlans[z].premiuns = premiuns;
        			$scope.listPlans[z].addCovers = addCoverPlans;
        		}
        		console.log($scope.listPlans);
            }

            $scope.formatListPlans();

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
				var notification = {};
				if($scope.form.pmethod==undefined || $scope.form.pmethod==""){
            		notification.body = "Debe seleccionar un método de pago\n";
				}

				if($scope.form.plan==undefined || $scope.form.plan==""){
            		notification.body = "Debe seleccionar un plan";
				}

				if($scope.form.owner_age==undefined || $scope.form.plan==""){
            		notification.body = "Debe ingresar edad Propietario\n";
				}else if($scope.form.owner_age<18){
					notification.body = "Edad propietario es inválida\n";
				}

				if($scope.form.spouse_age!=undefined && $scope.form.spouse_age!=""){
            		if($scope.form.spouse_age<18){
            			notification.body = "Edad esposo(a) es inválida\n";
            		}
				}

				if($scope.form.number_kid!=undefined && $scope.form.spouse_age!=""){
            		if($scope.form.number_kid<0){
            			notification.body = "Número de niños es inválido\n";
            		}
				}

				if($scope.form.deductibles[plan.plan_id].length==0){
            		notification.body = "Debe seleccionar al menos una deducción de deducible\n";
				}


				if(notification.body!=undefined){
					$scope.gettingCosts[plan.plan_id] = false;
					notification.title = "Error ";
        			novaNotification.showNotification(notification);
        			return;
				}
				
				

				var additionalCovers = [];
				for(ac in plan.addCovers){
					if(plan.addCovers[ac]['selected']){
						additionalCovers.push(plan.addCovers[ac]['name']);
					}
				}

				var selectedDeductibles = [];
				for(deductID in plan.deductibles){
					var deductible  = plan.deductibles[deductID];
					var idx = $scope.form.deductibles[plan.plan_id].indexOf(deductible['deduct_id']);
					if( idx >- 1 ){
						var selected = {'id':deductible['deduct_id'],'addCoversValue':[]};
						if(additionalCovers.length>0){
							for(ac in deductible.add_covers){
								var addCover = deductible.add_covers[ac];
								console.log(additionalCovers);
								console.log(addCover);
								var idx2 = additionalCovers.indexOf(addCover['name']);
								if(idx2>-1){
									console.log('entro');
									selected['addCoversValue'].push({
										"acID":addCover['id'],
										"acvID":addCover['options'][0]['id'],
									});
								}
							}
						}
						selectedDeductibles.push(selected);
					}
				}
				
				var urlReq = "quotation/calculatePremium";
				Restangular
	            .one(urlReq)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customGET("", {"deductibles":JSON.stringify(selectedDeductibles),
	            				"number_payment_id":$scope.form.pmethod,
	            				"ic":ic,
	            				"owner_age":$scope.form.owner_age,
	            				"spouse_age":$scope.form.spouse_age,
	            				"number_kid":$scope.form.number_kid,
	            				"effective_date":$scope.form.effective_date})
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
				if($scope.sendQuotationParam.type="plan"){
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