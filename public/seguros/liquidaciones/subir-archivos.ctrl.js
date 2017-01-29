angular
.module('ngNova')
.controller('LiquidacionesSubirArchivos', 
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout','webNotification',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout,webNotification){
		$scope.f = []; 
		$scope.form = [];
		$scope.registers = [];
		$scope.uregisters = [];
		$scope.errorMsg = {};
		$scope.process_id = null;
		$scope.keyStorage = 'settlement';
		$scope.ntoken = localStorage.getItem(__env.tokenst);

		//
		//{ 
		//	claim:{id:1, affiliate_name:rocio, policy_num:1}, 
		//  files:[
		//  		{id:1, data:'asdad', status:1, original_filename:'filename'}, 
		//        	{id:1, data:'asdad', status:1, original_filename:'filename'}
		//        ] 
		//}
		var temp_data = response.data.data;
		$scope.temp_data = response.data.data;
		$scope.claim_info = temp_data.claim;
		$scope.ufiles = temp_data.files;
		$scope.process_id = temp_data.process_id;

		$scope.registers.push({});	

		//get data from local storage to retrive files that wasnt uploaded
		var storage_files=localStorage.getItem($scope.keyStorage+'/'+$scope.process_id
													+"/files");
		if(storage_files != undefined){
			var storage_files=JSON.parse(storage_files);
			for(x in storage_files){
				var error = localStorage.getItem($scope.keyStorage+
											'/'+$scope.process_id+
											"/"+x);
				if(error==1){
					console.log(storage_files[x]);
					storage_files[x]['error']=true;
					$scope.ufiles.push(storage_files[x]);
				}
			}
		}

		//get file that are in process of been updated or have failed
		$scope.addRegister = function () {
			$scope.registers.push({});
	    };

	    $scope.deleteRegister = function (item) {
	    	var index=$scope.registers.indexOf(item);
	    	$scope.form.splice(index,1);
	    	$scope.f.splice(index,1);
	    	$scope.registers.splice(index,1);
	    };

		$scope.registerFile = function(file,index){
			var ts=Math.floor(Date.now() / 1000);
			if($scope.f[index] === 'undefined'){
				$scope.f.push(file);
				$scope.form[index]['ts']=ts;
				$scope.form[index]['name']=file.name;
			}else{
				$scope.f[index]=file;
				$scope.form[index]['ts']=ts;
				$scope.form[index]['name']=file.name;
			}
			console.log(index);
		};

		$scope.submit = function(errFiles){
			$state.go('^', {}, {reload: true});
			var urlUploadFile = 'reception/settlements/upload/'+$scope.process_id;
			$rootScope.uploadFiles($scope.f, 
									errFiles, 
									$scope.process_id, 
									$scope.form, 
									urlUploadFile,
									$scope.keyStorage);
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
}])
.directive('snewRegister', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/liquidaciones/registro-subir-archivo.view.html'
    };
})
.directive('sviewRegister', function(){
    return {
        restrict: 'E',
        templateUrl: 'seguros/liquidaciones/archivos-subidos.view.html'
    };
});