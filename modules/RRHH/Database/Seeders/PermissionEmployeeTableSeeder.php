<?php namespace Modules\RRHH\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionEmployeeTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$reception_role=Role::where('name','recepcion')->first();
		$reception_manager=Role::where('name','recepcion_manager')->first();
		$emission_role=Role::where('name','emision')->first();
		$emission_manager=Role::where('name','emision_manager')->first();
		$claims_role=Role::where('name','claims')->first();
		$administracion=Role::where('name','administracion')->first();
		$agente=Role::where('name','agente')->first();
		
		/****************************  RRHH  ******************************/
		$module = Module::where("code","sgs_rrhh")
							->first();
		if($module==null){
			$module=Module::create(['display_name'=>'RRHH',
								'code'=>'sgs_rrhh',
								'module_order'=>7]);
		}
		$menus1=Menu::create([
							'display_name'=>'Empleados',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.empleados',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>1,
							'module_code'=>'sgs_rrhh',
							]);
		$permission1 = Permission::create([
								"name"        => "employee_access",
								"display_name" => "Ver Empleados",
								"description"  => "Ver Empleados",
							]);
		$permission2 = Permission::create([
								"name"        => "employee_create",
								"display_name" => "Crear Empleados",
								"description"  => "Crear Empleados",
							]);
		$permission3 = Permission::create([
								"name"        => "employee_delete",
								"display_name" => "Crear Empleados",
								"description"  => "Crear Empleados",
							]);
		$permission4 = Permission::create([
								"name"        => "employee_edit",
								"display_name" => "Crear Empleados",
								"description"  => "Crear Empleados",
							]);

		//association permission and roles
		$roles = array( /*$reception_role->id,
						$reception_manager->id,
						$emission_role->id,
						$emission_manager->id,
						$claims_role->id,
						$agente->id,*/
					  	$administracion->id);
		$permission1->roles()->sync($roles);
		//association permissions and menus
		$permission1->menus()->sync([$menus1->id]);

		$permission2->roles()->sync([$administracion->id]);
		//association permissions and menus
		$permission2->menus()->sync([$menus1->id]);

		$permission3->roles()->sync([$administracion->id]);
		//association permissions and menus
		$permission3->menus()->sync([$menus1->id]);

		$permission4->roles()->sync([$administracion->id]);
		//association permissions and menus
		$permission4->menus()->sync([$menus1->id]);
	}

}