<?php namespace Modules\Plan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionsPlanSeeder extends Seeder {

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

        $m=Module::where("code","sgs_general")
                    ->first();
        if($m==null){
            $module=Module::create(['display_name'=>'General',
                                'code'=>'sgs_general',
                                'module_order'=>6]);
        }
        $menus4=Menu::create([
                            'display_name'=>'Planes',
                            'is_parent'=>0,
                            'level'=>1,
                            'link'=>'.planes',
                            'icon'=>'glyphicon glyphicon-tasks',
                            'order'=>4,
                            'module_code'=>'sgs_general',
                        ]);
        $permission4 = Permission::create([
                            "name"        => "plans_access",
                            "display_name" => "Ver Planes",
                            "description"  => "Ver Planes",
                        ]);
        $roles = array( $reception_role->id,
                        $reception_manager->id,
                        $emission_role->id,
                        $emission_manager->id,
                        $claims_role->id,
                        $administracion->id);
        $permission4->roles()->sync($roles);
        $permission4->menus()->sync([$menus4->id]);

        Model::reguard();
	}

}