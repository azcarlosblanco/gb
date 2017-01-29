<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AddCategoriesTicketToClaimsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		\DB::table('ticket_cat')->insert([
            'name'         => "deductible_not_match",
            'display_name' => 'Deducible calculado y recibo no coinciden',
            'category'     => 'settlement'
        ]);

        \DB::table('ticket_cat')->insert([
            'name'         => "value_uncovered",
            'display_name' => 'Valores no cubiertos',
            'category'     => 'settlement'
        ]);

        \DB::table('ticket_cat')->insert([
            'name'         => "invalid_claim_file",
            'display_name' => 'Archivos reclamos no válidos',
            'category'     => 'claim'
        ]);

				\DB::table('ticket_cat')->insert([
						'name'         => "invoice_no_settle",
						'display_name' => 'Factura no liquidada',
						'category'     => 'claim'
				]);
	}

}
