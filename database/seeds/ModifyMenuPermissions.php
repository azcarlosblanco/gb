<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class ModifyMenuPermission extends Seeder
{
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

        /****************************  RRHH  ******************************/
		//employee menu
		$emp_permission = Permission::where("name","employee_access")->first();
		$emp_permission->roles()->sync([$administracion->id]);
		
		//ventas
		$agent_permission = Permission::where("name","sales_access")->first();
		$agent_permission->roles()->sync([$administracion->id]);

		//doctor
		$doctor_permission = Permission::where("name","doctor_access")->first();
		$doctor_permission->roles()->sync([$administracion->id]);

		//health
		$sp_permission = Permission::where("name","health_specialities_access")->first();
		$sp_permission->roles()->sync([$administracion->id]);

    }
}
