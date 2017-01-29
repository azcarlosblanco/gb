angular
.module('ngNova')
.controller('ApiListCtrl', ['response', 'endPoint', '$scope', '$state', '$stateParams', '$filter', 
function(response, endPoint, $scope, $state, $stateParams, $filter) {
	
	var modul = endPoint.modul;
	var title = endPoint.title;
	
	var ids = $stateParams.ids;
	var moduls = $stateParams.moduls;
	var types = $stateParams.types;
	
	if (moduls == 'companies') {
		this.display = {title : "Lista de " + title + ": Compañias"};
	} else if (moduls == 'sellers') {
		this.display = {title : "Lista de " + title + ": Vendedor"};
	} else {
		this.display = {title : "Lista de " + title};
	}
	
	//console.log('module:', modul);
	
	var modelHeader = {
		'companies': [
			{fieldName: "name", filterType: "text", label : "Nombre de la compañia"},
			{fieldName: "commission_type_id", filterType: "text", label : "Tipo de comisión"},
			{fieldName: "buttons", filterType: "text", label: "Acciones"}
		],
		'sellers': [
			{fieldName: "name", filterType: "text", label : "Nombre"},
			{fieldName: "monthly_fee", filterType: "text", label : "Cuota mensual"},
			{fieldName: "percentage_extra", filterType: "text", label : "Porcentaje extra"},
			{fieldName: "commission_type_id", fieldArray: {key: 'commission_type', value:'display_name'}, filterType: "text", label : "Tipo de comisión"},
			{fieldName: "buttons", filterType: "text", label: "Acciones"}
		],
		'sales': [
			{fieldName: "company_id", filterType: "text", label: "Compañia"},
			{fieldName: "seller_id", filterType: "text", label: "Vendedor"},
			{fieldName: "commission_company", filterType: "text", label: "Estatus de Comision"},
			{fieldName: "quote_number", filterType: "text", label: "Número de cotización"},
			{fieldName: "total_amount", filterType: "text", label : "Cantidad total"},
			{fieldName: "sale_date", filterType: "text", label : "Fecha de venta"},
			{fieldName: "payment_date", filterType: "text", label : "Fecha de pago"},
			{fieldName: "confirmation_date", filterType: "text", label : "Fecha de confirmación"},
			{fieldName: "buttons", filterType: "text", label: "Acciones"}
		],
		'commissions/companies': [
			{fieldName: "link", filterType: "text", label: "Venta"},
			{fieldName: "commission_type_id", filterType: "text", label : "Tipo de Comision"},
			{fieldName: "percentage_applied", filterType: "text", label : "Porcentaje Aplicado"},
			{fieldName: "value", filterType: "text", label : "Pago"}
		],
		'commissions/sellers': [
			{fieldName: "link", filterType: "text", label: "Venta"},
			{fieldName: "commission_type_id", filterType: "text", label : "Tipo de Comision"},
			{fieldName: "percentage_applied", filterType: "text", label : "Porcentaje aplicado"},
			{fieldName: "extra_percentage_applied", filterType: "text", label : "Porcentaje adicional"},
			{fieldName: "value", filterType: "text", label : "Pago"}
		]
	};
	
	this.header = modelHeader[modul];
	
	this.actions = [];
	
	if (modul == 'sales' || modul == 'sellers' || modul == 'companies') {
		this.actions.push({
			link: ".create", 
			name: "create", 
			type: "button", 
			value: "Crear " + title
		});
	}
	
	this.fullList = response.data.data;
	
	for (var j = 0; j < this.fullList.length; j++) {
		$value = this.fullList[j];
		
		var button = [];
		
		if (modul == 'sales') {
			button.push({
				'active': true,
				'class': 'available',
				'description': 'Calcular comisiones',
				'icon': 'glyphicon glyphicon-stats',
				'link': '.calculate',
				params: {
					id: $value.id,
					type: 'calculate',
					uri: modul
				}
			});
		}
		
		if (modul == 'companies' || modul == 'sellers') {
			button.push({
				'active': true,
				'class': 'available',
				'description': 'Configuracion',
				'icon': 'glyphicon glyphicon-cog',
				'link': '.config',
				params: {
					idc: $value.id,
					idm: $value.commission_type_id
				}
			}, {
				'active': true,
				'class': 'available',
				'description': 'Ventas',
				'icon': 'glyphicon glyphicon-shopping-cart',
				'link': 'root.seguros.ventas',
				params: {
					ids: $value.id,
					moduls: modul,
					types: 'sales'
				}
			}, {
				'active': true,
				'class': 'available',
				'description': 'Comision',
				'icon': 'glyphicon glyphicon-usd',
				'link': 'root.seguros.comisiones',
				params: {
					ids: $value.id,
					moduls: modul,
					types: 'commissions'
				}
			});
		}
		
		if (modul == 'commissions/companies' || modul == 'commissions/sellers') {
			this.fullList[j].link = {link: 'root.seguros.ventas.edit', label: 'Ver venta', params: {id: $value.sale_id}};
		} else {
			button.push({
				'class': 'edit', 
				params: {
					id: $value.id
				}
			}, {
				'class': 'delete', 
				params: {
					id: $value.id,
					type: 'delete',
					uri: modul
				}
			});
		}
		
		this.fullList[j].buttons = button;
	}
	
	this.selected = [];
	$scope.selectedregs = [];
	$scope.form = {}

    // toggle selection 
	$scope.toggleSelection = function toggleSelection(idreg) {
		console.log(idreg);
		var idx = $scope.selectedregs.indexOf(idreg);
		if (idx > -1) {
			$scope.selectedregs.splice(idx, 1);
		} else {
			$scope.selectedregs.push(idreg);
		}
	};
	
	//console.log('actions', this.actions);

	if (this.actions !== undefined) {
		for (x in this.actions) {
			if (this.actions[x].initvalue !== undefined) {
				$scope.form[this.actions[x].name] = this.actions[x].initvalue;
				console.log($scope.form);
			}
		}
	}

	$scope.doAction = function(link) {
		console.log($scope.selectedregs);
		var receiver = $scope.form.receiver;
		$state.go(link, {'selected': $scope.selectedregs, 'receiver': receiver});
	}

	$scope.changeFilter = function(link) {
		var receiver = $scope.form.receiver;
		console.log('receiver', receiver);
		$state.go('.', {'receiver': receiver});
	}

	var blueprint = [];
	this.list = [];

	for (var i = 0; i < this.header.length; i++) {
		blueprint.push(this.header[i].fieldName);
	}

	for (var j = 0; j < this.fullList.length; j++) {
		
		this.list.push([]);
		this.list[j] = [];
		this.list[j].push({"selected" : false});
		
		for (var k = 0; k < blueprint.length; k++) {
			
			var index = blueprint[k];
			var data = this.fullList[j];
			$value = data[index];
			
			//console.log('key', index);
			
			// Commissions - Status
			if (index == 'commission_company') {
				if (typeof data.commission_company !== 'undefined' && typeof data.commission_company == 'object') {
					if (data.commission_company !== null) {
						$value = 'COMISION REALIZADA';
					}
				} else {
					$value = 'COMISION PENDIENTE';
				}
			}
			
			// Commissions
			if (index == 'commission_type_id' && typeof data.commission_type !== 'undefined' && typeof data.commission_type == 'object') {
				if (data.commission_type !== null) {
					$value = data.commission_type.display_name;
				}
			}
			
			// Companies
			if (index == 'company_id' && typeof data.company !== 'undefined' && typeof data.company == 'object') {
				if (data.company !== null) {
					$value = data.company.name;
				}
			}
			
			// Sellers
			if (index == 'seller_id' && typeof data.seller !== 'undefined' && typeof data.seller == 'object') {
				if (data.seller !== null) {
					$value = data.seller.name;
				}
			}
			
			// Date Format
			if (index.indexOf('_date') > 0) {
				var date = new Date(Date.parse(data[index]));
				$value = $filter('date')(date, 'yyyy-MM-dd');
			}
			
			// Value Null
			if ($value == null || $value == '') {
				$value = 'SIN DATOS';
			}
			
			this.list[j].push({'name': index, 'value': $value});
			
			//console.log('list', {'name': index, 'value': $value});
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
});