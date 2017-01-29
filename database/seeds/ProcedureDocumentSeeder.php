<?php

use Illuminate\Database\Seeder;

class ProcedureDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      //claim docs
      $id = DB::table('procedure_catalog')
              ->where('name','claims')
              ->value('id');

      if( empty($id) ){
        return;
      }

      DB::table('procedure_document')->insert([
        array('name' => 'claim_invoice', 'description' => 'Factura', 'type' => 'invoice', 'procedure_catalog_id'=>$id),
        array('name' => 'claim_laborder', 'description' => 'Orden', 'type' => 'laborder', 'procedure_catalog_id'=>$id),
        array('name' => 'claim_labresult', 'description' => 'Resultado', 'type' => 'labresult', 'procedure_catalog_id'=>$id)
      ]);
    }
}
