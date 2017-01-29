<?php namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PaymentPermissionsSeeder extends Seeder {

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

        $m=Module::where("code","sgs_general")->first();

        if($m==null){
            $module=Module::create(['display_name'=>'General',
                                    'code'=>'sgs_general',
                                    'module_order'=>6]);
        }
        $menus7=Menu::create([
                            'display_name'=>'Pago Poliza',
                            'is_parent'=>0,
                            'level'=>1,
                            'link'=>'.pago-poliza',
                            'icon'=>'glyphicon glyphicon-tasks',
                            'order'=>7,
                            'module_code'=>'sgs_general',
                            'display' => 0,
                        ]);
        $permission7 = Permission::create([
                            "name"        => "paypolicy_access",
                            "display_name" => "Pago de Poliza",
                            "description"  => "Pago de Poliza",
                        ]);
        $roles = array( $emission_role->id,
                        $emission_manager->id,
                        $administracion->id);
        $permission7->roles()->sync($roles);
        $permission7->menus()->sync([$menus7->id]);

        Model::reguard();
	}

}
