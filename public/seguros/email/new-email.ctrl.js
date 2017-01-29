angular
.module('ngNova')
.controller('EmailCtrl', ['Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification', 'Upload', '$timeout', 'myEmailShareService',
	function(Restangular, $scope, hotkeys, $state, $http, __env, webNotification, Upload, $timeout, myEmailShareService){

  console.log("entro aqui");

  $scope.emailObject = {}
  $scope.emailObject.attachments = [];

    $scope.attachFileFromSystem = function(){
      
    }

    $scope.attachFile = function(file){
        $scope.emailObject.attachments.push({});
        var item = $scope.emailObject.attachments[$scope.emailObject.attachments.length-1];
        item.file = file;
        item.ts = Math.floor(Date.now() / 1000);
        item.name = file.name;
        $scope.uploadFile(item,$scope.errFiles);
    };

    $scope.uploadFile = function(item, errFiles) {
        var token = localStorage.getItem(__env.tokenst);
        var postUrl = "email/uploadAttachament";
        var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

       $scope.errFile = errFiles && errFiles[0];
       file = item['file'];
       if (file) {
           file.upload = Upload.upload({
               url: urlWithToken,
               data: item
           });
           file.upload.then(function (response) {
               $timeout(function () {
                   file.progress = 0;
                   file.error = 0;
                   item.filetemp_id = response.data.data;
               });
           }, function (response) {
               if (response.status > 0){
                   file.error = 1
                   file.progress = 0;
               }
           }, function (evt) {
               file.progress = Math.min(100, parseInt(100.0 * 
                                        evt.loaded / evt.total));
           });
       }   
    }

    $scope.transmistemailInfo = function(emailObject) {
        myEmailShareService.prepForBroadcast(emailObject);
    };

    $scope.transmistemailInfoClose = function(emailObject) {
        myEmailShareService.preForBroadcastClose(emailObject);
    };
        
    $scope.$on('handleEmailBroadcast', function() {
        $scope.emailObject = myEmailShareService.emailObject;
    });


    $scope.saveEmailDetail = function(){
    	  if( Upload.isUploadInProgress() ){
    			$scope.checking = false;
    			alert("Espere a que todos los archivos terminen de subirse");
    			return;
    		}else{
          $scope.emailObject.showPreviewEmail = false;
          for(x in $scope.emailObject.attachments){
              $scope.emailObject.attachList.push($scope.emailObject.attachments[x]['id']);
          }
          for(x in $scope.emailObject.internalattachments){
              $scope.emailObject.internalattachList.push($scope.emailObject.internalattachments[x]['id']);
          }
    			$scope.transmistemailInfo($scope.emailObject);
    		}
    }

    $scope.deleteAttachment = function(item){
      var idx = $scope.attachments.indexOf(item);
      if(idx>-1){
        $scope.attachments.splice(idx,1);
      }
    }

    $scope.closeEmailPreview = function(){
      $scope.emailObject.showPreviewEmail = false;
    }
}])