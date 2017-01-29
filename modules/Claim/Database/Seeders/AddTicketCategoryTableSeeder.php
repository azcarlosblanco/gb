<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AddTicketCategoryTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		\DB::table('ticket_cat')->insert([
            'name'         => "invoice_no_settle",
            'display_name' => 'Factura no liquidada',
            'category'     => 'claim'
        ]);
	}

}