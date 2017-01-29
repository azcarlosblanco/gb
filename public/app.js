angular
.module('ngNova', 
	   ['restangular', 'slideables', 'ui.router', 'ngAnimate', 'cfp.hotkeys',
	    'angular-web-notification', 'selectionModel', 'ngFileUpload', 'ui.bootstrap', 'chart.js'])
// run blocks
.run(function($rootScope,Upload,$timeout,__env,webNotification,$window) {
	$rootScope.errorMsg = {}; 
	$filesErrors=[];

	$rootScope.uploadFiles = function(files, errFiles, process_id, form, urlUpF, keyStorage) {
	    $num_files=0;
		$uploadedfiles=0;
		$errors=false;
	    for(index in files){
	    	if (files[index]) {
	    		$num_files++;
	    	}
	    }
	    a_ts={};
	    var notification = {};
	    for(index in files){
			file=files[index];
	        $rootScope.errFile = errFiles && errFiles[0];
	        var regData=form[index];
	        if (file) {
	        	//indicate file is pending to upload
	        	localStorage.setItem(keyStorage+'/'+process_id+"/"+regData['ts'],
	                    					 1);
	        	a_ts[regData['ts']]=regData;
	        	localStorage.setItem(keyStorage+'/'+process_id+"/files",
	                    					 JSON.stringify(a_ts));
	        	regData['process_id']=process_id;
	        	
	        	var token = localStorage.getItem(__env.tokenst);
	        	var urlWithToken = urlUpF+"?token="+token;
	        	
	        	regData.file=file;
	            file.upload = Upload.upload({
	                url: urlWithToken,
	                data: regData
	            });
	            file.upload.then(function (response) {
	                $timeout(function () {
	                    file.result = response.data;
	                    $uploadedfiles++;
	                    notification.title = "Archivos subidos";
	                    notification.body="Todos los archivos se subieron exitosamente";
	                    
	                    //file was upload, remove item
	                    localStorage.removeItem(keyStorage+'/'+process_id+"/"+
	                    						response.data.data.ts);
	                    if($uploadedfiles==$num_files){
	                    	if($errors==true){
	                    		notification.title = "ERROR SUBIENDO LOS ARCHIVOS";
	                    		notification.body="Algunos archivos tuvieron fallas, por favor revisar";
	                    	}
	                    	$rootScope.showNotification(notification);
	                    }
	                });
	            }, function (response) {
	                if (response.status > 0){
	                    $rootScope.errorMsg[index] = response.status + ': ' + response.data;
	                    $uploadedfiles++;
	                    $errors=true;
	                    //add the file with error to the local storage
	                    if($uploadedfiles==$num_files){
	                    	notification.title = "ERROR SUBIENDO LOS ARCHIVOS";
	                    	notification.body="Algunos archivos tuvieron fallas, por favor revisar";
	                    	$rootScope.showNotification(notification);
	                    }
	                }
	            }, function (evt) {
	                file.progress = Math.min(100, parseInt(100.0 * 
	                                         evt.loaded / evt.total));
	            });
	        }   
	    }
	};

	/*var leavingPageText = "Algunos archivos están aún subiéndose y se perderán";
    window.onbeforeunload = function(event){
    	//event.preventDefault();
    	//if(Upload.isUploadInProgress()){
			//return leavingPageText;
		//}
    }
    /*$rootScope.$on('$destroy', function() {
    	window.onbeforeunload = undefined;
    });
    $rootScope.$on('$locationChangeStart', function(event, next, current) {
        if(!confirm(leavingPageText + "\n\nEstá seguro que quiere refrescar la página?")) {
            event.preventDefault();
        }
    });*/

	$rootScope.showNotification = function(data){
		webNotification.showNotification(data.title, {
            body: data.body,
            icon: 'assets/img/GB.ico',
            onClick: function onNotificationClicked() {
                console.log('Notification clicked.');
            },
            //autoClose: 4000 //auto close the notification after 4 seconds (you can manually close it via hide function)
        }, function onShow(error, hide) {
            if (error) {
                window.alert('Unable to show notification: ' + error.message);
            } else {
                console.log('Notification Shown.');
                setTimeout(function hideNotification() {
                    console.log('Hiding notification....');
                }, 5000);
            }
        });
	}
})
.filter('titleCase', function() {
	return function(s) {
		s = ( s === undefined || s === null ) ? '' : s;
		return s.toString().toLowerCase().replace( /\b([a-z])/g, function(ch) {
			return ch.toUpperCase();
		});
	};
})
;
Date.prototype.toString = function() {
	return this.toUTCString(); //no más timezones!
}