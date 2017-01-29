<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionClientServiceTableSeeder extends Seeder {

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



        $menu1=Menu::where('display_name','Tramites Pendientes')
						->first();
        if($menu1==null){
			$menu1=Menu::create([
							'display_name'=>'Tramites Pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.clientservice_pending',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>1,
							'module_code'=>'sgs_clientservice',
							]);

			$permission1 = Permission::create([
									"name"         => "clientservice_pending_access",
									"display_name" => "Ver Tramites Pendientes",
									"description"  => "Ver Tramites Pendientes",
								]);
			//association permission and roles
			$roles = array( $administracion->id );
			$permission1->roles()->sync($roles);
			//association permissions and menus
			$permission1->menus()->sync([$menu1->id]);
		}


		$menus4=Menu::where('display_name','Ticket')
		                  ->first();
		 if($menus4==null){
		 	$menus4=Menu::create([
                            'display_name'=>'Ticket',
                            'is_parent'=>0,
                            'level'=>1,
                            'link'=>'.ticket',
                            'icon'=>'glyphicon glyphicon-tasks',
                            'order'=>2,
                            'module_code'=>'sgs_clientservice',
                        ]);
        $permission4 = Permission::create([
                            "name"        => "ticket_access",
                            "display_name" => "Ticket",
                            "description"  => "Ticket",
                        ]);
        $roles = array( $administracion->id);
        $permission4->roles()->sync($roles);
        $permission4->menus()->sync([$menus4->id]);
		 }

		  $menus3=Menu::where('display_name','Hospitales')
                          ->first();
        if($menus3==null){$menus3=Menu::create([
							'display_name'=>'Hospitales',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.hospital',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>3,
							'module_code'=>'sgs_clientservice',
							]);

		$permission3 = Permission::create([
								"name"         => "hospital_access",
								"display_name" => "Ver Hospitales",
								"description"  => "Ver Hospitales",
							]);
		//association permission and roles
		$roles = array( $administracion->id );
		$permission3->roles()->sync($roles);
		$permission3->menus()->sync([$menus3->id]);

	}
         $menus2=Menu::where('display_name','Medicos')
						->first();
        if($menus2==null){
        	$menus2=Menu::create([
							'display_name'=>'Medicos',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.doctor',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>4,
							'module_code'=>'sgs_clientservice',
							]);

		$permission2 = Permission::create([
								"name"         => "doctor_access",
								"display_name" => "Ver Doctor",
								"description"  => "Ver Doctor",
							]);
		//association permission and roles
		$roles = array( $administracion->id );
		$permission2->roles()->sync($roles);
		$permission2->menus()->sync([$menus2->id]);
		//association permissions and menus

	}
        $menu=Menu::where('display_name','Especialidades Medicas')
						->first();
		if($menu==null){
			$menu=Menu::create([
							'display_name'=>'Especialidades Medicas',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.health_specialities',
							'icon'=>'glyphicon glyphicon-tasks',
							'order'=>5,
							'module_code'=>'sgs_clientservice',
							]);

			$permission = Permission::create([
									"name"         => "health_specialities_access",
									"display_name" => "Ver Especialidad Medicas",
									"description"  => "Ver Especialidad Medicas",
								]);
			//association permission and roles
			$roles = array( $administracion->id );
			$permission->roles()->sync($roles);
			//association permissions and menus
			$permission->menus()->sync([$menu->id]);
		}

   }

 }
