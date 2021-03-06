angular
    .module('ngNova')
    .controller('NewPolicyCustomerResponseCtrl', ['response', 'Restangular', '$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification',
        function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification) {

            console.log(response);

            var process_ID = response.data.data.process_ID;

            $scope.confirmSendApp = false;
            $scope.catalog = response.data.data.catalog;
            $scope.form = {}
            $scope.focused = [];

            $scope.showMessage = function() {
                $scope.confirmSendApp = true;
            };

            $scope.submit = function(data) {
                if ($scope.form) {
                    $scope.confirmSendApp = false;
                    var data = new FormData();
                    var token = localStorage.getItem(__env.tokenst);
                    data.append("token", token); // API TOKEN
                    data.append("response", $scope.form.response)

                    var postUrl = "emission/newPolicy/registerCustomerResponse/" + process_ID;
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
                                notificacion.title = "La respuesta dle cliente fue registrada";
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
                })
            }

        }
    ]);
