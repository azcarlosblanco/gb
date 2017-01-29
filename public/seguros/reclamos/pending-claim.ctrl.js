angular
.module('ngNova')
.provider('novaNotification', novaNotification)
.controller('PendingClaimCtrl',
		['Restangular','response','$rootScope', '$scope', 'hotkeys', '$state', '$http', '__env', 'Upload', '$timeout', 'novaNotification', 'myShareService',
		function(Restangular, response, $rootScope, $scope, hotkeys, $state, $http, __env, Upload, $timeout, novaNotification, myShareService){

		var tmp_data = response.data.data;
		$scope.ntoken = localStorage.getItem(__env.tokenst);
		$scope.affDiagnostics = [];
		//variable used to display modal with files that have been uploaded
		$scope.pickFilesAffiliate = false;
		//to editor add a ne file to the clim
		$scope.addEditClaimFile = false;
        // add diagnosis
        $scope.ShowAddDiagnosis= false;
		//variable that hold the files of teh claim
		$scope.uploadedFiles = [];
		$scope.currentAffDigdx = 0;
		$scope.errFiles = [];
		$scope.process_ID = tmp_data.process_id;
		$scope.notification = {};
		$scope.affSelectedDiagnosis = {};
		$scope.policy_number = tmp_data.policy_number;
		$scope.customer_name = tmp_data.customer_name;
		$scope.brand_new = tmp_data.brand_new;
		$scope.pickFileHasError = false;

		$scope.effective_date = tmp_data.effective_date;
		$scope.plan = tmp_data.plan;

		$scope.addAffiliateCategory = function (){
			$scope.affDiagnostics.push({});
		}

		//functions used to link a link a file to array affiliate diagnosis

		//display modal with info about file have been updated
		$scope.selectFileAff = function (affDiagnostic){
			var idx = $scope.affDiagnostics.indexOf(affDiagnostic);
			$scope.currentAffDigdx = idx;
			if($scope.affDiagnostics[idx].pickedFiles===undefined){
				$scope.affDiagnostics[idx].pickedFiles = [];
			}
			$scope.pickFilesAffiliate = true;

			//create a list with the files that have been selected in the claim
			$scope.listPickedFiles = {};
			for(x in $scope.affDiagnostics){
				for(y in $scope.affDiagnostics[x].pickedFiles){
					console.log($scope.affDiagnostics[x].pickedFiles[y]);
					$scope.listPickedFiles[$scope.affDiagnostics[x].pickedFiles[y].id]="file";
				}
			}
		}

		//close modal with the files that have been updated
		$scope.closePickFilesAffiliate = function(){
			$scope.pickFilesAffiliate = false;
		}


		$scope.linkFiles = function(oldSelection){
			$scope.affDiagnostics[$scope.currentAffDigdx].files = {};
			for(x in $scope.affDiagnostics[$scope.currentAffDigdx].pickedFiles){
				var file = $scope.affDiagnostics[$scope.currentAffDigdx].pickedFiles[x];
				$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']] = {}
				$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['procedure_document_id']=file['data']['procedure_document_id'];
				$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['prev_order']=file['prev_order'];
				$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['value']=file['data']['amount'];
				$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['supplier']=file['data']['supplier'];
				$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['concept']=file['data']['concept'];

				for(y in oldSelection){
					if(y == file['id']){
						console.log(oldSelection[y]);
						//el archivo ya estaba seleccioando anteriormente
						$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['value']=oldSelection[y]['value'];
						$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['supplier']=oldSelection[y]['supplier'];
						$scope.affDiagnostics[$scope.currentAffDigdx].files[file['id']]['concept']=oldSelection[y]['concept'];
					}
				}
			}
		}

		//link the files have been checked in the modal to the affiliates
		$scope.linkFilesAffiliate = function(){

			$scope.pickFilesAffiliate = false;
			if($scope.affDiagnostics[$scope.currentAffDigdx].files!=undefined)
				var oldSelection = $scope.affDiagnostics[$scope.currentAffDigdx].files;
			else
				var oldSelection = {};
			$scope.linkFiles(oldSelection);
		}

		$scope.addFile = function(){
			$scope.addEditClaimFile = true;
			$scope.file_upload = {};
			$scope.file_upload.data = {};
			$scope.file_upload.data.valid = 1;
		}

		$scope.editFile = function(uploadFile){
			$scope.addEditClaimFile = true;
			$scope.file_upload = uploadFile;
		}

		//close modal with the files that have been updated
		$scope.closeAddEditClaimFile = function(){
			$scope.addEditClaimFile = false;
		}

		//function used to save files selected for the affiliate
		$scope.toggleSelection = function toggleSelection(item,list) {
			//check is the selected file is complete, if the fiel is no complete show alert
    		var idx = list.indexOf(item);
		    // is currently selected
		    if (idx > -1) {
		      	list.splice(idx, 1);
		    }
		    // is newly selected
		    else {
		      	list.push(item);
		    }
		};

		$scope.ischecked = function(uploadFile){
			if($scope.affDiagnostics[$scope.currentAffDigdx].pickedFiles !== undefined){
				var idx = $scope.affDiagnostics[$scope.currentAffDigdx].pickedFiles.indexOf(uploadFile);
				console.log("curren aff index"+$scope.currentAffDigdx);
				console.log(idx);
				if(idx > -1){
					return 1;
				}
			}
			return 0;
		}

		$scope.pickFileHasError = false;
		$scope.pickFileErrors = [];
		//function used to save files selected for the affiliate
		$scope.toggleSelectionLinkFiles = function toggleSelection(item,list) {
			console.log('ngclick');
			$scope.checkFilesAssociateIsOk(item);
    		//if($scope.checkFilesAssociateIsOk(item)){
				var idx = list.indexOf(item);
			    // is currently selected
			    if (idx > -1) {
			      	list.splice(idx, 1);
			    }
			    // is newly selected
			    else {
			      	list.push(item);
			    }
			//}else{
			//	alert("Por favor complete los campos faltantes del archivo");
			//}
		};
		$scope.ischeckedLinkFiles = function(uploadFile){
			if(uploadFile.dirty){
				console.log("dirty");
				return 0;
			}else{
				console.log("no dirty");
			}

			if($scope.affDiagnostics[$scope.currentAffDigdx].pickedFiles !== undefined){
				var idx = $scope.affDiagnostics[$scope.currentAffDigdx].pickedFiles.indexOf(uploadFile);
				if(idx > -1){
					return 1;
				}
			}
			return 0;
		}
		$scope.checkFilesAssociateIsOk = function(uploadFile){
			var error = false;
			var errors = [];
			var category = $scope.cat_names[uploadFile.data.procedure_document_id];
			$scope.pickFileErrors = [];
			$scope.pickFileHasError = true;
			if(uploadFile.data.supplier==undefined || uploadFile.data.supplier==""){
				error = true;
				$scope.pickFileErrors.push({
						"file" : category+" "+uploadFile.description,
						"error" : "Debe seleccionar proveedor",
							});
			}
			var isinvoice = ($scope.cat_names[uploadFile.data.procedure_document_id]=='claim_invoice')?true:false;
			if(isinvoice){
				if(uploadFile.data.date==undefined || uploadFile.data.date==""){
					error = true;
					$scope.pickFileErrors.push({
						"file" : category+" "+uploadFile.description,
						"error" : "Debe seleccionar la fecha de la factura",
							});
				}

				if(uploadFile.data.amount==undefined || uploadFile.data.amount==""){
					error = true;
					$scope.pickFileErrors.push({
						"file" : category+" "+uploadFile.description,
						"error" : "Debe ingresar el monto de la factura",
							});
				}

				if(uploadFile.data.currency==undefined || uploadFile.data.currency==""){
					error = true;
					$scope.pickFileErrors.push({
						"file" : category+" "+uploadFile.description,
						"error" : "Debe ingrsar el tipo de moneda de la factura",
							});
				}
			}

			if(error){
				uploadFile.dirty = true;
				$scope.pickFileHasError = true;
			}else{
				uploadFile.dirty = false;
				$scope.pickFileHasError = false;
			}

			return !error;
		}

		$scope.changeUploadFile = function(uploadFile){
			uploadFile.dirty = true;
		}

		//catalog lists
		//documemts cats
		var temp_data = response.data.data;
		$scope.categories = [];
		$scope.cat_names = [];
		$scope.cat_placeholder = [];
		for (x in temp_data.categories) {
			$scope.categories[temp_data.categories[x]['id']]=temp_data.categories[x]['description'];
			$scope.cat_names[temp_data.categories[x]['id']]=temp_data.categories[x]['name'];
			if(temp_data.categories[x]['name']=="claim_invoice"){
				$scope.cat_placeholder[temp_data.categories[x]['id']] = "# Factura";
			}else if(temp_data.categories[x]['name']=="claim_laborder"){
				$scope.cat_placeholder[temp_data.categories[x]['id']] = "# Orden";
			}else if(temp_data.categories[x]['name']=="claim_labresult"){
				$scope.cat_placeholder[temp_data.categories[x]['id']] = "# Resultado";
			}
		}
		//currency
		$scope.currencies = {};
		for(x in temp_data.currencies){
			$scope.currencies[temp_data.currencies[x]['id']] = temp_data.currencies[x]['display_name'];
		}
		//suppliers
		$scope.suppliers = {};
		for(x in temp_data.suppliers){
			$scope.suppliers[temp_data.suppliers[x]['id']] = temp_data.suppliers[x]['name'];
		}
		//diagnosis
		$scope.diagnosis = {};
		for(x in temp_data.diagnosis){
			$scope.diagnosis[temp_data.diagnosis[x]['id']] = temp_data.diagnosis[x]['display_name'];
		}
		//concept
		$scope.concept = {};
		for(x in temp_data.concept){
			$scope.concept[temp_data.concept[x]['id']] = temp_data.concept[x]['display_name'];
		}

		//affiliates
		$scope.affiliates = {};
		for(x in temp_data.affiliates){
			$scope.affiliates[temp_data.affiliates[x]['id']] = temp_data.affiliates[x]['name']
																+" - "+temp_data.affiliates[x]['role'];
			//list of diagnosis the have not been linked with the user
			$scope.affSelectedDiagnosis[temp_data.affiliates[x]['id']] = [];
		}

		//concepts
		$scope.concepts = {};
		for(x in temp_data.concepts){
			$scope.concepts[temp_data.concepts[x]['id']] = temp_data.concepts[x]['display_name'];
		}


		//read files that have been uploaded
		for(x in temp_data.files){

			if(temp_data.files[x].data.valid==undefined){
				temp_data.files[x].data.valid=1;
			}else{
				temp_data.files[x].data.valid=parseInt(temp_data.files[x].data.valid);
			}
			if(temp_data.files[x].data.usa==undefined){
				temp_data.files[x].data.usa="0";
			}else{
				temp_data.files[x].data.usa=parseInt(temp_data.files[x].data.usa);
			}
			if(temp_data.files[x].data.amount!=undefined){
				temp_data.files[x].data.amount=parseFloat(temp_data.files[x].data.amount);
			}
			if(temp_data.files[x].data.date!=undefined){
				temp_data.files[x].data.date = new Date(Date.parse(temp_data.files[x].data.date));
			}
			$scope.uploadedFiles[x] = temp_data.files[x];
		}

		$scope.addAffiliateCategory();


		$scope.registerExistingFile = function(file,item){
			console.log(file);
			item.file = file;
			item.original_filename = file.name;
		}

		$scope.submitEditFile = function(item){
			if(Upload.isUploadInProgress()){
				alert("Espere a que el archivo termine de subirse");
				return;
			}else{
				$scope.uploadFile(item, $scope.errFiles, 'edit');
			}
		}

		$scope.registerNewFile = function(file,item){
			item.file = file;
			item.data.ts = Math.floor(Date.now() / 1000);
			item.original_filename = file.name;
		}

		//this function save the changes in all the files in the list
		//in the back
		$scope.saveChangesFiles = function(){
			if(Upload.isUploadInProgress()){
				alert("Espere a que el archivo termine de subirse");
				return;
			}else{
				for(x in $scope.uploadedFiles){
					$scope.uploadFile($scope.uploadedFiles[x], $scope.errFiles, 'edit');
				}
			}
		}

		//add new file file in the back
		$scope.submitAddFileToClaim = function(item){
			if(item.file==undefined){
				//alert, can no create the file without the file
				$scope.notification.title = "Error";
        		$scope.notification.body = "Debe subir un archivo";
    			novaNotification.showNotification($scope.notification);
    			$scope.checking_new_file = false;
    			return;
			}

			if(Upload.isUploadInProgress()){
				alert("Espere a que el archivo termine de subirse");
				return;
			}else{
				if(item.data.usa == undefined){
					item.data.usa = 0;
				}
				$scope.uploadFile(item, $scope.errFiles, 'new');
			}

		}


		$scope.uploadFile = function(item, errFiles, action){

			var token = localStorage.getItem(__env.tokenst);
			var	postUrl = "claims/reviewDocuments/"+$scope.process_ID+"/files";
			var urlWithToken = postUrl+"?token="+token;

	        $scope.errFile = errFiles && errFiles[0];

	        if(action=='new'){
        		var checkVar = $scope.checking_new_file;
        	}else{
        		var checkVar = $scope.checking_edit_file;
        	}
	        file = item['file'];
	        if (file) {
	        	checkVar = true;
	            file.upload = Upload.upload({
	                url: __env.apiUrl+urlWithToken,
	                data: item
	            });
	            file.upload.then(function (response) {
	            	checkVar = false;
	                $timeout(function () {
	                    file.progress = 0;
	                    file.error = 0;
	                    if(action=='new'){
	                    	item['id']  = response.data.data.id;
	                    	item['dirty'] = false;
		                    //add file to the list of files availables
		                    $scope.uploadedFiles.push(item);
		                    $scope.addEditClaimFile = false;
		                    $scope.notification.title = "Archivo "+file.name+" subido";
	                    }else{
	                    	$scope.notification.title = "Archivo "+file.name+" actualizado";
	                    }
	                    $scope.notification.body = item.original_filename;
	                    novaNotification.showNotification($scope.notification);
	                });
	            }, function (response) {
	            	checkVar = false;
	                if (response.status > 0){
	                    file.error = 1
	                    file.progress = 0;
	                }
	                $scope.checking_new_file = false;
                	$scope.notification.title = "Error al subir el archivo";
                    $scope.notification.body = $scope.parseErrorMsg(response);
                    novaNotification.showNotification($scope.notification);
	            }, function (evt) {
	            	$scope.checking_new_file = false;
	                file.progress = Math.min(100, parseInt(100.0 *
	                                         evt.loaded / evt.total));
	            });
	        }else{
	        	//update file info, but no upload a new file
	        	checkVar = false;
	        	var data = new FormData();
	        	for(x in item){
	        		if(x == 'data'){
	        			for(y in item[x]){
	        				data.append('data['+y+']',item[x][y]);
	        			}
	        		}else{
	        			data.append(x,item[x]);
	        		}
	        	}

	        	Restangular
	            .one(urlWithToken)
	            .withHttpConfig({transformRequest: angular.identity})
	            .customPOST(data, '', undefined, {'Content-Type': undefined})
				.then(
					function successCallback(response) {
						checkVar = false;
						$scope.notification.title = "Archivo actualiazado";
	                    $scope.notification.body = item.original_filename;
	                    novaNotification.showNotification($scope.notification);
	                },
	                function errorCallback(response) {
	                	checkVar = false;
	                	$scope.notification.title = "Error al actualizar el archivo";
	                    $scope.notification.body = item.original_filename;
	                    novaNotification.showNotification($scope.notification);
	                }
	            );
	        }
        }

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

        $scope.selectAffiliate = function(affDiagnostic){
        	if(affDiagnostic.oldAffiliate!==undefined){
        		if(affDiagnostic.oldAffiliate!=undefined && affDiagnostic.oldAffiliate!=""){
	        		var idx = $scope.affSelectedDiagnosis[affDiagnostic.oldAffiliate].indexOf(affDiagnostic.diagnosis);
					if(idx!=-1){
						$scope.affSelectedDiagnosis[affDiagnostic.oldAffiliate].splice(idx,1);
					}
	        	}
        	}
        	affDiagnostic.oldAffiliate = affDiagnostic.affiliate;
        	if(affDiagnostic.diagnosis!=undefined){
        		$scope.selectDiagnisticAff(affDiagnostic);
        	}
        }

		$scope.selectDiagnisticAff = function(affDiagnostic){
			//get the list of diagnosisi the affiliate can select
			//just can select diagnosis that have not been selected before for him
			var idx = $scope.affDiagnostics.indexOf(affDiagnostic);
			for(x in $scope.affDiagnostics){
				if( x != idx ){
					if(affDiagnostic.affiliate == $scope.affDiagnostics[x].affiliate){
						if(affDiagnostic.diagnosis == $scope.affDiagnostics[x].diagnosis){
							alert("Ya existe la combinación afiliado diagnóstico");
							affDiagnostic.diagnosis = null;
							return;
						}
					}
				}
			}

			/*
			for(x in $scope.affSelectedDiagnosis[affDiagnostic.affiliate]){
				console.log(x);
				if( affDiagnostic.diagnosis==$scope.affSelectedDiagnosis[affDiagnostic.affiliate][x] ){
					alert("Ya existe la combinación afiliado diagnóstico");
					affDiagnostic.diagnosis = null;
					return;
				}
			}

			$scope.affSelectedDiagnosis[affDiagnostic.affiliate].push(affDiagnostic.diagnosis);
			if(affDiagnostic.oldDiagnosis!==undefined){
				//delete old diagnisis of list os selectd diagnotic
				var idx = $scope.affSelectedDiagnosis[affDiagnostic.affiliate].indexOf(affDiagnostic.oldDiagnosis);
				if(idx!=-1){
					$scope.affSelectedDiagnosis[affDiagnostic.affiliate].splice(idx,1);
				}
			}
			affDiagnostic.oldDiagnosis = affDiagnostic.diagnosis;*/

		}

		$scope.removeAffDiagnostic = function(affDiagnostic){
			var idx = $scope.affDiagnostics.indexOf(affDiagnostic);
			if(idx > -1){
				$scope.affDiagnostics.splice(idx,1);
			}
		}

		$scope.submitAffiliateDiagnosis = function(){
			var data = {};
			data['aff_diag'] = {};

			$scope.checking_submit = true;
			var listUsedFiles = [];
			for(x in $scope.affDiagnostics){
				var affDiagnosis = $scope.affDiagnostics[x];
				if(affDiagnosis.affiliate==undefined
					|| affDiagnosis.diagnosis==undefined){
					$scope.checking_submit = false;
					$scope.notification.title = "Error de validación";
                    $scope.notification.body = "Debe seleccionar afiliado y diagnóstico";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(data['aff_diag'][affDiagnosis.affiliate]==undefined)
					data['aff_diag'][affDiagnosis.affiliate]={};

				data['aff_diag'][affDiagnosis.affiliate][affDiagnosis.diagnosis] = [];
				var flag = false;
				var flaginvoice = false;
				for(y in affDiagnosis.files){
					flag = true;
					var file = {};

					var idx = listUsedFiles.indexOf(y);
					if( idx == -1 ){
						listUsedFiles.push(y);
					}

					console.log(affDiagnosis.files[y]);
					file.file_entry_id = y;
					file.value = affDiagnosis.files[y]['value'];
					file.concept = affDiagnosis.files[y]['concept'];
					file.prev_order = affDiagnosis.files[y]['prev_order'];
					file.supplier = affDiagnosis.files[y]['supplier'];
					if( $scope.cat_names[affDiagnosis.files[y]['procedure_document_id']] == "claim_invoice" ){
						flaginvoice =true;
					}
					data['aff_diag'][affDiagnosis.affiliate][affDiagnosis.diagnosis].push(file);
				}

				if(!flag){
					$scope.checking_submit = false;
					$scope.notification.title = "Error";
                    $scope.notification.body = "Debe seleccionar al menos un archivo para el afiliado";
                    novaNotification.showNotification($scope.notification);
                    return;
				}

				if(!flaginvoice){
					$scope.checking_submit = false;
					$scope.notification.title = "Error";
                    $scope.notification.body = "Debe seleccionar al menos una factura para el afiliado";
                    novaNotification.showNotification($scope.notification);
                    return;
				}
			}

			var numFilesValid = 0 ;
			var listInavlidFiles = [];
			for(x  in $scope.uploadedFiles){
				if($scope.uploadedFiles[x].data.valid){
					//valid files
					numFilesValid++;
				}else{
					//list of inavlid files thata nedd to be register in the server
					listInavlidFiles.push($scope.uploadedFiles[x].id);
				}
			}
			data['invalidFiles'] = listInavlidFiles;

			if( listUsedFiles.length < numFilesValid ){
				$scope.checking_submit = false;
				$scope.notification.title = "Error";
                $scope.notification.body = "Existen archivos válidos no han sido usado en el reclamo";
                novaNotification.showNotification($scope.notification);
                return;
			}

			urlApi = "claims/reviewDocuments/"+$scope.process_ID;

			Restangular
            .one(urlApi)
            .withHttpConfig({transformRequest: angular.identity})
            .customPOST(JSON.stringify(data), '', undefined, {'Content-Type': undefined})
			.then(
				function successCallback(response) {
					$scope.checking_submit = false;
					$scope.notification.title = "Formulario Enviado";
                    $scope.notification.body = "Reclamos han sido registrados";
                    novaNotification.showNotification($scope.notification);
                    $state.go('root.seguros.reclamos.impresion-carta', {'process_ID':$scope.process_ID}, {reload: true});
                },
                function errorCallback(response) {
                	$scope.checking_submit = false;
                	$scope.notification.title = "Error al enviar el archivo";
                    $scope.notification.body = $scope.parseErrorMsg(response);
                    novaNotification.showNotification($scope.notification);
                }
            );

		}

		$scope.selectPrevOrders = function(affDiagnosis){
			$scope.pickPrevOrders = true;

			console.log(affDiagnosis);

			var idx = $scope.affDiagnostics.indexOf(affDiagnosis);
			$scope.currentAffDigdx = idx;
			if($scope.affDiagnostics[idx].previousOrders === undefined){
				$scope.affDiagnostics[idx].previousOrders = [];
				if($scope.affDiagnostics[idx].pickedFiles == undefined){
					$scope.affDiagnostics[idx].pickedFiles = [];
				}
				//get the list of previous orders from back
				//get previous orders of affiliate
		        Restangular
		        .one('claims/reviewDocuments/previousOrders',affDiagnosis.affiliate)
		        .get("")
		        .then(
		            function successCallback(response) {
		            	var listOrders = response.data.data;
		            	for(x in listOrders){
		            		$scope.affDiagnostics[idx].previousOrders[x] = {}
		            		$scope.affDiagnostics[idx].previousOrders[x]['id'] = listOrders[x]['file_id'];
		            		$scope.affDiagnostics[idx].previousOrders[x]['description'] = listOrders[x]['description'];
		            		$scope.affDiagnostics[idx].previousOrders[x]['prev_order'] = 1;
		            		$scope.affDiagnostics[idx].previousOrders[x]['data'] = {};
		            		$scope.affDiagnostics[idx].previousOrders[x]['data']['procedure_document_id'] = listOrders[x]['category']+"";
		            		$scope.affDiagnostics[idx].previousOrders[x]['data']['concept'] = listOrders[x]['concept']+"";
		            		$scope.affDiagnostics[idx].previousOrders[x]['data']['supplier'] = listOrders[x]['supplier_id']+"";
		            	}
		            },
		            function errorCallback(response) {
		            	$scope.notification.title = "Error al caragar las ódenes anteriores";
	                    $scope.notification.body = $scope.parseErrorMsg(response);
	                    novaNotification.showNotification($scope.notification);
		            }
		        );
			}
		}

		$scope.closepickPrevOrders = function(){
			$scope.pickPrevOrders = false;
		}

		$scope.linkOrdersAffiliate = function(){
			$scope.pickPrevOrders = false;
			if($scope.affDiagnostics[$scope.currentAffDigdx].files!=undefined)
				var oldSelection = $scope.affDiagnostics[$scope.currentAffDigdx].files;
			else
				var oldSelection = {};
			$scope.linkFiles(oldSelection);
		}

		//function used to recive the diagnosis that was created
		$scope.newDiagnosisObject = {};
		$scope.newDiagnosisObject.showmodal = false;

		$scope.addDiagnostic = function(){
        	$scope.newDiagnosisObject.showmodal = true;
        }

	    $scope.$on('handleDiagnosisBroadcast', function() {
	        $scope.newDiagnosisObject = myShareService.diagnosis;
	        $scope.diagnosis[$scope.newDiagnosisObject['id']] =
	        			$scope.newDiagnosisObject['display_name'];
	    });
	    $scope.$on('handleCancelDiagnosisBroadcast', function() {
	        $scope.newDiagnosisObject.showmodal = false;
	    });

	    //function used to recive the diagnosis that was created
	    $scope.newConceptObject={};
	    $scope.newConceptObject.showmodal=false;

	    $scope.addConcept = function(){
	    	$scope.newConceptObject.showmodal=true;
	    }

        $scope.$on('handleConceptBroadcast', function (){
        	$scope.newConceptObject=myShareService.concept;
        	$scope.concepts[$scope.newConceptObject['id']]=
        	             $scope.newConceptObject['display_name'];
        });
       $scope.$on('handleCancelConceptBroadcast', function (){
       	   $scope.newConceptObject.showmodal=false;
       });
}])
.filter('reverse', function() {
	return function(items) {
	    return items.slice().reverse();
	};
});
