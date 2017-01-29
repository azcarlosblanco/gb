<?php namespace Modules\Quotation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Email\Entities\EmailByReason;

class EmailQuotationTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		$data['reason']='quotation';
		$data['sender']='quotation@gb.com';
		$data['subject']='Cotización: <CUSTOMER>';
		$data['template']="";
		$data['template_html']="quotation::email_quotation";
		$email=EmailByReason::create($data);
	}

}