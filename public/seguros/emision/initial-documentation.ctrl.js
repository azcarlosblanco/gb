angular
    .module('ngNova')
    .controller('EmissionInitDocumentation', ['response', 'Restangular', '$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'webNotification',
        function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, webNotification) {
            $scope.form = {};
            $scope.form.files = [];
            $scope.form.errFiles = [];
            $scope.registers = [];
            $scope.uregisters = [];
            $scope.errorMsg = {};
            $scope.F = null;
            $scope.keyStorage = 'emission/initdocumentation';

            $scope.showSubmit = true;
            $scope.viewprocess = false;

            $scope.ntoken = localStorage.getItem(__env.tokenst);


            var temp_data = response.data.data;
            console.log("response ", response.data);

            if (temp_data.process_id !== undefined) {
                $scope.process_id = temp_data.process_id;
            }

            $scope.catalog = {};

            $scope.typesDocuments = {
                "1": "Cedula",
                "2": "Ruc",
                "3": "Pasaporte",
            };

            console.log("temp_data ", temp_data);
            for (var x in temp_data) {
                if (x == 'data') {
                    for (y in temp_data[x]) {
                        $scope.viewprocess = true;
                        $scope.showSubmit = false;
                        if (y == 'prev_insurance') {
                            temp_data[x][y] = temp_data[x][y] == "true" ? true : false;
                        }
                        $scope.form[y] = temp_data[x][y];
                    }
                } else if (x == 'files') {
                    //archivos subidos o que han fallado de subir
                    var files = temp_data[x];
                    $scope.form.files = [];
                    for (ts in files) {
                        //check if the file has failed to allow him re-upload the file
                        var ferror = localStorage.getItem($scope.keyStorage + '/' + $scope.process_id +
                            "/" + ts);
                        if (ferror == 1) {
                            $scope.showSubmit = true;
                            files[ts].error = 1;
                        }
                        files[ts].ts = ts;
                        $scope.registers.push({});
                        $scope.form.files.push(files[ts]);
                    }
                } else {
                    $scope.catalog[x] = temp_data[x];
                }
            }

            if ($scope.viewprocess == false) {
                //form
                $scope.form.files.push({});
                $scope.form.files[0]['category'] = "" + $scope.catalog['docListName']['form'];
                $scope.registers.push({});
                //identity document
                $scope.form.files.push({});
                $scope.form.files[1]['category'] = "" + $scope.catalog['docListName']['id'];
                $scope.registers.push({});

                console.log($scope.form);
            }

            $scope.sizeDocument = function() {
                console.log("form.typeDocument ", $scope.form.typeDocument);
                if ($scope.form.typeDocument == 1) {
                    $scope.size = 10;
                } else if ($scope.form.typeDocument == 2) {
                    $scope.size = 13;
                } else {
                    $scope.size = 18;
                }
            };
            /**
            @author :: Angel Tigua <atigua@novatechnology.com.ec>
            @description :: Validating method Cedula.
            */
            $scope.validateDni = function() {
                // console.log("$scope.form.identity_document AAAAAAa ", $scope.form.identity_document);
                var cedula = $scope.form.identity_document;
                if (cedula.length == 10) {

                    //Obtenemos el digito de la region que sonlos dos primeros digitos
                    var digito_region = cedula.substring(0, 2);

                    //Pregunto si la region existe ecuador se divide en 24 regiones
                    if (digito_region >= 1 && digito_region <= 24) {

                        // Extraigo el ultimo digito
                        var ultimo_digito = cedula.substring(9, 10);

                        //Agrupo todos los pares y los sumo
                        var pares = parseInt(cedula.substring(1, 2)) + parseInt(cedula.substring(3, 4)) + parseInt(cedula.substring(5, 6)) + parseInt(cedula.substring(7, 8));

                        //Agrupo los impares, los multiplico por un factor de 2, si la resultante es > que 9 le restamos el 9 a la resultante
                        var numero1 = cedula.substring(0, 1);
                        numero1 = (numero1 * 2);
                        if (numero1 > 9) {
                            numero1 = (numero1 - 9);
                        }

                        var numero3 = cedula.substring(2, 3);
                        numero3 = (numero3 * 2);
                        if (numero3 > 9) {
                            numero3 = (numero3 - 9);
                        }

                        var numero5 = cedula.substring(4, 5);
                        numero5 = (numero5 * 2);
                        if (numero5 > 9) {
                            numero5 = (numero5 - 9);
                        }

                        var numero7 = cedula.substring(6, 7);
                        numero7 = (numero7 * 2);
                        if (numero7 > 9) {
                            numero7 = (numero7 - 9);
                        }

                        var numero9 = cedula.substring(8, 9);
                        numero9 = (numero9 * 2);
                        if (numero9 > 9) {
                            numero9 = (numero9 - 9);
                        }

                        var impares = numero1 + numero3 + numero5 + numero7 + numero9;

                        //Suma total
                        var suma_total = (pares + impares);

                        //extraemos el primero digito
                        var primer_digito_suma = String(suma_total).substring(0, 1);

                        //Obtenemos la decena inmediata
                        var decena = (parseInt(primer_digito_suma) + 1) * 10;

                        //Obtenemos la resta de la decena inmediata - la suma_total esto nos da el digito validador
                        var digito_validador = decena - suma_total;

                        //Si el digito validador es = a 10 toma el valor de 0
                        if (digito_validador == 10)
                            digito_validador = 0;

                        //Validamos que el digito validador sea igual al de la cedula
                        if (digito_validador == ultimo_digito) {
                            console.log('la cedula:' + cedula + ' es correcta');
                            return true;
                        } else {
                            console.log('la cedula:' + cedula + ' es incorrecta');
                            return false;
                        }

                    } else {
                        // imprimimos en consola si la region no pertenece
                        console.log('Esta cedula no pertenece a ninguna region');
                        return false;
                    }
                } else if (cedula.length === 13) {
                    // console.log("aquiiii...", cedula);
                    // function validar() {
                    var number = cedula;
                    var dto = number.length;
                    var valor;
                    var acu = 0;
                    if (number === "") {
                        console.log('No has ingresado ningún dato, porfavor ingresar los datos correspondientes.');
                    } else {
                        for (var i = 0; i < dto; i++) {
                            valor = parseInt(number.substring(i, i + 1));
                            console.log("tipo ", typeof(valor));
                            // console.log("int ",);
                            if (valor === 0 || valor === 1 || valor === 2 || valor === 3 || valor === 4 || valor === 5 || valor === 6 || valor === 7 || valor === 8 || valor === 9) {
                                acu = acu + 1;
                            }
                        }
                        if (acu == dto) {
                            while (number.substring(10, 13) !== '001') {
                                console.log('Los tres últimos dígitos no tienen el código del RUC 001.');
                                return false;
                            }
                            while (number.substring(0, 2) > 24) {
                                console.log('Los dos primeros dígitos no pueden ser mayores a 24.');
                                return false;
                            }
                            // alert('El RUC está escrito correctamente');
                            // alert('Se procederá a analizar el respectivo RUC.');
                            var porcion1 = number.substring(2, 3);
                            if (porcion1 < 6) {
                                console.log('El tercer dígito es menor a 6, por lo \ntanto el usuario es una persona natural.\n');
                                return true;

                            } else {
                                if (porcion1 == 6) {
                                    console.log('El tercer dígito es igual a 6, por lo \ntanto el usuario es una entidad pública.\n');
                                    return true;
                                } else {
                                    if (porcion1 == 9) {
                                        console.log('El tercer dígito es igual a 9, por lo \ntanto el usuario es una sociedad privada.\n');
                                        return true;
                                    }
                                }
                            }
                        } else {
                            console.log("Se ingreso Texto.");
                            return false;
                        }
                    }

                    //  ====================================================================
                    //imprimimos en consola si la cedula tiene mas o menos de 10 digitos
                    // console.log('Esta cedula tiene menos de 10 Digitos');/
                    // console.log('validacion para ruc ...');

                    // return false;
                }

            };


            //get file that are in process of been updated or have failed
            $scope.addRegister = function() {
                $scope.registers.push({});
                $scope.form.files.push({});
            };

            $scope.deleteRegister = function(item) {
                var index = $scope.registers.indexOf(item);
                $scope.form.files.splice(index, 1);
                $scope.registers.splice(index, 1);
            };

            $scope.registerFile = function(file, item) {
                var index = $scope.registers.indexOf(item);
                if ($scope.form.files[index] == undefined) {
                    $scope.form.files[index] = {};
                }
                if ($scope.viewprocess === false) {
                    var ts = Math.floor(Date.now() / 1000);
                    $scope.form.files[index]['ts'] = ts;
                }
                $scope.form.files[index]['f'] = file;
                $scope.form.files[index]['name'] = file.name;
            };

            $scope.submit = function(errFiles) {

                if ($scope.form.files.length === 0) {
                    var notificacion = {};
                    notificacion.title = "El formulario tiene fallas";
                    notificacion.body = "Debe subir los archivos";
                    $scope.showNotification(notificacion);
                    return;
                }
                // console.log("validateDni ", $scope.validateDni());

                if (!$scope.validateDni()) {
                    var message = {
                        title: "El formulario tiene fallas",
                        body: "Error al validar identificación"
                    };

                    $scope.showNotification(message);
                    return;
                }

                var filesByUpload = {};
                var num_files = 0;
                var uploadcheque = 0;
                for (var x in $scope.form.files) {
                    if ($scope.form.files[x]['f'] === undefined) {
                        alert("Faltan de subir archivos");
                        return;
                    }
                    //$scope.form.files[x]['ts']=ts;
                    var ts = $scope.form.files[x].ts;
                    filesByUpload[ts] = {
                        'name': $scope.form.files[x].name,
                        'description': $scope.form.files[x]['description'],
                        'category': $scope.form.files[x]['category']
                    };

                    if ($scope.catalog['documentList'][$scope.form.files[x]['category']] === 'Cheque') {
                        uploadcheque = 1;
                    }
                    num_files++;
                }

                var data = new FormData();

                var token = localStorage.getItem(__env.tokenst);
                data.append("token", token); // API TOKEN

                data.append("name", $scope.form.name);
                data.append("lastname", $scope.form.lastname);
                console.log("$scope.form.identity_document ", $scope.form.identity_document);
                data.append("identity_document", $scope.form.identity_document);

                data.append("email", $scope.form.email ? $scope.form.email : "");

                if (!isNaN($scope.form.mobile)) { // es numero
                    data.append("mobile", $scope.form.mobile ? $scope.form.mobile : "");
                } else {
                    $scope.showNotification({
                        title: "Error en Celular",
                        body: "Solo puede contener Número"
                    });
                }
                if (!isNaN($scope.form.phone)) { // es numero
                    data.append("phone", $scope.form.phone);
                } else {
                    $scope.showNotification({
                        title: "Error en Convencional",
                        body: "Solo puede contener Número"
                    });
                }

                data.append("agente_id", $scope.form.agente_id);
                data.append("plan_id", $scope.form.plan_id);
                data.append("prev_insurance", $scope.form.prev_insurance);

                if ($scope.form.prev_insurance)
                    data.append("prev_insurance_comp", $scope.form.prev_insurance_comp);
                else
                    data.append("prev_insurance_comp", "");

                data.append("num_files", num_files);
                data.append("upload_cheque", uploadcheque);
                data.append("files", JSON.stringify(filesByUpload));

                var postUrl = "reception/newPolicy/initialDocumentation";
                var urlWithToken = postUrl + "?token=" + token;

                // console.log("data ", data);

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
                            var key = response.data.data;
                            //send the files
                            var process_id = response.data.data.process_id;
                            //var cronid = response.data.data.cron_id;
                            var listFiles = [];
                            for (x in $scope.form.files) {
                                $scope.form.files[x].cron_id = response.data.data.cron_id;
                                listFiles.push($scope.form.files[x]['f']);
                            }
                            //send the files
                            $state.go('^', {}, {
                                reload: true
                            });
                            var urlUploadFile = __env.apiUrl + postUrl + "/" + process_id + '/file';
                            $rootScope.uploadFiles(listFiles,
                                errFiles,
                                process_id,
                                $scope.form.files,
                                urlUploadFile,
                                $scope.keyStorage);
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

            $scope.resubmit = function(errFiles) {
                var urlUploadFile = 'reception/newPolicy/initialDocumentation/' + $scope.process_id + '/file';

                var listFiles = [];
                var error = false;
                for (x in $scope.form.files) {
                    if ($scope.form.files[x].error == 1) {
                        if ($scope.form.files[x]['f'] != undefined) {
                            listFiles.push($scope.form.files[x]['f']);
                        } else {
                            error = true;
                        }
                    } else {
                        listFiles.push({});
                    }
                }

                if (error) {
                    var notificacion = {};
                    notificacion.title = "El formulario tiene fallas";
                    notificacion.body = "Debe resubir todos los archivos con fallas";
                    $scope.showNotification(notificacion);
                    return;
                }

                $state.go('^', {}, {
                    reload: true
                });

                $rootScope.uploadFiles(listFiles,
                    errFiles,
                    $scope.process_id,
                    $scope.form.files,
                    urlUploadFile,
                    $scope.keyStorage);
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
    ])
    .directive('testnewRegister', function() {
        return {
            restrict: 'E',
            templateUrl: 'seguros/emision/upload-file.view.html'
        };
    });
/*.directive('sviewRegister', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/emision/archivos-subidos.view.html'
    };
});*/
