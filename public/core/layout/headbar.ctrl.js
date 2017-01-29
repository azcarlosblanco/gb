var hdbr = {
    "message": [],
    "data": {
        "project": "ngNova",
        "user": "Juan Torbay",
        "startUrl": "root.seguros.poliza",
        "modules": [{
            "label": "Seguros",
            "url": ".seguros",
            "icon": "glyphicon"
        }]
    },
    "route": "#"
};

angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('HeadbarController', 
    ['$scope', '$state', '__env', 'Restangular', 'novaNotification',
    function($scope, $state, __env, Restangular, novaNotification){
    this.data = hdbr.data;

    this.logout = function (){
        //call back to invalidate the token
        var token = localStorage.getItem(__env.tokenst);
        var urlWithToken = "auth/logout?token="+token;

        Restangular
        .one(urlWithToken)
        .withHttpConfig({transformRequest: angular.identity})
        .customPOST(this.data, '', undefined, {'Content-Type': undefined})
        .then(
            function successCallback(response) {
                //remove teh token from internal storage
                localStorage.removeItem(__env.tokenst);
                //redirect to login page
                $state.go('login', {}, {reload: true});
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
                novaNotification.showNotification(notificacion);
            }
        );
    }
}])

;