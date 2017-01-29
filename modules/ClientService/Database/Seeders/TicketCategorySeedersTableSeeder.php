<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TicketCategorySeedersTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()

	{
		Model::unguard();

		\DB::table('ticket_cat')->insert([
            'name'         => "add_baby",
            'display_name' => 'Registro de bebe en poliza'

        ]);

       \DB::table('ticket_cat')->insert([
            'name'         => "missing_files",
            'display_name' => 'Archivo perdido'

        ]);
	}

}