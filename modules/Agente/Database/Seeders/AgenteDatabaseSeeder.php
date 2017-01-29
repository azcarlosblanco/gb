<?php namespace Modules\Agente\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;


class AgenteDatabaseSeeder extends Seeder {

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

        $module=Module::where("code","sgs_sales")
                    ->first();
        if($module==null){
            $module=Module::create(['display_name'=>'Ventas',
                                    'code'=>'sgs_sales',
                                    'module_order'=>4]);
        }

        $menus1=Menu::create([
                            'display_name'=>'Agentes',
                            'is_parent'=>0,
                            'level'=>1,
                            'link'=>'.sales',
                            'icon'=>'glyphicon glyphicon-tasks',
                            'order'=>3,
                            'module_code'=>'sgs_sales',
                            ]);

        $permission1 = Permission::create([
                                "name"        => "sales_access",
                                "display_name" => "Ver Agentes",
                                "description"  => "Ver Agentes",
                            ]);
        //association permission and roles
        $roles = array( $reception_role->id,
                        $reception_manager->id,
                        $emission_role->id,
                        $emission_manager->id,
                        $claims_role->id,
                        $administracion->id);
        $permission1->roles()->sync($roles);
        //association permissions and menus
        $permission1->menus()->sync([$menus1->id]);
	}

}