angular
.module('ngNova')
.controller('ListCtrl', ['response', '$scope', '$state', function(response, $scope, $state){
	console.log(response);
	this.display = response.data.data.display;
	this.header = response.data.data.display.header;
	this.actions = response.data.data.actions;
	this.fullList = response.data.data.list;
	this.selected = [];
	//this.selectedregs = [];

	$scope.selectedregs = [];
	$scope.form={}

    // toggle selection 
	$scope.toggleSelection = function toggleSelection(idreg) {
		console.log(idreg);
		var idx = $scope.selectedregs.indexOf(idreg);
        // is currently selected
       	if (idx > -1) {
            $scope.selectedregs.splice(idx, 1);
       	}else {
	        $scope.selectedregs.push(idreg);
		}
	};

	
	console.log(this.actions);

	if(this.actions !== undefined){
		for(x in this.actions){
			if(this.actions[x].initvalue !== undefined ){
				$scope.form[this.actions[x].name]=this.actions[x].initvalue;
				console.log($scope.form);
			}
		}
	}

	$scope.doAction = function(link){
		console.log($scope.selectedregs);
		var receiver=$scope.form.receiver;
		$state.go(link,{
						'selected': $scope.selectedregs, 
						'receiver': receiver
						});
	}

	$scope.changeFilter = function(link){
		var receiver=$scope.form.receiver;
		console.log(receiver);
		$state.go('.',{
						'receiver': receiver
						});
	}


	var blueprint = [];
	this.list = [];

	for (var i=0; i<this.header.length; i++) {
		blueprint.push(this.header[i].fieldName);
	}

	for (var j=0; j<this.fullList.length; j++)   { 
		this.list.push([]);
		this.list[j] = [];
		this.list[j].push({"selected" : false});

		for (var k=0; k<blueprint.length; k++) {
			$value = this.fullList[j][blueprint[k]];
			/*if(blueprint[k]=="buttons"){
				console.log(1);
				for(index in $value){
					var button = $value[index];
					var params = button.params;
					console.log(params);
				}
			}*/

			this.list[j].push(
				{
					"name": blueprint[k],
					"value": $value
				}
			);

			
		}

	}
}])

.config(function(selectionModelOptionsProvider) {
  selectionModelOptionsProvider.set({
    selectedClass: 'list-selected',
    type: 'checkbox',
    mode: 'multiple-additive',
    cleanupStrategy: 'deselect'
  });
});;