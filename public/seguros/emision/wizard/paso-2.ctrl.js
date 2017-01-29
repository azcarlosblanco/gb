angular
.module('ngNova')
.controller('WizPaso2Ctrl', ['response','Restangular','$scope', 'hotkeys', '$state', '$http', '__env', 'webNotification',
 function(response, Restangular, $scope, hotkeys, $state, $http, __env, webNotification){
	console.log('\n response.data.data ▼')
	console.log(response.data.data); //      //      //      //      //      //      //      //      //
	var temp_data = response.data.data.temp_data ? JSON.parse(response.data.data.temp_data) : {};

	//wizard_steps obj used to create the links in the top of modal
	$scope.current_step = 2;
	$scope.wizard_steps = [];
	$scope.max_step=parseInt(temp_data.max_step);
	var step_data = {"step":1,
					"link":"^.paso-1",
					"icon":"glyphicon-pencil",
					"label":"Solicitante"};
	$scope.wizard_steps.push(step_data);
	//plan
	var step_data2 = {}
	step_data2['icon']="glyphicon-cog";
	step_data2['label']="Plan";
	step_data2['step']=2;
	step_data2['link']="^.paso-2";
	$scope.wizard_steps.push(step_data2);
	//dependiente
	var step_data3 = {}
	step_data3['icon']="glyphicon-user";
	step_data3['label']="Dependientes";
	step_data3['step']=3;
	step_data3['link']="^.paso-3";
	$scope.wizard_steps.push(step_data3);
	//Médico
	var step_data4 = {}
	step_data4['icon']="glyphicon-plus";
	step_data4['label']="Médico";
	step_data4['step']=4;
	step_data4['link']="^.paso-4";
	$scope.wizard_steps.push(step_data4);
	//Pago
	var step_data5 = {}
	step_data5['icon']="glyphicon-usd";
	step_data5['label']="Pago";
	step_data5['step']=5;
	step_data5['link']="^.paso-5";
	$scope.wizard_steps.push(step_data5);
	//Inicio Cobertura
	var step_data6 = {}
	step_data6['icon']="glyphicon-ok-circle";
	step_data6['label']="Inicio Cobertura";
	step_data6['step']=6;
	step_data6['link']="^.paso-6";
	$scope.wizard_steps.push(step_data6);
	//Documentos
	var step_data7 = {}
	step_data7['icon']="glyphicon-file";
	step_data7['label']="Documentos";
	step_data7['step']=7;
	step_data7['link']="^.paso-7";
	$scope.wizard_steps.push(step_data7);
	//end wizard_steps

	var process_ID = response.data.data.process_ID;
	$scope.data = response.data.data;
	$scope.form = {}; temp_data.plan_obj;

	$scope.focused = [];


	for(x in $scope.data.plans){
		var plan = $scope.data.plans[x];
		if(plan.id == temp_data.plan_obj.id){
			$scope.form.id = plan.id;
			for(d in plan.deducibles){
				var deducible = plan.deducibles[d];
				if(deducible.id == temp_data.plan_obj.option){
					$scope.form.option = deducible;
					for( y in deducible.additional_cover){
						var addcover = deducible.additional_cover[y];
						for( cv in addcover.add_cover_value ){
							var addcoverv = addcover.add_cover_value[cv];
							if( temp_data.plan_obj.add_covers!==undefined ) {
								var idx = temp_data.plan_obj.add_covers.indexOf(addcoverv.id);
								if(idx>-1){
									addcoverv.selected = true;
								}
							}
						}
					}
				}
			}
		}
	} 

	$scope.next = function () {
		if ($scope.form) {

			var data = new FormData();

			var token = localStorage.getItem(__env.tokenst);
            data.append("token",token); // API TOKEN

            var obj_send = {};
        	obj_send.id = $scope.form.option.plan_id
        	obj_send.option = $scope.form.option.id;
        	obj_send.add_covers = [];
        	for(y in $scope.form.option.additional_cover){
        		for( z in $scope.form.option.additional_cover[y].add_cover_value ){
					var acv = $scope.form.option.additional_cover[y].add_cover_value[z]; 
        			if(acv.selected!==undefined && acv.selected==true){
        				obj_send.add_covers.push(acv.id);
        			}
        		}
        	}

			data.append("wiz_obj", JSON.stringify(obj_send));

			var	postUrl = "emission/newPolicy/uploadPolicyRequest/form/"+process_ID+"/step/2";
			var urlWithToken = postUrl+"?token="+token;

			Restangular
            .one(urlWithToken)
            .withHttpConfig({transformRequest: angular.identity})
            .customPOST(data, '', undefined, {'Content-Type': undefined})
			.then(
				function successCallback(response) {
					console.log(response);
					$state.go('^.paso-3');
				},
				function errorCallback(response) {
					$scope.checking = false;
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
	};

	$scope.changeDeducible = function(){

	}

	$scope.$watchCollection('checkModel', function () {
	    $scope.checkResults = [];
	    angular.forEach($scope.checkModel, function (value, key) {
	      if (value) {
	        $scope.checkResults.push(key);
	      }
	    });
  	});

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
	        })
	    }
}])
;