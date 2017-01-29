angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('QuotationViewCtrl', 
		['Restangular','response','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification',
		function(Restangular, response, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification){
		
		var data = response.data.data;
		$scope.form = data;

}]);