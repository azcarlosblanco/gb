<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class ReportPermissionTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$claims_role=Role::where('name','claims')->first();
		$administracion=Role::where('name','administracion')->first();

		$module = Module::where('code','sgs_claims')
							->first();
		if($module==null){
			$module=Module::create(['display_name' => 'Reclamos',
								'code'         => 'sgs_claims',
								'module_order' => 3]);
		}
		$menus1[]=Menu::create([
							'display_name'=>'Reporte',
							'is_parent'=>0,
							'level'=>4,
							'link'=>'.reporte',
							'icon'=>'glyphicon glyphicon-list-alt',
							'order'=>1,
							'module_code'=>'sgs_claims',
							]);

		$permission1 = Permission::create([
								"name"        => "claims_view_report",
								"display_name" => "Ver Reporte",
								"description"  => "ver reporte",
							]);

		//association of permissions and role
		$roles= array($claims_role->id,
			           $administracion->id);

		$permission1->roles()->sync($roles);
		//association of permissions and menu
		
		$menuIDs = array();
		foreach ($menus1 as $key => $menu) {
			$menuIDs[]=$menu->id;
		}
		$permission1->menus()->sync($menuIDs);
		
	}

}