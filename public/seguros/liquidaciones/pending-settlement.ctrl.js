angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('PendingSettlementCtrl', 
		['response','Restangular','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification', 'myEmailShareService',
		function(response, Restangular, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification, myEmailShareService){ 
			
			$scope.ntoken = localStorage.getItem(__env.tokenst);

			$scope.claim = response.data.data.claim;
			$scope.payments_method = response.data.data.payments_method;
			$scope.currencies = response.data.data.currencies;
			$scope.categories = response.data.data.categories;
			$scope.aff_deduct = response.data.data.affiliate_deductibles;
			$scope.global_deduct = response.data.data.global_deductibles;
			$scope.policy_deduct = response.data.data.policy_deductibles;
			$scope.showRegisterRefund = false;
			$scope.showCreateTicket = false
			var process_ID = response.data.route.substring(25);
			$scope.notification = {};
			$scope.beneficiary_types = {"0":"Afiliado","1":"Proveedor"};
			$scope.checking_refund = false;
			$scope.deduct_familiar = response.data.data.deduct_familiar;

			$scope.showClaimFiles = false;

			$scope.emailObject = {};
			$scope.emailObject.urlgetdata = "settlement/registerForm/"+process_ID+"/emaildata";
			$scope.emailObject.firstTimeEmail = true;
			$scope.emailObject.showPreviewEmail = false;

			$scope.ticket = {};

			$scope.url_print_letter = {};


			function computeTotal(invoice){
				return ( invoice.settlement.amount -
	            							invoice.settlement.uncovered - 
	            							invoice.settlement.deduct - 
	            							invoice.settlement.discount - 
	            							invoice.settlement.coaseguro
	            						   );
			}

			$scope.invoices = [];
			//files that have not been settled yet
			for(x in response.data.data.not_associated){
				var invoice = response.data.data.not_associated[x];
				invoice.dirty = 0;
				invoice.saved = 0;
				invoice.settlement = {};
				invoice.settlement.amount = invoice.amount;
				invoice.settlement.uncovered = 0.00;
				invoice.settlement.coaseguro = 0.00;
				invoice.settlement.discount = 0.00;
				invoice.settlement.deduct = 0.00;
				invoice.settlement.total = computeTotal(invoice);
				invoice.file_entry_id = invoice.file_entry_id;
				$scope.invoices.push(invoice);
			}

			$scope.refunds = [];
			//files that have been already settled 
			for(x in response.data.data.settlements){
				var invoice = response.data.data.settlements[x];
				invoice.dirty = 0;
				invoice.saved = 1;
				invoice.error_deduct = 0;
				invoice.settlement.amount = invoice.settlement.amount;
				invoice.settlement.uncovered = invoice.settlement.uncovered_value;
				invoice.settlement.coaseguro = invoice.settlement.coaseguro;
				invoice.settlement.discount = invoice.settlement.descuento;
				invoice.settlement.deduct = invoice.settlement.deducible;
				invoice.settlement.total = invoice.settlement.refunded;
				invoice.settlement.claim_num = invoice.settlement.ic_num_claim;
				invoice.file_entry_id = invoice.file_entry_id;
				if(invoice.settlement.expected_refund != invoice.settlement.refunded){
					invoice.error_deduct = 1;
				}
				if(invoice.refunds.length>0){
					for(x in invoice.refunds){
						var refund = invoice.refunds[x];
						refund.document = invoice.description;
						$scope.refunds.push(refund);
					}
				}
				$scope.invoices.push(invoice);
			}

			console.log($scope.invoices);

			$scope.claimFiles = []
			for(x in response.data.data.allfiles){
				var claimFile = response.data.data.allfiles[x];
				$scope.claimFiles.push(claimFile);
			}


			$scope.currentInvoiceIdx = null;

			$scope.parseErrorMsg = function(response_data){
	            var data = "";
	            if(response_data.data.message == undefined){
	                for(key in response_data.data){
	                    data = data + key+' : '+response_data.data[key].join(',')+"\n";
	                }
	            }else{
	                errors = response_data.data.message.Error;
	                for(key in errors){
	                    data = data + key+': '+errors[key]+"\n";
	                }
	                dataErrors = response_data.data.data;    
	                for(key in dataErrors){
	                    data = data + key+': '+dataErrors[key]+"\n";
	                }
	            }
	            return data;
	        }

			$scope.calculateTotal = function(invoice){
				invoice.dirty = 1;
				invoice.settlement.total = computeTotal(invoice);
			}

			
			
			$scope.submitSettlement = function(invoice){

				/*if($scope.form.files.length == 0){
					alert("Debe asociar archivos a la liquidación");
					return;
				}*/
				$scope.checking_settlement = true;
				/*if($scope.currencies[invoice.currency_id]=='USD'){
					$scope.settlement.amount = invoice.amount;
				}*/

				var data = new FormData();
	            data.append('cfid', invoice.id);
	            data.append('claim_num', invoice.settlement.claim_num);
	            data.append('amount', invoice.settlement.amount);
	            data.append('uncovered', invoice.settlement.uncovered);
	            data.append('dscto', invoice.settlement.discount);
	            data.append('deducible', invoice.settlement.deduct);
	            data.append('coaseguro', invoice.settlement.coaseguro);
	            invoice.settlement.total = computeTotal(invoice);
	            data.append('refund', invoice.settlement.total);
	            //data.append('files', JSON.stringify($scope.form.files));

				
				var postUrl = "settlements/registerForm/"+process_ID;

				if( invoice.settlement.claim_num==undefined || invoice.settlement.claim_num=="" ){
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "El campo # reclamo es obligatorio";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if( invoice.settlement.total==null || invoice.settlement.total < 0) {
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "Los valores registrados no están correctos";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(invoice.settlement.amount==null){
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "El valour registro en 'Monto' no es correcto";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(invoice.settlement.uncovered==null){
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "El valour registro en 'No cubierto' no es correcto";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(invoice.settlement.discount==null){
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "El valour registro en 'Descuento' no es correcto";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(invoice.settlement.deduct==null){
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "El valour registro en 'Deduct' no es correcto";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(invoice.settlement.coaseguro==null){
					$scope.checking_settlement = false;
                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
                    $scope.notification.body = "El valour registro en 'Coaseguro' no es correcto";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

		        Restangular
		        .one(postUrl)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customPOST(data, undefined, undefined, {'Content-Type': undefined})
		        .then(
		            function successCallback(response) {
		            	$scope.checking_settlement = false;
		            	invoice.saved = 1;
		            	invoice.dirty = 0;
		            	
		            	invoice.settlement.expected_deduct = response.data.data.expected_deduct;
		            	invoice.settlement.expected_refund = response.data.data.expected_refund;
		            	invoice.settlement.id = response.data.data.id;

		            	console.log(invoice.settlement.expected_deduct+","+invoice.settlement.deduct);
		            	if(invoice.settlement.expected_deduct != invoice.settlement.deduct){
		            		invoice.error_deduct = 1;
		            	}else{
		            		invoice.error_deduct = 0;
		            	}
		            	console.log(invoice.settlement.expected_refund+","+invoice.settlement.total);
		            	if(invoice.settlement.expected_refund != invoice.settlement.total){
		            		invoice.error_deduct = 1;
		            	}else{
		            		invoice.error_deduct = 0;
		            	}
		            	
		            	//update values of deductibles
		            	$scope.aff_deduct = response.data.data.affiliate_deductibles;
						$scope.global_deduct = response.data.data.global_deductibles;

		            	$scope.notification.title = "Liquidación "+invoice.description;
	                    $scope.notification.body = "La liquidación fue registrada exitosamente";
	                    novaNotification.showNotification($scope.notification);
		            },
		            function errorCallback(response) {
		            	$scope.checking_settlement = false;
	                	$scope.notification.title = "Error registrar liquidación "+invoice.description;
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
		            }
		        );
			}

			$scope.registerRefund = function(invoice){
				if(invoice.saved==0){
					alert("Debe guardar la liquidación primero");
					return;
				}
				if(invoice.dirty==1){
					alert("Debe guardar los cambios en la liquidación primero");
					return;
				}

				var idx = $scope.invoices.indexOf(invoice);
				$scope.currentInvoiceIdx = idx;
				$scope.showRegisterRefund = true;
				$scope.refund = {};
				$scope.refund.set_id = invoice.settlement.id;

				//ask back if settlement have been refunded
				/*$scope.checking_refund = true;
				Restangular
		        .one("settlements/registerPayment",invoice.settlement.id)
		        .get("")
		        .then(
		            function successCallback(response) {
		            	$scope.checking_refund = false;
		            	var payments = response.data.data;
		            	for(x in payments){
		            		var refund = {};
		            		$scope.refund.id = payments[x].id;
		            		$scope.refund.set_id = payments[x].claim_settlement_id;
		            		$scope.refund.paid = payments[x].value;
		            		$scope.refund.method = payments[x].payment_method_id+"";
		            		$scope.refund.reference_number = parseInt(payments[x].reference_number);
		            		$scope.refund.beneficiary_type = payments[x].to_supplier+"";
		            		$scope.refund.date = new Date(Date.parse(payments[x].pay_date));
		            	}
		            },
		            function errorCallback(response) {
		            	$scope.checking_refund = false;
		            	$scope.notification.title = "Error al caragar las ódenes anteriores";
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
		            }
		        );*/

			}

			$scope.submitRegisterRefund = function(refund){
				//send the refund to the back
				$scope.checking_refund = true;

				var data = new FormData();
	            data.append('set_id', refund.set_id);
	            data.append('paid', $scope.invoices[$scope.currentInvoiceIdx]['settlement']['total']);
	            data.append('pay_method', refund.method);
	            data.append('reference_number', refund.reference_number);
	            data.append('to_supplier', refund.beneficiary_type);
	            data.append('pay_date', refund.date);

				var postUrl = "settlements/registerPayment/"+process_ID;

		        Restangular
		        .one(postUrl)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customPOST(data, '', undefined, {'Content-Type': undefined})
		        .then(
		            function (response) {
		            	$scope.checking_refund = false;
		            	$scope.showRegisterRefund = false;
		            	refundIserted = response.data.data;
		            	refundIserted.document = $scope.invoices[$scope.currentInvoiceIdx].description;
		            	$scope.refunds.push(refundIserted);
		            },
		            function (response) {
		              	$scope.checking_refund = false;
						$scope.notification.title = "Error al registrar pago "+invoice.description;
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
			        }
		        );
			}

			$scope.closeRegisterRefund = function(){
				$scope.showRegisterRefund = false;
			}

			$scope.deleteRefund = function(refund){
				//delete refund
				var uri = 'settlements/registerPayment/'+refund.id;
				Restangular
		        .one(uri)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customDELETE(undefined, undefined, {'Content-Type': undefined})
		        .then(
		            function (response) {
		            	refund.deleted_at = response.data.data.deleted_at;
		            },
		            function (response) {
		              	$scope.checking_refund = false;
						$scope.notification.title = "Error al cancelar el pago de liquidación "+refund.amount;
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
			        }
		        );
			}

			$scope.createTicket = function(invoice){
				$scope.showCreateTicket = true;
				$scope.checking_ticket = true;

				var idx = $scope.invoices.indexOf(invoice);
				$scope.currentInvoiceIdx = idx;

				//get from back ticket_cat form ticket creation
				Restangular
		        .one("settlements/registerForm/getTicketContent")
		        .get({"claim_file_id": invoice.id})
		        .then(
		            function successCallback(response) {
		            	$scope.checking_ticket = false;
		            	$scope.emailObject.firstTimeEmail = false;

		            	var data = response.data.data;

		            	$scope.ticket_categories = data.ticketcat;

		                $scope.emailObject.emailto = data.email.to;
		                $scope.emailObject.emailcc = data.email.cc;
		                $scope.emailObject.emailcontent = data.email.content;
		                $scope.emailObject.eamilsubject = data.email.subject;
		                $scope.emailObject.attachments = [];
		                for(x in data.email.attachments){
		                	var file = {};
		                	file.name = data.email.attachments[x].name;
		                	file.id = data.email.attachments[x].id;
		                	$scope.emailObject.attachments.push(file);
		                }
		                $scope.emailObject.internalAttachments = [];
		                for(x in data.email.internalAttachments){
		                	var file = {};
		                	file.name = data.email.internalAttachments[x].name;
		                	file.id = data.email.internalAttachments[x].id;
		                	$scope.emailObject.internalAttachments.push(file);
		                }
		                $scope.transmistemailInfo($scope.emailObject);
		            },
		            function errorCallback(response) {
		            	$scope.checking_ticket = false;
		            	$scope.notification.title = "Error al obetener los valores";
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
		            }
		        );
			}

			$scope.previewEmail = function(){
				$scope.emailObject.showPreviewEmail = true;
				$scope.transmistemailInfo($scope.emailObject);
			}

			$scope.submitCreateTicket = function(){
				$scope.checking_ticket = true;
				//get from back ticket_cat form ticket creation

				console.log($scope.currentInvoiceIdx);

				var data = new FormData();
	            data.append('ticket_cat_id', $scope.ticket.category);
	            data.append('claim_file_id', $scope.invoices[$scope.currentInvoiceIdx].id);
	            data.append('short_desc', $scope.ticket.description);

	            //email data
	            data.append('emailto',$scope.emailObject.emailto);
		        data.append('emailcc',$scope.emailObject.emailcc);
		        data.append('emailcontent',$scope.emailObject.emailcontent);
		        data.append('internallistIds',$scope.emailObject.internalattachList);
		        data.append('llistIds',$scope.emailObject.attachList);

				var postUrl = "settlements/registerForm/createTicket";

		        Restangular
		        .one(postUrl)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customPOST(data, '', undefined, {'Content-Type': undefined})
		        .then(
		            function successCallback(response) {
		            	$scope.checking_ticket = false;
		            	$scope.showCreateTicket = false;
		            	$scope.notification.title = "Ticket Creado con exito";
	                    $scope.notification.body = "";
	                    ticketData = response.data.data;
	                    $scope.invoices[$scope.currentInvoiceIdx].tickets.push(ticketData);
	                    novaNotification.showNotification($scope.notification);
		            },
		            function errorCallback(response) {
		              	$scope.checking_ticket = false;
						$scope.notification.title = "Error al crear el ticket";
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
			        }
		        );
			}

			$scope.transmistemailInfo = function(emailObject) {
				console.log(myEmailShareService.emailObject);
		        myEmailShareService.prepForBroadcast(emailObject);
		    };
		        
		    $scope.$on('handleEmailBroadcast', function() {
		    	console.log(myEmailShareService.emailObject);
		        $scope.emailObject = myEmailShareService.emailObject;
		    });

			$scope.closeCreateTicket = function(){
				$scope.showCreateTicket = false;
			}

			$scope.addNotes = function(invoice){
				$scope.showAddNotes = true;
				var idx = $scope.invoices.indexOf(invoice);
				$scope.currentInvoiceIdx = idx;
			}

			$scope.saveAddNote = function(){
				$scope.showAddNotes = false;
				//if the settlement have been already saved, save the change in the back
				$scope.invoices[$scope.currentInvoiceIdx].dirty = 1;
			}

			$scope.settelementFiles = [];
			for(x in response.data.data.uploaded){
				var fileUploadSettlement = response.data.data.uploaded[x];
				var tmpfile = {};
				tmpfile.id = fileUploadSettlement.id;
				tmpfile.category    = fileUploadSettlement.procedure_document_id;
				tmpfile.description = fileUploadSettlement.description;
				tmpfile.name = fileUploadSettlement.name;
				$scope.settelementFiles.push(tmpfile);
			}
			$scope.errFiles = [];
			$scope.settelemetDocCategories = response.data.data.categories_settlement;
			$scope.openUploadFiles = function(){
				$scope.showAddFile = true;
			}

			$scope.addRegisterFile = function(){
				var ufile = {};
				$scope.settelementFiles.push(ufile);
			}

			$scope.registerFile = function($file, ufile){
				var index=$scope.settelementFiles.indexOf(ufile);
				if($scope.settelementFiles[index]==undefined){
					$scope.settelementFiles[index]={};
				}
				if($scope.settelementFiles[index]['ts']==undefined){
					var ts=Math.floor(Date.now() / 1000);
					$scope.settelementFiles[index]['ts'] = ts;
				}
				ufile.file = $file;
				ufile.name = $file.name;
				//upload file to the server
				$scope.uploadFile(ufile,$scope.errFiles);
			}

			$scope.uploadFile = function(register_file, errFiles) {
				var token = localStorage.getItem(__env.tokenst);
				var	postUrl = "settlements/registerForm/"+process_ID+"/file";
				var urlWithToken = __env.apiUrl+postUrl+"?token="+token;

		        $scope.errFile = errFiles && errFiles[0];
		        file = register_file['file'];
		        if (file) {
		            file.upload = Upload.upload({
		                url: urlWithToken,
		                data: register_file
		            });
		            file.upload.then(function (response) {
		                $timeout(function () {
		                    file.progress = 0;
		                    file.error = 0;
		                    reg = $scope.searchByTSFile(response.data.data.ts);
		                    reg.id = response.data.data.id;
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

		    $scope.searchByTSFile = function (ts){
		    	for(x in $scope.settelementFiles){
		    		if(ts == $scope.settelementFiles[x]['ts']){
		    			return $scope.settelementFiles[x];
		    		}
		    	}
		    }

			$scope.deleteRegisterFile = function(ufile){
				var index=$scope.settelementFiles.indexOf(ufile);
				if(ufile.id!=undefined){
					var uri = 'settlements/registerForm/'+process_ID+'/file/'+ufile.id;
					Restangular
			        .one(uri)
			        .withHttpConfig({transformRequest: angular.identity})
			        .customDELETE(undefined, undefined, {'Content-Type': undefined})
			        .then(
			            function (response) {
			            	$scope.settelementFiles.splice(index,1);
			            },
			            function (response) {
			              	$scope.checking_refund = false;
							$scope.notification.title = "Error al eliminar el archivo del sevidor "+ufile.amount;
		                    $scope.notification.body = $scope.parseErrorMsg(response);
		                    novaNotification.showNotification($scope.notification);
				        }
			        );
				}else{
    				$scope.settelementFiles.splice(index,1);
				}
			}

			$scope.closeAddFile = function(){
				$scope.showAddFile = false;
			}

			$scope.saveAddFile = function(){
				$scope.checkingaddfile = true;
				//send the information in the form to be saed in case, somo meodifications were doene after upload the file
				var postUrl = "settlements/registerForm/"+process_ID+"/file/saveBulk";
				var data = new FormData();
				data.append("filesUploadedObj",JSON.stringify($scope.settelementFiles));
				Restangular
		        .one(postUrl)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customPOST(data, '', undefined, {'Content-Type': undefined})
		        .then(
		            function successCallback(response) {
		            	$scope.checkingaddfile = false;
		            	$scope.showAddFile = false;
		            	$scope.notification.title = "Archivo guardado con éxito";
	                    $scope.notification.body = "";
	                    novaNotification.showNotification($scope.notification);
		            },
		            function errorCallback(response) {
		              	$scope.checkingaddfile = false;
						$scope.notification.title = "Error al cguardar los archivos";
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
			        }
		        );
			}

			$scope.imprimirCarta = function (ref) {
		        console.log('entro');

		        var token = localStorage.getItem(__env.tokenst);
				$scope.form.token=token;

	            $http.get(__env.apiUrl+ref, { 
	                params: $scope.form, 
	                responseType: 'arraybuffer'
	                            })
	                  .success(function (data) {
	                      var file = new Blob([data], {type: 'application/pdf'});
	                      var fileURL = URL.createObjectURL(file);
	                      window.open(fileURL);
	               });
		    }

		    $scope.finish_settlement = {};
		    $scope.finish_settlement.msg = "Confirma que desea terminar el proceso de Liquidación";
		    $scope.confirmFinishSettelement = function(){
		    	//TODO: ask in the back if all the invoice have been settle
		    	//DO not allow them to finish until that process have been finished
		    	$scope.showconfirmFinishSettelemnt = true;
		    }

		    $scope.finishSettlement = function(){
		    	$scope.checking_finish_settelement = true;
				//send the information in the form to be saed in case, somo meodifications were doene after upload the file
				var postUrl = "settlements/finish/"+process_ID;
				var data = new FormData();
				Restangular
		        .one(postUrl)
		        .withHttpConfig({transformRequest: angular.identity})
		        .customPOST(data, '', undefined, {'Content-Type': undefined})
		        .then(
		            function successCallback(response) {
		            	$scope.checking_finish_settelement = false;
		            	$scope.showAddFile = false;
		            	$scope.notification.title = "El trámite fue finalizado con éxito";
	                    $scope.notification.body = "";
	                    novaNotification.showNotification($scope.notification);
	                    $state.go('root.seguros.reclamos-liquidaciones',{}, {reload: true});
		            },
		            function errorCallback(response) {
		              	$scope.checking_finish_settelement = false;
						$scope.notification.title = "Error al cguardar los archivos";
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
			        }
		        );
		    }

}]);
	