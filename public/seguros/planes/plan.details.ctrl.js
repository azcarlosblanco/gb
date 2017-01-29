angular
.module('ngNova')
.controller('PlanDetailsCtrl', 
       ['Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', '$timeout', 'webNotification',
       function(Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, $timeout, webNotification){

   $scope.planes = [
       {
           planName: "Opcion 1",
           outEEUU: 500.00,
           inEEUU: 1000.00,
           ageRanges: [
               {
                   age: "18-24",
                   yearly: 1901,
                   semesterly: 1008,
                   trimesterly: 950
               }
           ]
           
       },
       {
           planName: "Opcion 2",
           outEEUU: 2000.00,
           inEEUU: 2000.00,
           ageRanges: [
               {
                   age: "18-24",
                   yearly: 1901,
                   semesterly: 1008,
                   trimesterly: 950
               }
           ]
           
       },
       {
           planName: "Opcion 3",
           outEEUU: 5000.00,
           inEEUU: 5000.00,
           ageRanges: [
               {
                   age: "18-24",
                   yearly: 1901,
                   semesterly: 1008,
                   trimesterly: 950
               }
           ]
           
       },
       {
           planName: "Opcion 4",
           outEEUU: 10000.00,
           inEEUU: 10000.00,
           ageRanges: [
               {
                   age: "18-24",
                   yearly: 1901,
                   semesterly: 1008,
                   trimesterly: 950
               }
           ]
           
       },
       {
           planName: "Opcion 5",
           outEEUU: 20000.00,
           inEEUU: 20000.00,
           ageRanges: [
               {
                   age: "18-24",
                   yearly: 1901,
                   semesterly: 1008,
                   trimesterly: 950
               }
           ]
           
       }


   ];

	$scope.planOptionSelected = function (inx) {
		console.log(inx);
		$scope.optionSelected = $scope.planes[inx];
	}
}]);