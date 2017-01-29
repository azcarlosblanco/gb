<?php namespace Modules\Reception\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ReceptionDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call("Modules\Reception\Database\Seeders\ProcedureDocumentTableSeeder");
		$this->call("Modules\Reception\Database\Seeders\PermissionReceptionTableSeeder");
		Model::reguard();
	}

}
