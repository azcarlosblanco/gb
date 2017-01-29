<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailReasonTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		\DB::table('email_by_reason')->insert([
            'reason'      => "clientservice_ticket",
            'sender'      => "clientservice@gb.com",
            'subject'     => "[<TRAMITE_ID>][<CATEGORIA>]",
            'company_id'  => 1 
        ]);

     }

}