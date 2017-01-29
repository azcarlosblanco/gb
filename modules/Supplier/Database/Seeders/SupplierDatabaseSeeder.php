<?php namespace Modules\Supplier\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SupplierDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$id = 1;

		\DB::table('supplier_category')->insert([
			array('id' => $id, 'name' => 'general')
		]);

		\DB::table('supplier')->insert([
			array('category' => $id, 'name' => 'InterLab', 'description' => 'InterLab'),
			array('category' => $id, 'name' => 'Clinica Alcivar', 'description' => 'Clinica Alcivar')
		]);
	}

}
