<?php namespace Modules\Dashboard\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class EmisionDashboardSeederTableSeeder extends Seeder {

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

		$module=Module::where("code","sgs_dashboard")
						->first();
		if($module==null){
			$module = Module::create(['display_name'=>'Dashboard',
										'code'=>'sgs_dashboard',
										'module_order'=>6]);
		} 

		 $menu1=Menu::where('display_name','Emisiones')
						->first();
        if($menu1==null){
			$menu1=Menu::create([
							'display_name'=>'Emisiones',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.dashboard-emission',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>1,
							'module_code'=>'sgs_dashboard',
							]);
			
			$permission1 = Permission::create([
									"name"         => "dashboard-emission_access",
									"display_name" => "Emisiones",
									"description"  => "Emisiones",
								]);
			//association permission and roles
            $roles = array( $administracion->id,
                            $emission_role ->id,
                            $emission_manager->id );
			$permission1->roles()->sync($roles);
			//association permissions and menus
			$permission1->menus()->sync([$menu1->id]);	

      }
  }
}