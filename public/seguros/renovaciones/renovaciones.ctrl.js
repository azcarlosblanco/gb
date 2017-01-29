angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('RenovacionesCtrl',
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification){

        $scope.f = null;
		$scope.form = [];

		$scope.errorMsg = {};
		$scope.showSubmit = true;

		$scope.registerFile = function(file){
			// $scope.f.push(file);
            $scope.f = file;
			$scope.form['name'] = file.name;
		};
        //
	    $scope.submit = function (errFiles) {

             var data = new FormData();

             data.append('file', $scope.f);
             data.append('form', $scope.form);


             Restangular.one('/renovations/upload-renovation' + "?token=" + localStorage.getItem(__env.tokenst)).withHttpConfig({transformRequest: angular.identity})
                 .customPOST(data, '', undefined, {'Content-Type': undefined})
                 .then(
                    function successCallback(response) {
                        console.log(response);
                        console.log('success');
                    },
                    function errorCallback(response) {
                         console.log('error');
                    }
                );
		};
}])
.directive('newRenovation', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/renovaciones/nueva-renovacion.html'
    };
});
