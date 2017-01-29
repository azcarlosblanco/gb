<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ClaimDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		//$this->call("\Modules\Claim\Database\Seeders\DeleteClaimsSeederTableSeeder");
		//$this->call("\Modules\Claim\Database\Seeders\ReportPermissionTableSeeder");
		$this->call("\Modules\Claim\Database\Seeders\AddTicketCategoryTableSeeder");
	}

}
