angular
.module('ngNova')
.controller('PolicyCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification' ,
                 function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification){

	//$scope.url = response.data.data.url;
	//$scope.catalog = response.data.data.catalog; /* ? response.data.data.data_section: null;*/
	//$scope.form = {};
    //$scope.focused = [];*/
   
    var temp_data = response.data.data;

    $scope.catalog = {};
    $scope.form = {};

    $scope.catalog = temp_data.catalog;
    if(temp_data.policy != undefined){
        $scope.form = temp_data.policy;   
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
