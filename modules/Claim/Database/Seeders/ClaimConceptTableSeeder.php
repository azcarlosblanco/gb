<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ClaimConceptTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		\DB::table('claim_concept')->insert([
					'name' => 'rutina',
					'display_name' => 'Rutina'
		]);
		\DB::table('claim_concept')->insert([
					'name' => 'cirugia',
					'display_name' => 'Cirugia-Honorarios'
		]);
		\DB::table('claim_concept')->insert([
					'name' => 'consulta',
					'display_name' => 'Consulta Medica'
		]);
		\DB::table('claim_concept')->insert([
					'name' => 'laboratorio',
					'display_name' => 'Laboratorio'
		]);
		\DB::table('claim_concept')->insert([
					'name' => 'images',
					'display_name' => 'Imagenologia'
		]);
		\DB::table('claim_concept')->insert([
					'name' => 'medicina',
					'display_name' => 'Medicina'
		]);
	}

}
