<?php namespace Modules\Reception\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionReceptionTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		
		Model::unguard();
		$reception_role=Role::where('name','recepcion')->first();
		$reception_manager=Role::where('name','recepcion_manager')->first();
		$emission_role=Role::where('name','emision')->first();
		$emission_manager=Role::where('name','emision_manager')->first();
		$administracion=Role::where('name','administracion')->first();

	
		\DB::table('permission')->delete();

		/**********Recepcion**********/
		$module = Module::where("code","sgs_reception")
							->first();
		if($module==null){
			$module = Module::create(['display_name'=>'Recepción',
										'code'=>'sgs_reception',
										'module_order'=>1]);
		}

		$menu1 = Menu::where("display_name",'Nueva Póliza')
						->where('module_code','sgs_reception')
						->first();
		if($menu1==null){
			$menu1=Menu::create([
							'display_name'=>'Nueva Póliza',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.recepcion-emisiones.nueva-poliza',
							'icon'=>'glyphicon glyphicon-pencil',
							'order'=>1,
							'module_code'=>'sgs_reception',
							]);
		}
		$menu2 = Menu::where("display_name",'Nuevo Reclamo')
						->where('module_code','sgs_reception')
						->first();
		if($menu2==null){
			$menu2=Menu::create([
							'display_name'=>'Nuevo Reclamo',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.recepcion-reclamos.nuevo-reclamo',
							'icon'=>'glyphicon glyphicon-pencil',
							'order'=>2,
							'module_code'=>'sgs_reception',
							]);
		}
		$menu3 = Menu::where("display_name",'Emisiones Pendientes')
						->where('module_code','sgs_reception')
						->first();
		if($menu3==null){
			$menu3=Menu::create([
							'display_name'=>'Emisiones Pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.recepcion-emisiones',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>3,
							'module_code'=>'sgs_reception',
							]);
		}
		$menu4 = Menu::where("display_name",'Reclamos Pendientes')
						->where('module_code','sgs_reception')
						->first();
		if($menu4==null){
			$menu4=Menu::create([
							'display_name'=>'Reclamos Pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.recepcion-reclamos',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>4,
							'module_code'=>'sgs_reception',
							]);
		}
		$menu5 = Menu::where("display_name",'Liquidaciones Pendientes')
						->where('module_code','sgs_reception')
						->first();
		if($menu5==null){
			$menu5=Menu::create([
							'display_name'=>'Liquidaciones Pendientes',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.recepcion-liquidaciones',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>5,
							'module_code'=>'sgs_reception',
							]);
		}
		$menu6 = Menu::where("display_name",'Envío Documentos')
						->where('module_code','sgs_reception')
						->first();
		if($menu6==null){
			$menu6=Menu::create([
							'display_name'=>'Envío Documentos',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.envio-documentos({receiver: null})',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>6,
							'module_code'=>'sgs_reception',
							]);
		}
		$menu7 = Menu::where("display_name",'Envío Documentos')
						->where('module_code','sgs_reception')
						->first();
		if($menu7==null){
			$menu7=Menu::create([
							'display_name'=>'Recepción General Documentos',
							'is_parent'=>0,
							'level'=>1,
							'link'=>'.recepcion-documentos',
							'icon'=>'glyphicon glyphicon-th-list',
							'order'=>7,
							'module_code'=>'sgs_reception',
							]);
		}

		//creation of permission
		$permission1 = Permission::create([
								"name"        => "reception_emit_policy",
								"display_name" => "Emitir Póliza",
								"description"  => "emitir una poliza nueva",
							]);
		$permission2 = Permission::create([
								"name"        => "reception_claim",
								"display_name" => "Realizar Reclamo",
								"description"  => "Registrar Papeles Reclamos",
							]);
		$permission3 = Permission::create([
								"name"        => "reception_receive_docs",
								"display_name" => "Recibir Documentos Varios",
								"description"  => "Recibir Documentos Varios",
							]);
		//creation of permission
		$permission4 = Permission::create([
								"name"        => "dispach_documents",
								"display_name" => "Envio Documentos",
								"description"  => "enviar documentos",
							]);

		$roles = array($reception_role->id,
					  $reception_manager->id,
					  $administracion->id);
		$permission1->roles()->sync($roles);
		$permission2->roles()->sync($roles);
		$permission3->roles()->sync($roles);
		$permission4->roles()->sync($roles);

		$menuIDs = array(
							$menu1->id,
							$menu2->id,
							$menu3->id,
							$menu4->id,
							$menu5->id,
							$menu6->id,
							$menu7->id,
						);

		$permission1->menus()->sync($menuIDs);
		$permission2->menus()->sync($menuIDs);
		$permission3->menus()->sync($menuIDs);
		$permission4->menus()->sync($menuIDs);
	}
}