angular
    .module('ngNova')
    .provider('novaNotification', novaNotification)
    .controller('loginCtrl', ['$scope', '$state', '$http', '__env', '$templateCache', 'novaNotification',
        function($scope, $state, $http, __env, $templateCache, novaNotification) {

            $scope.form = [];
            var oldtoken = localStorage.getItem(__env.tokenst);

            this.login = function() {
                if ($scope.form) {
                    var data = new FormData();
                    for (var i in $scope.form) {
                        data.append(i, $scope.form[i]);
                    }

                    //eliminar cache para evitar que angular use un token antiguo
                    console.log(data);
                    $templateCache.removeAll();

                    var authUrl = "auth/authenticate";
                    $http.post(
                            __env.authUrl + authUrl,
                            data, {
                                transformRequest: angular.identity,
                                headers: {
                                    'Content-Type': undefined
                                }
                            }
                        )
                        .then(
                            function successCallback(response) {
                                //save token in the local storage
                                var token = response.data.data.token;
                                localStorage.removeItem(__env.tokenst);
                                localStorage.setItem(__env.tokenst, token);
                                //window.location.href="http://ci.dev360.tech/#/admin";
                                window.location.href = __env.frontUrl + "#/seguros";
                            },
                            function errorCallback(response) {
                                var notificacion = {};
                                var data = "";
                                if (response.data.message == undefined) {
                                    for (key in response.data) {
                                        data = data + key + ' : ' + response.data[key].join(',') + "\n";
                                    }
                                } else {
                                    errors = response.data.message.Error;
                                    console.log(errors);
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
        }
    ]);
