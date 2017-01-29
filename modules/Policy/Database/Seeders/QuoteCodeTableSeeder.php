<?php namespace Modules\Policy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class QuoteCodeTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		\DB::table('plan')->where('id', '<=', 2)->update(['plan_category_id' => 2]);

		\DB::table('quote_code')->insert([
			array(
					'table_type' => 'number_payments',
					'table_id' => 1,
					'value' => 4,
					'insurance_company_id' => 1
			),
			array(
				'table_type' => 'number_payments',
				'table_id' => 2,
				'value' => 3,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'number_payments',
				'table_id' => 3,
				'value' => 2,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'affiliate_role',
				'table_id' => 1,
				'value' => 1,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'affiliate_role',
				'table_id' => 2,
				'value' => 2,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'affiliate_role',
				'table_id' => 3,
				'value' => 3,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'plan',
				'table_id' => 1,
				'value' => 37,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'plan_deducible',
				'table_id' => 1,
				'value' => 53,
				'insurance_company_id' => 1
			),
			array(
				'table_type' => 'plan_deducible',
				'table_id' => 2,
				'value' => 54,
				'insurance_company_id' => 1
			)
		]);
	}

}
