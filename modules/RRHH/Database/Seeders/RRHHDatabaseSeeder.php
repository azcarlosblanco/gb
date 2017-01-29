<?php namespace Modules\RRHH\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\RRHH\Entities\Department;

class RRHHDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call("\Modules\RRHH\Database\Seeders\PermissionEmployeeTableSeeder");
		
		Department::create([
							"name" => "sales",
							"description" => "Ventas"
						  ]);

		Department::create([
							"name" => "administration",
							"description" => "Administracion"
						  ]);

		Department::create([
							"name" => "reception",
							"description" => "Recepción"
						  ]);

		Department::create([
							"name" => "emissions",
							"description" => "Emisiones"
						  ]);

		Department::create([
							"name" => "claims",
							"description" => "Reclamos"
						  ]);
	}

}