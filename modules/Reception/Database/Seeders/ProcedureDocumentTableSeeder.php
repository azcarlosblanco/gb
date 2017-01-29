<?php namespace Modules\Reception\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\ProcedureDocument;

class ProcedureDocumentTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(){
		$procedure_catalog_id = \DB::table('procedure_catalog')
								->where('name','newpolicy')
								->value('id');

		if( !empty($procedure_catalog_id) ){
			$names = \DB::table('procedure_document')
									->where('procedure_catalog_id', $procedure_catalog_id)
									->pluck('id', 'name');

			$to_insert[] = array('name'=>'form',
													 'description'=>'Aplicación',
													 'type'=>'form',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'id',
													 'description'=>'Documento Identificacion',
													 'type'=>'id',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'paycheck',
													 'description'=>'Cheque',
													 'type'=>'paycheck',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'prev_insurance',
													 'description'=>'Seguro anterior',
													 'type'=>'insurance',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'invoice_form',
													 'description'=>'Formulario Para Facturación',
													 'type'=>'form',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'creditcard_auth_form',
													 'description'=>'Formulario Authorazación Tarjeta Crédito',
													 'type'=>'form',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'payment_proof',
													 'description'=>'Comprobante de Pago',
													 'type'=>'form',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'payment_proof_bd',
													 'description'=>'Comprobante de Pago BD',
													 'type'=>'form',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);
			$to_insert[] = array('name'=>'others',
													 'description'=>'Otros',
													 'type'=>'others',
													 'procedure_catalog_id'=>$procedure_catalog_id
													);

			foreach( $to_insert as $obj ){
				if( !isset($names[$obj['name']]) ){
					\DB::table('procedure_document')->insert([$obj]);
				}
			}//end foreach
		}
	}//end run

}
