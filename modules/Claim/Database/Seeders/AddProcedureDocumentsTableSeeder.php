<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AddProcedureDocumentsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		$id = \DB::table('procedure_catalog')
              ->where('name','settlement')
              ->value('id');

		Model::unguard();
		
		\DB::table('procedure_document')->insert([
	        array('name' => 'claim_letter', 'description' => 'Carta de Reclamo', 'type' => 'form', 'procedure_catalog_id'=>$id),
	        array('name' => 'claim_form', 'description' => 'Formulario de Reclamo', 'type' => 'form', 'procedure_catalog_id'=>$id),
	        array('name' => 'claim_settlement_payment', 'description' => 'Comprobonate Pago', 'type' => 'payment', 'procedure_catalog_id'=>$id),
	        array('name' => 'claim_settlement_eob', 'description' => 'EOB', 'type' => 'form', 'procedure_catalog_id'=>$id),
      	]);
	}

}