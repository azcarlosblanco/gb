<?php namespace Modules\Renovation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionRenovationsTableSeeder extends Seeder {

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

        /****************************  Renovation  ******************************/
        $module = Module::where("code","sgs_renovation")
                            ->first();
        if($module==null){
            $module=Module::create(['display_name'=>'Renovaciones',
                                'code'=>'sgs_renovation',
                                'module_order'=>3]);
        }
        $menus1 = Menu::where("display_name",'Cargar Renovaciones')
                        ->where('module_code','sgs_renovation')
                        ->first();

        if ($menus1 == null) {
            $menus1=Menu::create([
                'display_name'=>'Cargar Renovaciones',
                'is_parent'=>0,
                'level'=>1,
                'link'=>'.add-new-file',
                'icon'=>'glyphicon glyphicon-refresh',
                'order'=>1,
                'module_code'=>'sgs_renovation',
            ]);
        }

        $menus2 = Menu::where("display_name",'Renovaciones')
                        ->where('module_code','sgs_renovation')
                        ->first();

        if ($menus2 == null) {
            $menus2=Menu::create([
                'display_name'=>'Renovaciones',
                'is_parent'=>0,
                'level'=>1,
                'link'=>'.renovations',
                'icon'=>'glyphicon glyphicon-th-list',
                'order'=>2,
                'module_code'=>'sgs_renovation',
            ]);
        }

        $permission1 = Permission::create([
                                "name"        => "renovation",
                                "display_name" => "Renovar Polízas",
                                "description"  => "Cargar archivo para renovar polizas",
                            ]);
        // $permission2 = Permission::create([
        //                         "name"        => "employee_create",
        //                         "display_name" => "Crear Empleados",
        //                         "description"  => "Crear Empleados",
        //                     ]);
        // $permission3 = Permission::create([
        //                         "name"        => "employee_delete",
        //                         "display_name" => "Crear Empleados",
        //                         "description"  => "Crear Empleados",
        //                     ]);
        // $permission4 = Permission::create([
        //                         "name"        => "employee_edit",
        //                         "display_name" => "Crear Empleados",
        //                         "description"  => "Crear Empleados",
        //                     ]);

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
        $menuIDs = [
                        $menus1->id,
                        $menus2->id,
                    ];
        $permission1->menus()->sync($menuIDs);

        // $permission2->roles()->sync([$administracion->id]);
        // //association permissions and menus
        // $permission2->menus()->sync([$menus1->id]);
        //
        // $permission3->roles()->sync([$administracion->id]);
        // //association permissions and menus
        // $permission3->menus()->sync([$menus1->id]);
        //
        // $permission4->roles()->sync([$administracion->id]);
        // //association permissions and menus
        // $permission4->menus()->sync([$menus1->id]);
	}

}
