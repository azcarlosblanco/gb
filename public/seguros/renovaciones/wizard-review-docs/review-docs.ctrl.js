angular
.module('ngNova')
.controller('ClaimReviewDocsCtrl',
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification){
		$scope.validOpt = [{id:1, value:'Yes'},{id:0, value:'No'}];
		$scope.tDeductibles = [{id:0, value:'EC'},{id:1, value:'USA'}]
		$scope.init_value = [];
		$scope.file = {};
		$scope.step = 1;
		$scope.form = [];
		$scope.categories = {};
		$scope.affiliates = {};
		$scope.registers = [];
		$scope.errorMsg = {};
		$scope.ntoken=localStorage.getItem(__env.tokenst);
		$scope.catInvoice = null;
		$scope.categoriesA={};
		$scope.init_category={};
		$scope.datePicker = [];
		$scope.associations = [];
		$scope.associate = [];
		$scope.firsttime = true;
		$scope.suppliers = [];
		$scope.supplierA = [];
		$scope.affiliateA = [];
		
		//for invoice with alcance option
		$scope.alcanceOpt = [{id:1, value:'Yes'},{id:0, value:'No'}];
		$scope.prevOrders = {}
		$scope.noPreviousOrders = false;
		$scope.disableAlcance = false;


		var temp_data = response.data.data;

		for (x in temp_data.categories) {
			if(temp_data.categories[x]['name']=="claim_invoice"){
				$scope.catInvoice=temp_data.categories[x]['id'];
			}
			$scope.categoriesA[temp_data.categories[x]['id']]=
					temp_data.categories[x]['description']
			$scope.categories=temp_data.categories;
		}

		for (x in temp_data.affiliates) {
			$scope.affiliates=temp_data.affiliates;
			$scope.affiliateA[temp_data.affiliates[x]['id']]=
					temp_data.affiliates[x]['name'];
		}

		for (x in temp_data.suppliers) {
			$scope.suppliers=temp_data.suppliers;
			$scope.supplierA[temp_data.suppliers[x]['id']]=
					temp_data.suppliers[x]['name'];
		}

		$scope.form['policy_number']=temp_data.policy_number;
		$scope.form['customer_name']=temp_data.customer_name;
		$scope.process_id = temp_data.process_id;

		if(temp_data.files !== undefined){
			for(x in temp_data.files){
				var index = temp_data.files[x].id
				$scope.file[index]={}
				$scope.file[index]['id']=index;
				$scope.file[index]['name']=temp_data.files[x].original_filename;
				$scope.file[index]['description']=temp_data.files[x].description;
				$scope.file[index]['category']=temp_data.files[x].procedure_document_id;
				$scope.init_category[index]=temp_data.files[x].procedure_document_id;
				$scope.file[index]['valid']=1;
				$scope.file[index]['usa']=0;
				
				//$scope.registers.push({});
			}
		}

		$scope.addOtherAffiliate = function(index){
			$scope.associate.push({
							'id':$scope.file[index]['id'],
							'name':$scope.file[index]['name'],
							'description':$scope.file[index]['description'],
							'supplier':$scope.file[index]['supplier'],
							'category':$scope.file[index]['category'],
							'valid':$scope.file[index]['valid'],
							'usa':$scope.file[index]['usa'],
							'alcance':0
						});
			$scope.associations.push({});
		}

		$scope.addOrderInvoice = function(item){
			var index=$scope.associations.indexOf(item);
			if($scope.associate[index]['alcance']==1){
				$scope.associate[index]['showMsg'] = true;
				$scope.associate[index]['msgAlcance'] = "Cargando datos...";

				//add data
				affiliate_id = $scope.associate[index]['affiliate'];

				if(affiliate_id===undefined){
					$scope.associate[index]['msgAlcance'] = "Seleccione primero al afiliado...";
					return;
				}

				//get the orders from server
				var	getUrl = "claims/reviewDocuments/previousOrders/"+affiliate_id;
				var token = localStorage.getItem(__env.tokenst);

	    		//peticion http
    			$http.get(
					__env.apiUrl+getUrl+"?token="+token,
					data,
					{
						transformRequest: angular.identity,
						headers: {'Content-Type': undefined}
					}
				)
				.then(
					function successCallback(response) {
						//create a register with the old order that the user can
						//add
						if(response.data.data.length==0){
							$scope.associate[index]['msgAlcance'] = "No existen órdenes anteriores";
						}else{
							$scope.associate[index]['showMsg']=false;
							$scope.prevOrders[affiliate_id] = response.data.data;
							$scope.associate[index]['order']=$scope.prevOrders[affiliate_id][0];
						}
					},
					function errorCallback(response) {
						$scope.associate[index]['msgAlcance'] = "Se ha producido un error al obtener la información";
					}
				);
			}else{
				//remove the selected alcance
				delete $scope.associate[index]['order'];
			}
		};

		$scope.selectAffiliate = function(item){
			//delete the alcance files of the invoice
			var index=$scope.associations.indexOf(item);
			$scope.associate[index]['alcance']=0;
			delete $scope.associate[index]['order'];
		}

		$scope.deleteAffiliate = function(item){
			var index=$scope.associations.indexOf(item);
			$scope.associate.splice(index,1);
			$scope.associations.splice(index,1);
		}

		$scope.previous = function (){
			if($scope.step==3){
				$scope.step=2;
				$state.go('^.paso-2', {});
			}else if($scope.step==2){
				$scope.step=1;
				$state.go('^.paso-1', {});
			}
		}

	    $scope.next = function () {
	    	console.log("step"+$scope.step);
	    	if($scope.step==1){
				//check that at least one file is mark as valid
				//if not indicate that at least one file must be valid to continue
				var valid=false;
				var index=0;
				for(x in $scope.file){
					if($scope.firsttime){
						$scope.associate.push({
								'id':$scope.file[x]['id'],
								'name':$scope.file[x]['name'],
								'description':$scope.file[x]['description'],
								'category':$scope.file[x]['category'],
								'supplier':$scope.file[x]['supplier'],
								'valid':$scope.file[x]['valid'],
								'usa':0,
								'alcance':0
							});
						$scope.associations.push({});
						//create the datepicker object for them
						$scope.datePicker.push(false);
					}else{
						for(i in $scope.associate){
							if($scope.associate[i]['id']==$scope.file[x]['id']){
								$scope.associate[i]['description']=$scope.file[x]['description'];
								$scope.associate[i]['category']=$scope.file[x]['category'];
								$scope.associate[i]['supplier']=$scope.file[x]['supplier'];
								$scope.associate[i]['valid']=$scope.file[x]['valid'];
							}
						}
					}

					if($scope.file[x]['valid']){
						if($scope.file[x]['category']==$scope.catInvoice){
							valid=true;
						}
					}
					index++;
				}
				$scope.firsttime=false;
				if(valid){
					$scope.step=2;
					$state.go('^.paso-2', {});
				}else{
					alert("Por lo menos un archivo debe haber una factura para continuar");
				}
	    	}else if($scope.step==2){
				//check validations
				//create resume
				$scope.resume={};
				for(x in $scope.associate){
					index=$scope.associate[x]['affiliate'];
	    			if($scope.resume[index]===undefined){
	    				$scope.resume[index]={};
	    				$scope.resume[index]['id']=index;
	    				$scope.resume[index]['aff_name']=$scope.affiliateA[index];
	    				$scope.resume[index]['files']=[];
	    			}
	    			$scope.resume[index]['files'].push({
    					'id':$scope.associate[x]['id'],
    					'description':$scope.associate[x]['description'],
	    				'category':$scope.categoriesA[$scope.associate[x]['category']],
	    				'value':$scope.associate[x]['invoice_value'],
	    				'supplier':$scope.supplierA[$scope.associate[x]['supplier']],
    				});
    				if($scope.associate[x]['order']!==undefined){
	    				var ordera=$scope.associate[x]['order'];
	    				$scope.resume[index]['files'].push({
	    					'id':ordera['file_id'],
	    					'description':ordera['description'],
		    				'category':$scope.categoriesA[$scope.associate[x]['category']],
		    				'value':ordera['invoice_value'],
		    				'supplier':$scope.supplierA[$scope.associate[x]['supplier_id']],
	    				});
	    			}
	    		}

	    		//send associate values
	    		$scope.step=3;
				$state.go('^.paso-3', {});

	    	}else{
	    		//finish process
	    		var	postUrl = "claims/reviewDocuments/"+$scope.process_id;
				var files = {};
	    		for(x in $scope.associate){
	    			index=$scope.associate[x]['id'];
	    			if(files[index]===undefined){
	    				files[index]={
		    				'valid':$scope.associate[x]['valid'],
		    				'description':$scope.associate[x]['description'],
		    				'type':$scope.associate[x]['category'],
		    				'supplier':$scope.associate[x]['supplier'],
		    				'usa':$scope.associate[x]['usa'],
		    				'affs':[],
		    			};
	    			}

	    			if($scope.associate[x]['order']!==undefined){
	    				console.log($scope.associate[x]['order']);
	    				var ordera=$scope.associate[x]['order'];
	    				var orderid=ordera['file_id'];
	    				files[orderid]={
		    				'valid':1,
		    				'description':ordera['description'],
		    				'type':ordera['category'],
		    				'supplier':ordera['supplier_id'],
		    				'usa':0,
		    				'affs':[
		    						{
		    						'id'     :$scope.associate[x]['affiliate'],
	    							'date'   :$scope.associate[x]['invoice_date'],
	    							'value'  :$scope.associate[x]['invoice_value'],
	    							'concept':$scope.associate[x]['invoice_concept'],
	    							}
		    					],
		    			};
	    			}

	    			files[index]['affs'].push({
	    							'id'     :$scope.associate[x]['affiliate'],
	    							'date'   :$scope.associate[x]['invoice_date'],
	    							'value'  :$scope.associate[x]['invoice_value'],
	    							'concept':$scope.associate[x]['invoice_concept'],
	    						});
	    		}
	    		console.log(files);
	    		
	    		var data = new FormData();
	    		var token = localStorage.getItem(__env.tokenst);
    			data.append("token", token);
	    		data.append("files", JSON.stringify(files));

            	var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

	    		//peticion http
    			Restangular
		        .one(urlWithToken)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customPOST(data, '', undefined, {'Content-Type': undefined})
		        .then(
					function successCallback(response) {
						$state.go('root.seguros.reclamos', {}, {reload: true});
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
	        });
		}

		/**date functions **/
		$scope.dateOptions = {
		    formatYear: 'yyyy',
		    maxDate: new Date(),
		    minDate: new Date(2016, 1, 1),
		    startingDay: 1
		};
		
		$scope.dateopen = function(item) {
			var index=$scope.associations.indexOf(item);
			$timeout(function() {
		      	$scope.datePicker[index]=true
		    });
	  	};
	  	$scope.format = 'M!/d!/yyyy';
	  	/**********************/
}])
.directive('claimFiles', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/reclamos/wizard-review-docs/entry-file.view.html'
    };
})
.directive('newAssociation', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/reclamos/wizard-review-docs/entry-association.view.html'
    };
})
