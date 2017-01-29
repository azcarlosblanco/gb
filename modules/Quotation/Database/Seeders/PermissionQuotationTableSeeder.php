<?php namespace Modules\Quotation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionQuotationTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

                    
        $emission_role=Role::where('name','emision')->first();
        $emission_manager=Role::where('name','emision_manager')->first();
        $administracion=Role::where('name','administracion')->first();

        $m=Module::where("code","sgs_sales")
                    ->first();
        if($m==null){
            $module=Module::create(['display_name'=>'Ventas',
                                	'code'=>'sgs_sales',
                                    'module_order'=>4]);
        }
        $menu2=Module::where("display_name","Nueva Cotización")
                    ->first();
        if($menu2==null){
            $menu2=Menu::create([
                                'display_name'=>'Nueva Cotización',
                                'is_parent'=>0,
                                'level'=>1,
                                'link'=>'.new-quotation',
                                'icon'=>'glyphicon glyphicon-usd',
                                'order'=>1,
                                'module_code'=>'sgs_sales',
                            ]);
        }
        $menu1=Module::where("display_name","Cotizaciones")
                    ->first();
        if($menu1==null){
	        $menu1=Menu::create([
	                            'display_name'=>'Cotizaciones',
	                            'is_parent'=>0,
	                            'level'=>1,
	                            'link'=>'.quotation',
	                            'icon'=>'glyphicon glyphicon-usd',
	                            'order'=>2,
	                            'module_code'=>'sgs_sales',
	                        ]);
       	}
        $permission1 = Permission::create([
                            "name"        => "quotation_access",
                            "display_name" => "Ver Cotizaciones",
                            "description"  => "Ver Cotizaciones",
                        ]);
       	$permission2 = Permission::create([
                            "name"        => "quotation_create",
                            "display_name" => "Crear Cotización",
                            "description"  => "Crear Cotización",
                        ]);

        $roles = array( $emission_role->id,
                        $emission_manager->id,
                        $administracion->id);
        $permission1->roles()->sync($roles);
        $permission1->menus()->sync([$menu1->id,$menu2->id]);

        $permission2->roles()->sync($roles);
        $permission2->menus()->sync([$menu1->id,$menu2->id]);

        Model::reguard();
	}

}