<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class PermissionManagerClientServiceSeederTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$client_service_role=Role::where('name','client_service')->first();
		$client_service_manager=Role::where('name','client_service_manager')->first();
		$administracion=Role::where('name','administracion')->first();

                  /****Servicio al cliente ****/
		$menu=Menu::where('display_name','Tramites Pendientes')
						->first();
	    $permission=Permission::where('name','clientservice_pending_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission->roles()->sync($role1);

			
		//association permissions and menus
			$permission->menus()->sync([$menu->id]);	


	    $menu2=Menu::where('display_name','Especialidades Medicas')
						->first();
	    $permission2=Permission::where('name','health_specialities_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission2->roles()->sync($role1);

			
		//association permissions and menus
			$permission2->menus()->sync([$menu2->id]);


		 $menu3=Menu::where('display_name','Medicos')
						->first();
	    $permission3=Permission::where('name','doctor_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission3->roles()->sync($role1);

			
		//association permissions and menus
			$permission3->menus()->sync([$menu3->id]);	


        $menu4=Menu::where('display_name','Hospitales')
						->first();
	    $permission4=Permission::where('name','hospital_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission4->roles()->sync($role1);

			
		//association permissions and menus
			$permission4->menus()->sync([$menu4->id]);	

		$menu5=Menu::where('display_name','Ticket')
						->first();
	    $permission5=Permission::where('name','ticket_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission5->roles()->sync($role1);

			
			$permission5->menus()->sync([$menu5->id]);	


                    /***General ***/
        $menu6=Menu::where('display_name','Póliza')
						->first();
	    $permission6=Permission::where('name','policy_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission6->roles()->sync($role1);

			
		//association permissions and menus
			$permission6->menus()->sync([$menu6->id]);	


		$menu7=Menu::where('display_name','Afiliados')
						->first();
	    $permission7=Permission::where('name','affiliate_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission7->roles()->sync($role1);

			
		//association permissions and menus
			$permission7->menus()->sync([$menu7->id]);	


		$menu8=Menu::where('display_name','Reclamos Historial')
						->first();
	    $permission8=Permission::where('name','claims_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission8->roles()->sync($role1);

		//association permissions and menus
			$permission8->menus()->sync([$menu8->id]);	


		$menu9=Menu::where('display_name','Mensajeros')
						->first();
	    $permission9=Permission::where('name','carrier_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission9->roles()->sync($role1);

		//association permissions and menus
			$permission9->menus()->sync([$menu9->id]);	


		$menu10=Menu::where('display_name','Proveedor Servicios')
						->first();
	    $permission10=Permission::where('name','supplier_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id);
			$permission10->roles()->sync($role1);

		//association permissions and menus
			$permission10->menus()->sync([$menu10->id]);



        

	}

}