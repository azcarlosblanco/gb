angular
.module('ngNova')
.controller('TicketDetailCtrl', ['response','Restangular','$scope', '$window', 'hotkeys', '$state', '$http', '__env', 'webNotification' , 'Upload', '$timeout',
                 function(response, Restangular, $scope, $window, hotkeys, $state, $http, __env, webNotification, Upload, $timeout){
	//$scope.url = response.data.data.url;
	//$scope.catalog = response.data.data.catalog; /* ? response.data.data.data_section: null;*/
	//$scope.form = {};
    //$scope.focused = [];*/

    var temp_data = response.data.data;
    console.log(temp_data);
    $scope.attachments = [];
    $scope.catalog = {};
    $scope.form = {};

    //variables to set ticketdeatildatil
    $scope.form_detail = {};
    $scope.form_detail.qr = [];
    $scope.form_detail.dest = [];
    $scope.form_detail.files = [];
    $scope.errFiles = [];
    $scope.typeTicket = temp_data.typeTicket;
    $scope.ticketDetailDisplay = {'1':"Llamada",'2':"Email",'3':"Respuesta"};

    $scope.formatCommentCall = function(call_script){
        console.log(call_script);
        var call_array = JSON.parse("'"+call_script+"'");
        var text = "";
        for(x in call_array){
            text += "Q: "+call_array[x]['question']+"\n";
            text += "R: "+call_array[x]['replay']+"\n";
        }
        return text;
    }
    if(temp_data.detail != undefined){
        for(x in temp_data.detail.ticketdetail_obj){
            if(temp_data.detail.ticketdetail_obj[x]['type']==$scope.typeTicket['call']){
                var call_array = JSON.parse(temp_data.detail.ticketdetail_obj[x]['comment']);
                var text = "";
                for(y in call_array){
                    text += "Q: "+call_array[y]['question']+"\n";
                    text += "R: "+call_array[y]['replay']+"\n";
                }
                temp_data.detail.ticketdetail_obj[x]['comment'] = text;


            }
        }
        $scope.form = temp_data.detail;
    }
    $scope.formatAttachCall = function(files_script){
        console.log(files_script);
        var files_array = JSON.parse("'"+files_script+"'");
        var text = "";
        for(x in files_array){
            text += "id: "+files_array[x]['id']+" "+"name: "+files_array[x]['name']+"\n";

        }
        return text;
    }
    if(temp_data.detail != undefined){
        for(x in temp_data.detail.ticketdetail_obj){
            if(temp_data.detail.ticketdetail_obj[x]['type']==$scope.typeTicket['call']){
                var files_array = temp_data.detail.ticketdetail_obj[x]['files'];
                var text = "";
                 for(j in files_array){
                  text += "id: "+files_array[j]['id']+" "+"name: "+files_array[j]['name']+"\n";

                 }
                temp_data.detail.ticketdetail_obj[x]['files'] = text;
            }
        }
        $scope.form = temp_data.detail;
    }
    $scope.formatmail = function(email_script){
        console.log(email_script);
        var email_array = JSON.parse("'"+email_script+"'");
        var text = "";
        for(x in email_array){
            text += copy_array[x]['mail']+"\n";

        }
        return text;
    }
    if(temp_data.detail != undefined){
        for(x in temp_data.detail.ticketdetail_obj){
            if(temp_data.detail.ticketdetail_obj[x]['type']==$scope.typeTicket['email']){
                var email_array = temp_data.detail.ticketdetail_obj[x]['email'];
                var text = "";
                 for(j in email_array){
                  text += email_array[j]['mail']+"\n";

                 }
                temp_data.detail.ticketdetail_obj[x]['email'] = text;

            }
        }
        $scope.form = temp_data.detail;
    }
    $scope.formatcopy = function(copy_script){
        console.log(copy_script);
        var copy_array = JSON.parse("'"+copy_script+"'");
        var text = "";
        for(x in copy_array){
            text += copy_array[x]['cc']+"\n";

        }
        return text;
    }
    if(temp_data.detail != undefined){
        for(x in temp_data.detail.ticketdetail_obj){
            if(temp_data.detail.ticketdetail_obj[x]['type']==$scope.typeTicket['email']){
                var copy_array = temp_data.detail.ticketdetail_obj[x]['copy'];
                var text = "";
                 for(j in copy_array){
                  text += copy_array[j]['cc']+"\n";

                 }
                temp_data.detail.ticketdetail_obj[x]['copy'] = text;

            }
        }
        $scope.form = temp_data.detail;
    }
	$scope.showCreateCall  = false;
    $scope.showAddQuestion = false;
    $scope.showCreateEmail = false;
    $scope.showEndTicket   = false;
    $scope.showClaim       = false;
    $scope.showRegReplay   = false;

    $scope.createCall = function(){
        $scope.showCreateCall= true;
        Restangular
        .one('ticket/csQuestion')
        .get("")
        .then(
            function successCallback(response) {
                $scope.catalog.question = response.data.data.question;
                console.log($scope.catalog);
            },
            function errorCallback(response) {

            }
        );
    }

    $scope.createEmail = function(id){
        $scope.showCreateEmail = true;
        Restangular
        .one('ticket/csEmail',id)
        .get("")
        .then(
            function successCallback(response) {
               $scope.form_detail.email = response.data.data.email;
                console.log($scope.catalog);
            },
            function errorCallback(response) {

            }
        );

    }
    $scope.registerReplay = function(id){
        $scope.showRegReplay = true;

    }
    $scope.closeCreateEmail = function(){
        $scope.showCreateEmail = false;
    }

   $scope.closeCreateCall=function(){
       $scope.showCreateCall = false;
    }
   $scope.closeEndTicket=function(){
       $scope.showEndTicket = false;
   }
   $scope.closeClaim = function(){
     $scope.showClaim = false;
   }
    $scope.addQuestion = function(){
        $scope.showAddQuestion = true;
    }
    $scope.closeAddQuestion = function(){
        $scope.showAddQuestion = false;
    }

    $scope.submitQuestion = function(errFiles){
        var postUrl = "ticket/csQuestion";
        var token = localStorage.getItem(__env.tokenst);
        var urlWithToken = __env.apiUrl+postUrl+"?token="+token;
        var data = new FormData();
        for(x in $scope.form){
            data.append(x,$scope.form[x]);
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
                var notificacion = {};
                notificacion.title = "Formulario enviado";
                notificacion.body = "";
                $state.go('^', {}, {reload: true});
                var msg = response.data.message.Success;
                for(key in msg){
                    notificacion.body = notificacion.body + key+' '+msg[key];
                }
                $scope.showNotification(notificacion);
                $state.go('^', {}, {reload: true});
                console.log(response.data);
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
    $scope.addAttachment = function(){
        $scope.attachments.push({});
    }
    $scope.addAttachment();
    $scope.addQuestionReply = function(){
        $scope.form_detail.qr.push({});
    }
    $scope.addEmail = function(){
        $scope.form_detail.dest.push({});
    }
    $scope.addQuestionReply();
    $scope.addEmail();
    $scope.endTicket = function()
    {
      $scope.showEndTicket = true;
    }

    $scope.Claim = function(id){
      $scope.showClaim = true;

      Restangular
      .one('ticket/claim',id)
      .get("")
      .then(
          function successCallback(response) {
            var data = response.data.data;

            $scope.form_detail.policy      = data.policy;
            $scope.form_detail.claim       = data.claim.id;
            $scope.form_detail.description = data.claim.description;
            $scope.form_detail.amount      = data.claim.amount;
            $scope.form_detail.type        = data.claim.type;
            $scope.form_detail.types       = data.claim.types;
            $scope.form_detail.currency    = data.claim.currency;
            $scope.form_detail.currencies  = data.claim.currencies;

            $scope.selected = $scope.form_detail.types[$scope.form_detail.type.id-1];
            $scope.selected_currency = $scope.form_detail.currencies[$scope.form_detail.currency-1];
          },
          function errorCallback(response) {
              console.log(response);
          }
      );
    }

    $scope.editClaim = function(form){
      console.log(form);

      var postUrl = "ticket/claim/"+form['claim']+"/update";

      var data = new FormData();
      data.append("policy",form['policy']);
      data.append("claim",form['claim']);
      data.append("type",$scope.selected.id);
      data.append("amount",$scope.form_detail.amount);
      data.append("currency",$scope.selected_currency.id);
      data.append("description",$scope.form_detail.description);

      Restangular
      .one(postUrl)
      .withHttpConfig({transformRequest: angular.identity})
      .customPOST(data, '', undefined, {'Content-Type': undefined})
      .then(
          function successCallback(response) {
            console.log(response);
             var notificacion={};
              notificacion.title = "Reclamo actualizado!";
              notificacion.body = "El reclamo se actualizo exitosamente";
              $scope.showNotification(notificacion);
            /*  $state.go('^', {}, {reload: true});*/
          },
          function errorCallback(response) {
              /*var notificacion = {};
              var data = "";
              var notificacion = {};
              var data = "";
              if(response.data.message == undefined){
                  for(key in response.data){
                      data = data + key+' : '+response.data[key].join(',')+"\n";
                  }
              }else{
                  errors = response.data.message.Error;
                  for(key in errors){
                      data = data + key+': '+errors[key]+"\n";
                  }
                  dataErrors = response.data.data;
                  for(key in dataErrors){
                      data = data + key+': '+dataErrors[key]+"\n";
                  }
              }*/
              notificacion.title = "Error al enviar el formulario";
              notificacion.body = response;
              $scope.showNotification(notificacion);
          }
      );

      console.log(data);
    }
    /*function close ticket */
    $scope.closeTicket=function ()
    {
        ticketID = $scope.form.ticket_id;
        var postUrl = "ticket/"+ticketID+"/endTicket";
            console.log(2);
        var token = localStorage.getItem(__env.tokenst);
        var urlWithToken = __env.apiUrl+postUrl+"?token="+token;
        var data = new FormData();
        for(x in $scope.form){
            data.append(x,$scope.form[x]);
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
                var notificacion = {};
                notificacion.title = "Ticket Cerrado";
                $state.go('^', {}, {reload: true});
                notificacion.body = "";
                var msg = response.data.message.Success;
                for(key in msg){
                    notificacion.body = notificacion.body + key+' '+msg[key];
                }
                $scope.showNotification(notificacion);
                $state.go('^', {}, {reload: true});
                console.log(response.data);
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
    //*** functions to add attachment to the ticket detail to Call ***//
    $scope.registerFile = function(file,item){
        item.file = file;
        item.ts = Math.floor(Date.now() / 1000);
        item.name = file.name;
        $scope.uploadFile(item,$scope.errFiles);
    };
    $scope.uploadFile = function(item, errFiles) {
        var token = localStorage.getItem(__env.tokenst);
        var postUrl = "ticket/uploadFile";
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
                   $scope.attachments.push({});
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
    //*** functions to add attachment to the ticket detail to Email ***//
    $scope.registerFileCall = function(file,item){
        item.file = file;
        item.ts = Math.floor(Date.now() / 1000);
        item.name = file.name;
        item.description = file.description
        $scope.uploadFile(item,$scope.errFiles);
    };
    /*Function to delete question and replay*/
    $scope.deleteQuestion= function(form_detail, deleteView=true){
        var indx=$scope.form_detail.qr.indexOf(form_detail);
          if(indx == -1){
            return;
        }
        if(deleteView){
            $scope.form_detail.qr.splice(indx,1);
        }else{
            $scope.form_detail.qr[indx] = {};
        }
    }
    /*function to delete file */
    $scope.deleteFile = function(attachment,deleteView=true){
        var idx=$scope.attachments.indexOf(attachment);
        var data = new FormData();
        var postUrl = "ticket/deleteFile";
        var token = localStorage.getItem(__env.tokenst);
        var urlWithToken = __env.apiUrl+postUrl+"?token="+token;
        if(idx == -1){
            return;
        }
        if(attachment.file!=undefined){
            data.append("filetemp_id",attachment.filetemp_id);
            for(x in $scope.attachments){
                data.append(x,$scope.attachments[x]);
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
                    var notificacion = {};
                    notificacion.title = "Archivo Eliminado";
                    notificacion.body = "";
                    var msg = response.data.message.Success;
                    for(key in msg){
                        notificacion.body = notificacion.body + key+' '+msg[key];
                    }
                    console.log(response.data);
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
        //delete the attachment form list of files
        if(deleteView){
            $scope.attachments.splice(idx,1);
        }else{
            $scope.attachments[idx] = {};
        }
    }
    $scope.submitCallDetail = function(){
        ticketID = $scope.form.ticket_id;
        urlApi = "ticket/"+ticketID+"/detail";
         if(Upload.isUploadInProgress()){
            alert("Espere a que los archivo termines de subirse");
            return;
        }
        var data = new FormData();
        data.append("type",$scope.typeTicket["call"]);
        data.append("comment",JSON.stringify($scope.form_detail.qr));
        data.append("policy_id",$scope.form.policy_id);
        var files = [];
        var files_desc = [];
        for(x in $scope.attachments){
            files.push($scope.attachments[x]['file']);
            files_desc.push($scope.attachments[x]['description']);
        }
        data.append("files[]",files);
        data.append("description_files[]",files_desc);
        var listAttachIds = [];
        console.log($scope.attachments);
        for(x in $scope.attachments){
            if($scope.attachments[x].filetemp_id!=undefined){
                listAttachIds.push($scope.attachments[x].filetemp_id);
            }
        }
        data.append("listIds[]",listAttachIds);
        Restangular
        .one(urlApi)
        .withHttpConfig({transformRequest: angular.identity})
        .customPOST(data, '', undefined, {'Content-Type': undefined})
        .then(
            function successCallback(response) {
               var notificacion={};
                notificacion.title = "La llamada se registro";
                notificacion.body = "";
                $scope.showNotification(notificacion);
                $state.go('^', {}, {reload: true});
            },
            function errorCallback(response) {
                var notificacion = {};
                var data = "";
                var notificacion = {};
                var data = "";
                if(response.data.message == undefined){
                    for(key in response.data){
                        data = data + key+' : '+response.data[key].join(',')+"\n";
                    }
                }else{
                    errors = response.data.message.Error;
                    for(key in errors){
                        data = data + key+': '+errors[key]+"\n";
                    }
                    dataErrors = response.data.data;
                    for(key in dataErrors){
                        data = data + key+': '+dataErrors[key]+"\n";
                    }
                }
                notificacion.title = "Error al enviar el formulario";
                notificacion.body = data;
                $scope.showNotification(notificacion);
            }
        );
    }
    $scope.submitEmail = function(){
        ticketID = $scope.form.ticket_id;
        urlApi = "ticket/"+ticketID+"/detail";
        if(Upload.isUploadInProgress()){
            alert("Espere a que los archivo termines de subirse");
            return;
        }
        var data = new FormData();
        console.log($scope.typeTicket);
        data.append("comment",$scope.form_detail.content);
        data.append("type",$scope.typeTicket["email"]);
        data.append("email",$scope.form_detail.email);
        data.append("copy",$scope.form_detail.copy);
        console.log('here');
        var listAttachIds = [];

        console.log($scope.attachments);
        for(x in $scope.attachments){
            if($scope.attachments[x].filetemp_id!=undefined){
                listAttachIds.push($scope.attachments[x].filetemp_id);
            }
        }

        data.append("listIds[]",listAttachIds);
        Restangular
        .one(urlApi)
        .withHttpConfig({transformRequest: angular.identity})
        .customPOST(data, '', undefined, {'Content-Type': undefined})
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
                var notificacion = {};
                var data = "";
                if(response.data.message == undefined){
                    for(key in response.data){
                        data = data + key+' : '+response.data[key].join(',')+"\n";
                    }
                }else{
                    errors = response.data.message.Error;
                    for(key in errors){
                        data = data + key+': '+errors[key]+"\n";
                    }
                    dataErrors = response.data.data;
                    for(key in dataErrors){
                        data = data + key+': '+dataErrors[key]+"\n";
                    }
                }
                notificacion.title = "Error al enviar el formulario";
                notificacion.body = data;
                $scope.showNotification(notificacion);
            }
        );
    }
    $scope.submitReplay = function(){
        ticketID = $scope.form.ticket_id;
        urlApi = "ticket/"+ticketID+"/detail";
        var data = new FormData();
        data.append("type",$scope.typeTicket["replay"]);
        data.append("comment",$scope.form.replay);
        data.append("ticket_id",$scope.form.ticket_id);
        console.log(data);
        Restangular
        .one(urlApi)
        .withHttpConfig({transformRequest: angular.identity})
        .customPOST(data, '', undefined, {'Content-Type': undefined})
        .then(
            function successCallback(response) {
                var notificacion={};
                notificacion.title = "La respuesta se registro";
                notificacion.body = "";
                $scope.showNotification(notificacion);
                $state.go('^', {}, {reload: true});
            },
            function errorCallback(response) {
                var notificacion = {};
                var data = "";
                var notificacion = {};
                var data = "";
                if(response.data.message == undefined){
                    for(key in response.data){
                        data = data + key+' : '+response.data[key].join(',')+"\n";
                    }
                }else{
                    errors = response.data.message.Error;
                    for(key in errors){
                        data = data + key+': '+errors[key]+"\n";
                    }
                    dataErrors = response.data.data;
                    for(key in dataErrors){
                        data = data + key+': '+dataErrors[key]+"\n";
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
