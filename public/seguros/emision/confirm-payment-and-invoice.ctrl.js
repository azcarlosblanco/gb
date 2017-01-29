angular
    .module('ngNova')
    .controller('NewPolicyConfirmPaymentCtrl', ['response', 'Restangular', '$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification',
        function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification) {

            console.log(response);

            var process_ID = response.data.data.process_ID;

            $scope.confirmSendApp = false;
            $scope.catalog = response.data.data.catalog;
            $scope.form = {}
            $scope.focused = [];
            $scope.tmp_data = response.data.data.payment;

            $scope.filefields = [];
            $scope.confirm_payment_file = null;
            $scope.invoice_file = null;

            if ($scope.tmp_data != undefined) {
                for (x in $scope.tmp_data) {
                    $scope.form[x] = $scope.tmp_data[x];
                }
            }

            $scope.ntoken = localStorage.getItem(__env.tokenst);

            $scope.submit = function(data) {
                if ($scope.form) {
                    $scope.confirmSendApp = false;
                    var data = new FormData();
                    var token = localStorage.getItem(__env.tokenst);
                    data.append("token", token); // API TOKEN
                    for (x in $scope.form) {
                        data.append(x, $scope.form[x]);
                    }

                    data.append("confirm_payment_file", $scope.confirm_payment_file);
                    data.append("invoice_file", $scope.invoice_file);

                    if ($scope.confirm_payment_file == null || $scope.invoice_file == null) {
                        var notificacion = {};
                        notificacion.title = "Parámetros faltantes";
                        notificacion.body = "Debe subir el achivo de confirmación de pago y la factura del cliente";
                        $scope.showNotification(notificacion);
                    }

                    var postUrl = "emission/newPolicy/registerInvoice/" + process_ID;
                    var urlWithToken = postUrl + "?token=" + token;

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
                                console.log(response);
                                var notificacion = {};
                                notificacion.title = "La confirmación del pago fue registrada";
                                notificacion.body = "";
                                msg = response.data.message.Success;
                                for (key in msg) {
                                    notificacion.body = notificacion.body + key + ' ' + msg[key];
                                }
                                $scope.showNotification(notificacion);
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
                                $scope.showNotification(notificacion);
                            }
                        );
                }
            };

            $scope.showNotification = function(data) {
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
            };

        }
    ]);
