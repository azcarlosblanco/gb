<?php

return [
	
	/**
	 * Tamaño de la paginación para ciertos recursos. Es decir el número de resultados que se 
	 * mostrará en cada página de resultados al hacer una petición.
	 */
	'pagination_size' => [
		/**
		 * Para 
		 * 		GET | nova/sales/api/v1/sales
		 * 		GET | nova/sales/api/v1/sellers/{id}/sales
		 * 		GET | nova/sales/api/v1/companies/{id}/sales
		 */
		'sales' => 10,

		/**
		 * Para GET | nova/sales/api/v1/sellers
		 */
		'sellers' => 10,

		/**
		 * Para GET | nova/sales/api/v1/companies
		 */
		'companies' => 10,

		/**
		 * Para 
		 * 		GET | nova/sales/api/v1/commissions/companies
		 * 		GET | nova/sales/api/v1/companies/{id}/commissions
		 */
		'commissions_companies' => 10,

		/**
		 * Para 
		 * 		GET | nova/sales/api/v1/commissions/sellers
		 * 		GET | nova/sales/api/v1/sellers/{id}/commissions
		 */
		'commissions_sellers' => 10,

		/**
		 * Para GET | nova/sales/api/v1/commissions/types
		 */
		'commissions_types' => 10,

		/**
		 * Para GET | nova/sales/api/v1/companies/config/fixed
		 */
		'config_fixed_companies' => 10,

		/**
		 * Para GET | nova/sales/api/v1/sellers/config/fixed
		 */
		'config_fixed_seller' => 10,

		/**
		 * Para GET | nova/sales/api/v1/companies/config/sale-type
		 */
		'config_sale_type_companies' => 10,

		/**
		 * Para GET | nova/sales/api/v1/sellers/config/sale-type
		 */
		'config_sale_type_seller' => 10,

		/**
		 * Para GET | nova/sales/api/v1/companies/config/plan
		 */
		'config_plan_companies' => 10,

		/**
		 * Para GET | nova/sales/api/v1/sellers/config/plan
		 */
		'config_plan_seller' => 10,

		/**
		 * Para GET | nova/sales/api/v1/companies/config/plan-and-sale-type
		 */
		'config_plan_sale_type_companies' => 10,

		/**
		 * Para GET | nova/sales/api/v1/seller/config/plan-and-sale-type
		 */
		'config_plan_sale_type_seller' => 10,

		/**
		 * Para GET | nova/sales/api/v1/companies/config/service-type
		 */
		'config_service_type_companies' => 10,

		/**
		 * Para GET | nova/sales/api/v1/sellers/config/service-type
		 */
		'config_service_type_seller' => 10,
	],

	/**
	 * Middleware que se desea aplicar a las rutas que ofrece el paquete novatechnology/sales.
	 * Por ejemplo, si se desea que las rutas esten protegidas para que puedan ser visibles solo
	 * a los usuarios que han iniciado sesión:
	 * 
	 * 'middleware' => ['auth']
	 * 
	 * Se puede definir uno o varios middlewares, tanto los que trae Laravel por defecto como los
	 * creados por el desarrollador:
	 * 
	 * 'middleware' => ['auth', 'mi-propio-middleware'],
	 * 
	 */
	'middleware' => ['jwt.auth', 'addJSONHeader'],
];