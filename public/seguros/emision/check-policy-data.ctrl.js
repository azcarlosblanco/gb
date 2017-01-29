angular
    .module('ngNova')
    .provider('novaNotification', novaNotification)
    .controller('CheckPolicyDataCtrl', ['response', 'Restangular', '$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification',
        function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification) {
            $scope.form = {};
            $scope.form.extras = [];
            $scope.form.annexes = [];
            $scope.form.payment = [];
            $scope.errorMsg = {};
            $scope.keyStorage = 'emission/initdocumentation';
            $scope.showSubmit = true;
            $scope.checking = false;


            var temp_data = response.data.data;
            $scope.temp_data = response.data.data;
            $scope.catalog = temp_data.catalog;
            $scope.policy_cost = temp_data.policy_cost;
            $scope.process_id = temp_data.process_id;
            $scope.total_amount = 0;

            $scope.addDiscount = function(list_quotes) {
                console.log(list_quotes);
                list_quotes.push({
                    "concept": "Descuento 1",
                    "value": 0.00
                });
            }

            for (x in $scope.policy_cost) {
                if (x != "total_cost") {
                    $quote = $scope.policy_cost[x];

                    var sum = 0;
                    //console.log($quote);
                    $quote.itemPrimes = [];
                    $quote.itemTaxes = [];
                    //to save discount in the quote
                    $quote.discounts = [];
                    $scope.addDiscount($quote.discounts);
                    for (y in $quote.items) {
                        var item = $quote.items[y];
                        sum = sum + item.value;
                        if (item.commissionable) {
                            $quote.itemPrimes.push(item);
                        } else {
                            $quote.itemTaxes.push(item);
                        }
                    }

                    $quote.total_quote = sum;
                    $scope.total_amount = $scope.total_amount + sum;
                }
            }

            $scope.changePayment = function() {

                $scope.total_amount = 0;
                for (x in $scope.policy_cost) {
                    if (x != "total_cost") {
                        var sum = 0;
                        $quote = $scope.policy_cost[x];
                        for (y in $quote.itemPrimes) {
                            var item = $quote.itemPrimes[y];
                            //$scope.form.payment[x][item.id] = item.value;
                            sum = sum + item.value;
                        }

                        for (y in $quote.itemTaxes) {
                            var item = $quote.itemTaxes[y];
                            //$scope.form.payment[x][item.id] = item.value;
                            sum = sum + item.value;
                        }

                        var disc = 0;
                        for (z in $quote.discounts) {
                            var discount = $quote.discounts[z];
                            //$scope.form.payment[x][item.id] = item.value;
                            disc = disc + discount.value;
                        }
                        $quote.total_quote = sum - disc;
                        $scope.total_amount = $scope.total_amount + sum;
                    }
                }

                /*for(x in $scope.form.payment){
                	var sum = 0;
                	var quote = $scope.form.payment[x];
                	for(y in quote){
                		if(y!='sum'){
                			sum += +quote[y];
                			console.log(quote[y]);
                		}
                	}
                	$scope.form.payment[x]['sum']=sum;
                	$scope.total_amount = $scope.total_amount + sum;
                }*/
            }

            //get file that are in process of been updated or have failed
            $scope.addExtras = function() {
                $scope.form.extras.push({});
            };

            $scope.deleteExtras = function(item) {
                var index = $scope.form.extras.indexOf(item);
                $scope.form.extras.splice(index, 1);
            };

            $scope.addAnnexes = function() {
                $scope.form.annexes.push({});
            };

            $scope.deleteAnnexes = function(list, item) {
                var index = $scope.form.annexes.indexOf(item);
                if (index > -1) {
                    $scope.form.annexes.splice(index, 1);
                }
            };

            $scope.addCostDetail = function(list) {
                var item = {
                    "concept": "",
                    "new": 1,
                    "value": 0.00
                }
                list.push(item);
            }

            $scope.deleteCostDetail = function(list, item) {
                var index = list.indexOf(item);
                if (index > -1) {
                    list.splice(index, 1);
                }
            }

            $scope.deleteDiscount = function(list, item) {
                var index = list.indexOf(item);
                if (index > -1) {
                    list.splice(index, 1);
                }
            }

            $scope.addCostTaxes = function(list) {
                var item = {
                    "concept": "",
                    "new": 1,
                    "value": 0.00
                }
                list.push(item);
            }

            $scope.deleteCostTaxes = function(list, item) {
                var index = list.indexOf(item);
                if (index > -1) {
                    list.splice(index, 1);
                }
            }

            $scope.submit = function() {
                $scope.checking = true;
                var token = localStorage.getItem(__env.tokenst);
                var postUrl = "emission/newPolicy/reviewProspectPolicy/" + $scope.process_id;
                var urlWithToken = postUrl + "?token=" + token;

                var data = new FormData();

                $scope.form.policy_cost = $scope.policy_cost;

                data.append("review_policy_obj", JSON.stringify($scope.form));

                Restangular
                    .one(urlWithToken)
                    .withHttpConfig({
                        transformRequest: angular.identity
                    })
                    .customPOST(data, '', undefined, {
                        'Content-Type': undefined
                    })
                    .then(
                        function successCallback(response) {
                            $scope.checking = false;
                            console.log(response);

                            //send the files
                            var notificacion = {};
                            notificacion.title = "Formulario fue enviado exitosamente";
                            notificacion.body = "";
                            novaNotification.showNotification(notificacion);
                            $state.go('^', {}, {
                                reload: true
                            });
                        },
                        function errorCallback(response) {
                            $scope.checking = false;
                            var notificacion = {};
                            var data = "";
                            if (response.data.message == undefined) {
                                for (key in response.data) {
                                    data = data + key + ' : ' + response.data[key].join(',') + "\n";
                                }
                            } else {
                                errors = response.data.message.Error;
                                for (key in errors) {
                                    data = data + key + ' ' + errors[key];
                                }
                                dataErrors = response.data.data;
                                for (key in dataErrors) {
                                    data = data + key + ' ' + dataErrors[key];
                                }
                            }
                            notificacion.title = "Error al enviar el formulario";
                            notificacion.body = data;
                            novaNotification.showNotification(notificacion);
                        }
                    );
            }

        }
    ])
    .directive('newExtra', function() {
        return {
            restrict: 'E',
            templateUrl: 'seguros/emision/affiliate-extra.view.html'
        };
    })
    .directive('newAnnex', function() {
        return {
            restrict: 'E',
            templateUrl: 'seguros/emision/affiliate-annexe.view.html'
        };
    })
    /*.directive('sviewRegister', function(){
        return {
            restrict: 'E',
            templateUrl: 'seguros/emision/archivos-subidos.view.html'
        };
    });*/
