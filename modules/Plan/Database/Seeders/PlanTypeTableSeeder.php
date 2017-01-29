<?php namespace Modules\Plan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Plan\Entities\PlanType;

class PlanTypeTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		PlanType::create([
					'name'        =>  'Individual',
					'description' =>  'Individual',
					'num_members'  =>  1
						]);

		PlanType::create([
					'name'        =>  '2 Members',
					'description' =>  'Familia de 2 mienbros',
					'num_members'  =>  2
						]);

		PlanType::create([
					'name'        =>  'Familiar',
					'description' =>  'Toda la familia',
					'num_members'  =>  3
						]);

	}

}