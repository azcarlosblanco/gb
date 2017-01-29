<?php namespace Modules\Observation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ObservationTypesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		\DB::table('observation_type')->insert([
			array('id'=> 1, 'name' => 'Subsanable por Usuario'),
			array('id'=> 2, 'name' => 'Subsanable por Cliente'),
			array('id'=> 3, 'name' => 'No Subsanable')
		]);
	}

}
