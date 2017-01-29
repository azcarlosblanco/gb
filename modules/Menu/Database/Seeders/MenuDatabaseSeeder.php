<?php namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class MenuDatabaseSeeder extends Seeder {

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

		$renewal_role  =Role::where('name','renovacion')->first();

		$emission_manager=Role::where('name','emision_manager')->first();
		$claims_role=Role::where('name','claims')->first();
		$administracion=Role::where('name','administracion')->first();
		$agente=Role::where('name','agente')->first();

		/*********** EMISSIONS **************************/
		$module = Module::where('code','sgs_emission')
							->first();
		if($module==null){
			$module=Module::create(['display_name' => 'Emisión',
									'code'         => 'sgs_emission',
									'module_order' => 2]);
		}

		$menu1 = Menu::where("display_name",'Emisiones pendiente')
						->where('module_code','sgs_emission')
						->first();
		if($menu1==null){
			$menu1 = Menu::create([
							'display_name'=>'Emisiones pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.emision',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>1,
							'module_code'=>'sgs_emission',
							]);
		}
		$menu2 = Menu::where("display_name",'Emisiones Actuales')
						->where('module_code','sgs_emission')
						->first();
		if($menu2==null){
			$menu2 = Menu::create([
							'display_name'=>'Emisiones Actuales',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.emision-actuales',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>2,
							'module_code'=>'sgs_emission',
							]);
		}
		$menu3 = Menu::where("display_name",'Histórico Emisiones')
						->where('module_code','sgs_emission')
						->first();
		if($menu3==null){
			$menu3 = Menu::create([
							'display_name'=>'Histórico Emisiones',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.emision-tiempos',
							'icon'=>'glyphicon glyphicon-time',
							'order'=>3,
							'module_code'=>'sgs_emission',
							]);
		}

		//creation of permission
		$permission1 = Permission::create([
								"name"        => "emission_history_policy",
								"display_name" => "Emitir Póliza Nueva",
								"description"  => "emitir una poliza nueva",
							]);
		//association of permission to role
		$roles = array($emission_role->id,
					  $emission_manager->id,
					  $administracion->id);
		$permission1->roles()->sync($roles);
		$menuIDs=array(
						$menu1->id,
						$menu3->id,
					  );
		$permission1->menus()->sync($menuIDs);

		$permission2 = Permission::create([
								"name"        => "view_poliza_state",
								"display_name" => "Ver Poliza Emisión",
								"description"  => "Ver Poliza Emisión",
							]);
		//association of permission to role
		$roles = array($emission_role->id,
					  $emission_manager->id,
					  $administracion->id,
					  $agente->id);
		$permission2->roles()->sync($roles);
		$menuIDs=array(
						$menu2->id,
					  );
		$permission2->menus()->sync($menuIDs);


		/*********** CLAIMS **************************/
		$module = Module::where('code','sgs_claims')
							->first();
		if($module==null){
			$module=Module::create(['display_name' => 'Reclamos',
								'code'         => 'sgs_claims',
								'module_order' => 3]);
		}
		$menus1[]=Menu::create([
							'display_name'=>'Reclamos Pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.reclamos',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>1,
							'module_code'=>'sgs_claims',
							]);
		$menus1[]=Menu::create([
							'display_name'=>'Liquidaciones Pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.reclamos-liquidaciones',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>2,
							'module_code'=>'sgs_claims',
							]);
		$menus1[]=Menu::create([
							'display_name'=>'Reclamos Tiempos',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.reclamos-tiempos',
							'icon'=>'glyphicon glyphicon-time',
							'order'=>3,
							'module_code'=>'sgs_claims',
							]);

		//creation of permission
		$permission1 = Permission::create([
								"name"        => "claims_create_claim",
								"display_name" => "Emitir Póliza",
								"description"  => "emitir una poliza nueva",
							]);
		//association of permissions and role
		$roles = array($claims_role->id,
					  $administracion->id);
		$permission1->roles()->sync($roles);
		//association of permissions and menu
		$menuIDs = array();
		foreach ($menus1 as $key => $menu) {
			$menuIDs[]=$menu->id;
		}
		$permission1->menus()->sync($menuIDs);

		/**********Renovaciones***************/

		$module = Module::where('code','sgs_renewal')
							->first();
		if($module==null){
			$module=Module::create(['display_name' => 'Renovación',
									'code'         => 'sgs_renewal',
									'module_order' => 8]);
		}

		$menu1 = Menu::where("display_name",'Nueva Renovación')
						->where('module_code','sgs_renewal')
						->first();

		if($menu1==null){
			$menu1 = Menu::create([
							'display_name'=>'Nueva Renovación',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.renewal',
							'icon'=>'glyphicon glyphicon-pencil',
							'order'=>1,
							'module_code'=>'sgs_renewal',
							]);
		}

		//creation of permission
		$permission1 = Permission::create([
								"name"        => "renewal_access",
								"display_name" => "Emitir Renovación",
								"description"  => "create renewal by policy",
							]);
		//association of permission to role
		$roles = array($emission_role->id,
						$emission_manager->id,
						$administracion->id);
		$permission1->roles()->sync($roles);
		$menuIDs=array(
						$menu1->id,
						$menu3->id,
						);
		$permission1->menus()->sync($menuIDs);

		/*$permission2 = Permission::create([
								"name"        => "view_emission_state",
								"display_name" => "Ver Estado de la Emisión",
								"description"  => "Ver Estado de la Emisión",
							]);*/
		//association of permission to role
		$roles = array($emission_role->id,
						$emission_manager->id,
						$administracion->id,
						$agente->id);
		$permission2->roles()->sync($roles);
		$menuIDs=array(
						$menu2->id,
						);
		$permission2->menus()->sync($menuIDs);








		/*********** GENERAL ********************/
		$m=Module::where("code","sgs_general")
                    ->first();
        if($m==null){
            $module=Module::create(['display_name' => 'General',
                                	'code'         => 'sgs_general',
                                	"module_order" => 6]);
        }
		$menus1=Menu::create([
							'display_name'=>'Póliza',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.polizas',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>1,
							'module_code'=>'sgs_general',
							]);
		$menus2=Menu::create([
							'display_name'=>'Afiliados',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.afiliados',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>2,
							'module_code'=>'sgs_general',
							]);
		$menus3=Menu::create([
							'display_name'=>'Reclamos Historial',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.reclamos-historial',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>3,
							'module_code'=>'sgs_general',
							'display' => 0
							]);
		$menus5=Menu::create([
							'display_name'=>'Mensajeros',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.mensajeros',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>4,
							'module_code'=>'sgs_general',
							]);
		$menus6=Menu::create([
							'display_name'=>'Proveedor Servicios',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.proveedorservicios',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>5,
							'module_code'=>'sgs_general',
							]);
		//creation of permission
		$permissions = array();
		$permission1 = Permission::create([
								"name"        => "policy_access",
								"display_name" => "Ver Pólizas",
								"description"  => "Ver pólizas",
							]);
		$permission2 = Permission::create([
								"name"        => "affiliate_access",
								"display_name" => "Ver Afiliados",
								"description"  => "Ver Afiliados",
							]);
		$permission3 = Permission::create([
								"name"        => "claims_access",
								"display_name" => "Ver Historial Reclamos",
								"description"  => "Ver Historial Reclamos",
							]);
		$permission5 = Permission::create([
								"name"        => "carrier_access",
								"display_name" => "Ver Mensajeros",
								"description"  => "Ver Mensajeros",
							]);
		$permission6 = Permission::create([
								"name"        => "supplier_access",
								"display_name" => "Ver Proveedor de Servicios",
								"description"  => "Ver Proveedor de Servicios",
							]);

		//association permission and roles
		$roles = array( $reception_role->id,
						$reception_manager->id,
						$emission_role->id,
						$emission_manager->id,
						$claims_role->id,
					  	$administracion->id);
		$permissions = array($permission1,
							$permission2,
							$permission3,
							$permission5,
							$permission6);
		foreach ($permissions as $permission) {
			$permission->roles()->sync($roles);
		}
		//association permission and menus
		$menus = array($menus1,
						$menus2,
						$menus3,
						$menus5,
						$menus6);
		foreach ($permissions as $key => $permission) {
			$permission->menus()->sync([$menus[$key]->id]);
		}

	}
}
