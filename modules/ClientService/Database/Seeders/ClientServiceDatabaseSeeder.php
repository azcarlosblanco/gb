<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ClientServiceDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		 Model::unguard();

        $this->call("\Modules\ClientService\Database\Seeders\PermissionClientServiceTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\TicketCategorySeedersTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\EmailReasonTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\ProcedureClientServiceSeederTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\PermissionManagerClientServiceSeederTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\TicketPermissionSeederTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\EmergencySeedEmailbyReasonTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\CategoryEmergencyTableSeeder");
        $this->call("\Modules\ClientService\Database\Seeders\HospitalizationClientServiceTableSeeder");

        $this->call("\Modules\ClientService\Database\Seeders\TypeHospitalizationPermissionTableSeeder");

	}

}
