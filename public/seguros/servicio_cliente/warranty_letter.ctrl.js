angular
.module('ngNova')
.controller('WarrantyLetterCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification' , 'Upload', '$timeout',
                 function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification, Upload, $timeout){
	//$scope.url = response.data.data.url;
	//$scope.catalog = response.data.data.catalog; /* ? response.data.data.data_section: null;*/
	//$scope.form = {};
    //$scope.focused = [];*/
   
    var temp_data = response.data.data;
    console.log('here');
    $scope.attachment = [];
    $scope.catalog = {};
    $scope.form = {};
   
    $scope.form = temp_data.detail;
    $scope.showCreateEmail  = false;
    
    $scope.addAttachment = function(){
        $scope.attachment.push({});
    }
    $scope.addAttachment();

    //function to close email
    $scope.closeCreateEmail = function(){
           $scope.showCreateEmail = false;  
         }

     //*** functions to add attachment t ***//
    $scope.registerFile = function(file,item){
        item.file = file;
        item.ts = Math.floor(Date.now() / 1000);
        item.name = file.name;
        $scope.uploadFile(item,$scope.errFiles);
    };

    $scope.uploadFile = function(item, errFiles) {
        var token = localStorage.getItem(__env.tokenst);
        var postUrl = "pending/uploadFile";
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
                   $scope.attachment.push({});
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

    $scope.submit = function(errFiles){

           correctLetter = $scope.form.not;
           if(correctLetter == 1)
             {
                var postUrl = "pending/observations";
                var token = localStorage.getItem(__env.tokenst);
                var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

                var data = new FormData();          

            for(x in $scope.form){
                data.append(x,$scope.form[x]);
                data.append("table_type","emergency");
                data.append("table_id", $scope.form.id);           
            }
            $http.post(
                urlWithToken,
                data, 
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }
            )
            .then(
                function successCallback(response) {
                    if($scope.attachment.filetemp_id!=undefined)
                    {
                        procedureID = $scope.form.id;  
                        $scope.showCreateEmail  = true;
                        Restangular
                        .one('pending/emailAgent/'+procedureID)
                        .get("")
                        .then(
                            function successCallback(response) {
                                $scope.form.copy = response.data.data.copy;
                            console.log($scope.catalog);
                            },
                            function errorCallback(response) {
                    
                            }
                        );    
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
           else
            {
                procedureID = $scope.form.id;

                var postUrl = "pending/closeTicket/"+procedureID;
                var token = localStorage.getItem(__env.tokenst);
                var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

                var data = new FormData();          

            $http.post(
                urlWithToken,
                data, 
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }
            )
            .then(
                function successCallback(response) {

                    if($scope.attachment.filetemp_id!=undefined)
                    {
                        procedureID = $scope.form.id;  
                        $scope.showCreateEmail  = true;
                        Restangular
                        .one('pending/emailAgent/'+procedureID)
                        .get("")
                        .then(
                            function successCallback(response) {
                                $scope.form.copy = response.data.data.copy;
                            console.log($scope.catalog);
                            },
                            function errorCallback(response) {
                    
                            }
                        );    
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
    }   

    ///function to send email 

        $scope.submitEmail = function(errFiles){

            var postUrl = "pending/email";
            var token = localStorage.getItem(__env.tokenst);
            var urlWithToken = __env.apiUrl+postUrl+"?token="+token;
            if(Upload.isUploadInProgress()){
                alert("Espere a que los archivo termines de subirse");
                return;
            }
                var data = new FormData();
                data.append("content",$scope.form.content);
                data.append("email",$scope.form.email);
                data.append("copy",$scope.form.copy);
                console.log('here');
                var listAttachIds = [];
                
                console.log($scope.attachment);
                for(x in $scope.attachment){
                    if($scope.attachment[x].filetemp_id!=undefined){
                        listAttachIds.push($scope.attachment[x].filetemp_id);
                    }
                }
                
                data.append("listIds[]",listAttachIds);         
            
            $http.post(
                urlWithToken,
                data, 
                {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }
            )
            .then(
                function successCallback(response) {
                 var notificacion={};
                 notificacion.title = "El correo se envio";
                 notificacion.body = "";
                 $scope.showNotification(notificacion);
                 $state.go('^', {}, {reload: true});
 
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