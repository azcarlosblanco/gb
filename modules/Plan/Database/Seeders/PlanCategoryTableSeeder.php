<?php namespace Modules\Plan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PlanCategoryTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();


		\DB::table('plan_category')->insert([
			array(
					'name' => 'bdil',
					'display_name' => 'Best Doctors Internacional',
					'insurance_company_id' => 1
			),
			array(
					'name' => 'ppga',
					'display_name' => 'Prepagada',
					'insurance_company_id' => 1
			)
		]);

	}

}
