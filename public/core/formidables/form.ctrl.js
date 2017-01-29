angular
.module('ngNova')
.controller('FormCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification' ,
                 function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification){

	this.sections = response.data.data.sections;
	this.actions = response.data.data.actions;
	this.url = response.data.data.url;
	this.data_fields = response.data.data.data_fields; /* ? response.data.data.data_section: null;*/
	$scope.data = response.data;
	

	$scope.form = {};
	$scope.focused = [];

	$scope.filefields = [];

    $scope.myFunction = function (ref) {
        console.log('entro');
        var token = localStorage.getItem(__env.tokenst);
        $scope.form.token=token;
        if(angular.isElement($scope.form.carrier_id) && 
            $scope.form.carrier_id === null){
            $window.alert('Seleccione mensajero');
        }else{
            $http.get(ref, { 
                params: $scope.form, 
                responseType: 'arraybuffer'
                        })
                .success(function (data) {
                      var file = new Blob([data], {type: 'application/pdf'});
                      var fileURL = URL.createObjectURL(file);
                      window.open(fileURL);

               });
        }
    }

	$scope.submit = function () {
		if ($scope.form) {

			var data = new FormData();
			console.log($scope.form);

			for (var i in $scope.form) {
				data.append(i,$scope.form[i]);
			}

			if ($scope.filefields.length >0 ) {
				for (var i in $scope.filefields) {
					data.append("filefields[]",$scope.filefields[i]);
				}
			}

            var token = localStorage.getItem(__env.tokenst);
			data.append("token",token); // API TOKEN

            var urlWithToken = response.data.data.url+"?token="+token;

            Restangular
            .one(urlWithToken)
            .withHttpConfig({transformRequest: angular.identity})
            .customPOST(data, '', undefined, {'Content-Type': undefined})
			.then(
				function successCallback(response) {
					console.log(response);
                    var notificacion = {};
                    switch(response.status) {
                        case 201:
                            notificacion.title = "Formulario enviado";
                            notificacion.body = "El formulario se envió exitosamente";

                            $scope.showNotification(notificacion);
                            $state.go('^', {}, {reload: true});
                            break;
                        default:
                            notificacion.title = "Formulario enviado";
                            notificacion.body = "El formulario se envió exitosamente";

                            $scope.showNotification(notificacion);
                            $state.go('^', {}, {reload: true});
                            break;
                    }
                },
                function errorCallback(response) {
                    var notificacion = {};
                    var data = "";
                    var notificacion = {};
                    var data = "";
                    if(response.data.message == undefined){
                        for(key in response.data){
                            data = data + key+' : '+response.data[key].join(',')+"\n";
                        }
                    }else{
                        errors = response.data.message.Error;
                        for(key in errors){
                            data = data + key+': '+errors[key]+"\n";
                        }
                        dataErrors = response.data.data;    
                        for(key in dataErrors){
                            data = data + key+': '+dataErrors[key]+"\n";
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


    hotkeys.bindTo($scope)
    .add({
        combo: 'pagedown',
        allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
        description: 'blah blah',
        callback: function() {
            var focused = angular.element(document.activeElement);
            var id = '#section-'+(Number(focused.attr('section'))+1);
            var target = angular.element(document.querySelector(id));
            var field = angular.element(target[0].querySelector('.first'));
            window.scrollTo(0, field[0].offsetTop + 100);
            field[0].focus();
        }
    })
    .add({
        combo: 'pageup',
        allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
        description: 'blah blah',
        callback: function() {
            var focused = angular.element(document.activeElement);
            var id = '#section-'+(Number(focused.attr('section'))-1);
            var target = angular.element(document.querySelector(id));
            var field = angular.element(target[0].querySelector('.first'))[0].focus();
        }
    })
    ;


}])
.directive('formidableField', function(){
    return {
        restrict: 'E',
        templateUrl: 'core/formidables/formidable-field.html'
    };
})
.directive('btnSpinner', function(){
    return {
        restrict: 'E',
        templateUrl: 'core/formidables/btn-spinner.html'
    };
})
.directive('focus', ['$timeout', function($timeout) {
    return {
        scope : {
            trigger : '@focus'
        },
        link : function(scope, element) {
            scope.$watch('trigger', function(value) {
                if (value === "true") {
                    $timeout(function() {
                        element[0].focus();
                    });
                }
            });
        }
    };
}])
.filter('parseDate', function($filter) {
    return function(input) {
        console.log(input);
        var serverdate = new Date(Date.parse(input));
        console.log(serverdate);
        return serverdate; 
    };
})
.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            element.bind('change', function(){
                scope.$apply(function(){
                    scope.filefields.push(element[0].files[0]);
                    var nameField = element[0].name;
                    if(scope[nameField]!==undefined){
                        scope[nameField] = element[0].files[0];
                        console.log(scope[nameField]);
                    }
                });
            });
        }
    };
}])
.directive('searchBar', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            element.bind('change', function(){
                scope.$apply(function(){
                    scope.filefields.push(element[0].files[0]);
                    var nameField = element[0].name;
                    if(scope[nameField]!==undefined){
                        scope[nameField] = element[0].files[0];
                        console.log(scope[nameField]);
                    }
                });
            });
        }
    };
}])
.service('fileUpload', ['$http', function ($http) {
    this.uploadFileToUrl = function(file, uploadUrl){
        var fd = new FormData();
        fd.append('file', file);
        $http.post(uploadUrl, fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        })
        .success(function(){
        })
        .error(function(){
        });
    }
}])
.directive('composeEmail', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/email/new-email.view.html'
    };
})
.directive('addDiagnosis', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/general/new-diagnosis.view.html'
    };
})
.directive('addClaimConcept', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/email/new-concept.view.html'
    };
})
.directive('addDoctor', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/email/new-doctor.view.html'
    };
})
.directive('addHealthSpeciality', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/email/new-health-speciality.view.html'
    };
})
;
