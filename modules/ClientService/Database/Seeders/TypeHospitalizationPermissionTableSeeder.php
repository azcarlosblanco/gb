<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class TypeHospitalizationPermissionTableSeeder extends Seeder {

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
       
		$module=Module::where("code","sgs_clientservice")
						->first();
		if($module==null){
			$module = Module::create(['display_name'=>'Servicio al Cliente',
										'code'=>'sgs_clientservice',
										'module_order'=>5]);
		} 


		
        $menu1=Menu::where('display_name','Tipo Hospitalización')
						->first();
        if($menu1==null){
			$menu1=Menu::create([
							'display_name'=>'Tipo Hospitalización',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.type_hospitalization',
							'icon'=>'glyphicon glyphicon-home',
							'order'=>6,
							'module_code'=>'sgs_clientservice',
							]);
			
			$permission1 = Permission::create([
									"name"         => "type_hospitalization_access",
									"display_name" => "Ver Tipo Hospitalización",
									"description"  => "Ver Tipo Hospitalización",
								]);
			//association permission and roles
			$roles = array( $administracion->id );
			$permission1->roles()->sync($roles);
			//association permissions and menus
			$permission1->menus()->sync([$menu1->id]);	
		}

	}
}