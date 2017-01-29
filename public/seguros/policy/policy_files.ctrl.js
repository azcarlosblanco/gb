angular
.module('ngNova')
.controller('PolicyFileCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification' ,
                 function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification){

    var temp_data = response.data.data;
    $scope.ntoken = localStorage.getItem(__env.tokenst);
    $scope.files = temp_data.files;

}])
