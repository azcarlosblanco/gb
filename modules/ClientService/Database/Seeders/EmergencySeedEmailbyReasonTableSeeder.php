<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmergencySeedEmailbyReasonTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
			\DB::table('email_by_reason')->insert([
            'reason'      => "request_warranty_letter",
            'sender'      => "clientservice@gb.com",
            'subject'     => "[<TRAMITE_ID>]",
            'company_id'  => 1 
        ]);

			\DB::table('email_by_reason')->insert([
            'reason'      => "warranty_letter",
            'sender'      => "clientservice@gb.com",
            'subject'     => "[<TRAMITE_ID>]",
            'company_id'  => 1 
        ]);

	}

}