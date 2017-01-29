angular
.module('ngNova')
.controller('AffiliateCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification' ,
                 function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification){

	$scope.url = response.data.data.url;
	$scope.catalog = response.data.data.catalog; /* ? response.data.data.data_section: null;*/
	$scope.form = {};
    $scope.focused = [];
    if(response.data.data.init_values !== undefined){
        $scope.form = response.data.data.init_values;
    }

	$scope.submit = function () {
		if ($scope.form) {
			var data = new FormData();
			console.log($scope.form);
			for (var i in $scope.form) {
				data.append(i,$scope.form[i]);
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
                            notificacion.body = response.status + " - El formulario se envió exitosamente";
                            $scope.showNotification(notificacion);
                            $state.go('^', {}, {reload: true});
                            break;
                        default:
                            notificacion.title = "Formulario enviado";
                            notificacion.body = response.status + " - El formulario se envió exitosamente";
                            $scope.showNotification(notificacion);
                            $state.go('^', {}, {reload: true});
                            break;
                    }
                },
                function errorCallback(response) {
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
.filter('parseDate', function($filter) {
    return function(input) {
        console.log(input);
        var serverdate = new Date(Date.parse(input));
        console.log(serverdate);
        return serverdate; 
    };
})
;
