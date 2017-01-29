angular
.module('ngNova')
.controller('RefundSettlementCtrl', 
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout','webNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout,webNotification){ 
			
			console.log(response.data.data);

			$scope.pmethods = response.data.data.catalog;
			$scope.beneficiaries = {'affiliate':'Afiliado','supplier':"Proveedor"};
			$scope.claim = response.data.data.claim;
			$scope.idForPost = response.data.route.substring(28);
			$scope.form = {}
			$scope.refunds = [];
			$scope.settlements = [];
			$scope.ntoken = localStorage.getItem(__env.tokenst);

			$scope.setRefundsData = function(response){
				var index = 0;
				var total_refunded = 0;
				$scope.refunds = [];
				$scope.settlements = [];
				for(x in response.data.data.settlements){
					total_refunded = 0;	
					for(y in response.data.data.settlements[x].refunds){
						$scope.refunds[index]={}
						$scope.refunds[index]['id']=response.data.data.settlements[x].refunds[y].id;
						$scope.refunds[index]['amount']=response.data.data.settlements[x].refunds[y].value;
						$scope.refunds[index]['invoice']=response.data.data.settlements[x].description;
						$scope.refunds[index]['beneficiary']="affiliate";
						if(response.data.data.settlements[x].refunds[y].to_supplier==1){
							$scope.refunds[index]['beneficiary']="supplier";
						}
						$scope.refunds[index]['method']=response.data.data.settlements[x].refunds[y].payment_method_id+"";
						$scope.refunds[index]['date']=new Date(Date.parse(response.data.data.settlements[x].refunds[y].pay_date));
						total_refunded = total_refunded + $scope.refunds[index]['amount'];
						index++;
						console.log($scope.refunds);
					}
					if(total_refunded<response.data.data.settlements[x].expected_refund){
						response.data.data.settlements[x].total_refunded = total_refunded;
						console.log(response.data.data.settlements[x]);
						$scope.settlements.push(response.data.data.settlements[x]);
						console.log("total_refunded "+total_refunded);
					}
				}
			}

			$scope.refreshData = function(){
				$scope.amountLiqSelected = 0;

				var token = localStorage.getItem(__env.tokenst); // API TOKEN
				var endpoint = 'settlements/registerPayment/'+$scope.idForPost
									+"?token="+token;
          		$http.get(
					__env.apiUrl+endpoint,
					{
						transformRequest: angular.identity,
						headers: {'Content-Type': undefined}
					}
				).then(
					function successCallback(response) {
						$scope.pmethods = response.data.data.catalog;
						$scope.beneficiaries = {'affiliate':'Afiliado','supplier':"Proveedor"};
						$scope.claim = response.data.data.claim;
						$scope.form = {}
						$scope.resetRefundFields();
						$scope.setRefundsData(response);
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

			$scope.selectInvoice = function (item) {
				console.log(item);
				$scope.resetRefundFields();
				$scope.form.settlement = item;
				$scope.form.id = item.id;
				console.log("Before : ", $scope.amountByPay);
				$scope.amountByPay = item.expected_refund - item.total_refunded;
				console.log("After : ", $scope.amountByPay);

			}

			$scope.resetRefundFields = function() {
				$scope.form = {};
				$scope.form.amount = 0.00;
				$scope.form.method = null;
				$scope.form.supplier = 0;
				$scope.amountByPay = 0.00;
				$scope.form.date = "";
			}

			$scope.resetRefundFields();
			$scope.setRefundsData(response);

			$scope.addRefund = function() {
				console.log($scope.form.settlement);
				var data = new FormData();
				var refund = $scope.form.amount - $scope.form.uncovered - $scope.form.coaseguro - $scope.form.deducible - $scope.form.dscto;
	            data.append('set_id', $scope.form.settlement.id);
	            data.append('invoice', $scope.form.settlement.description);
	            data.append('paid', $scope.form.amount);
	            data.append('pay_method', $scope.form.method);
	            data.append('supplier', 0);
	            if($scope.form.beneficiary=="supplier"){
	            	data.append('supplier', 1);
	            }
	            data.append('pay_date', $scope.form.date);

	            var token = localStorage.getItem(__env.tokenst); // API TOKEN
	            data.append('token',token); // API TOKEN

	            $scope.submitRegister(data);
			}

			$scope.submitRegister = function(content) {
				var token = localStorage.getItem(__env.tokenst); // API TOKEN
				var postUrl = "settlements/registerPayment/"+$scope.idForPost;
        		var urlWithToken = postUrl+"?token="+token;

				if (content) {
			        Restangular
			        .one(urlWithToken)
			        .withHttpConfig({transformRequest: angular.identity})
			        .customPOST(data, '', undefined, {'Content-Type': undefined})
			        .then(
			            function (response) {
			            	$scope.refreshData();
			            },
			            function (response) {
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

			$scope.deleteRefund  = function (refund){

				var token = localStorage.getItem(__env.tokenst); // API TOKEN

				var uri = 'settlements/registerPayment/'+refund.id+
							'?token='+token;
				Restangular
			        .one(uri)
			        .withHttpConfig({transformRequest: angular.identity})
			        .customDELETE(undefined, undefined, {'Content-Type': undefined})
			        .then(
			            function (response) {
			            	$scope.refreshData();
			            },
			            function (response) {
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
.filter('parseDate', function($filter) {
    return function(input) {
        console.log(input);
        var serverdate = new Date(Date.parse(input));
        console.log(serverdate);
        return serverdate; 
    };
});
	