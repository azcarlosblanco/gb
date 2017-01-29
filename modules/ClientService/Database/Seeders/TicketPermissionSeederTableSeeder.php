<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Authorization\Entities\Role;
use Modules\Menu\Entities\Module;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuPermission;
use Modules\Authorization\Entities\Permission;

class TicketPermissionSeederTableSeeder extends Seeder {

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
		$reception_role=Role::where('name','recepcion')->first();
		$reception_manager=Role::where('name','recepcion_manager')->first();
		$emission_role=Role::where('name','emision')->first();
		$emission_manager=Role::where('name','emision_manager')->first();
		$claims_role=Role::where('name','claims')->first();
		$administracion=Role::where('name','administracion')->first();

                  /****Servicio al cliente ****/
		$menu=Menu::where('display_name','Ticket')
						->first();
	    $permission=Permission::where('name','ticket_access')
						->first();
	
        //association permission and roles
			$role1 = array( $client_service_role->id,
			                $administracion ->id,
			                $client_service_manager->id,
			                $reception_role->id,
			                $reception_manager->id,
			                $emission_role->id,
			                $emission_manager->id,
			                $claims_role->id);

			$permission->roles()->sync($role1);

			
		//association permissions and menus
			$permission->menus()->sync([$menu->id]);	

	}

}