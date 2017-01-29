<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CategoryEmergencyTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
     		
    		\DB::table('ticket_cat')->insert([
                'name'         => "warranty_letter_incorrect",
                'display_name' => 'Carta de Garantía incorrecta',
            ]);
	}

}