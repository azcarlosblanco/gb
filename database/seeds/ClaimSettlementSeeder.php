<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcessEntry;

class ClaimSettlementSeeder extends Seeder
{

      /**
       * Run the database seeds.
       *
       * @return void
       */
      public function run()
      {
          Model::unguard();

          DB::table('process_catalog')
                      ->where('name','ClaimsReceiveReceipt')
                      ->update(['last_process'=>1]);

          $ids = DB::table('process_catalog')
                      ->where('name','ClaimsReceiveSettlement')
                      ->where('name','ClaimsSettlement')
                      ->get();

          //delete associated pre requisites
          foreach( $ids as $x ){
            DB::table('process_prerequisite')
                        ->where('prs_cat_id',$x->id)
                        ->delete();
          }

          DB::table('process_catalog')
                      ->where('name','ClaimsReceiveSettlement')
                      ->delete();
          DB::table('process_catalog')
                      ->where('name','ClaimsSettlement')
                      ->delete();

          $claims_id=DB::table('role')
                  ->where('name','claims')
                  ->select('id')
                          ->first()
                          ->id;
          $reception_id=DB::table('role')
                  ->where('name','recepcion')
                  ->select('id')
                          ->first()
                          ->id;

          $procedure = ProcedureCatalog::where('name', 'settlement')->first();
          if( is_null($procedure) ){
            $procedure = ProcedureCatalog::create(['name'=>'settlement', 'description'=>'Liquidaciones']);
          }
          
         //la persona de recpecion recibe la aplicacion para nueva liquidacion
          $process1=ProcessCatalog::create(
              [
                  'name'                => 'SettlementInit',
                  'description'         => 'Inicio del Trámite',
                  'department'           => $reception_id,
                  'procedure_catalog_id' => $procedure->id,
                  'group'                => 1,
                  'seq_number'           => 1,
                  'icon'                 => 'glyphicon glyphicon-upload',
                  'link'                 => '.nueva-liquidacion-ver',
                  'compulsory'           => 1,
                  'display'              => 0
              ]
          );

          $process2=ProcessCatalog::create(
              [
                  'name'                => 'SettlementUploadFiles',
                  'description'         => 'Subir archivos de liquidacion',
                  'department'           => $reception_id,
                  'procedure_catalog_id' => $procedure->id,
                  'group'                => 2,
                  'seq_number'           => 2,
                  'icon'                 => 'glyphicon glyphicon-upload',
                  'link'                 => '.upload-files',
                  'compulsory'           => 1,
              ]
          );
          $process3=ProcessCatalog::create(
              [
                  'name'                => 'SettlementRegister',
                  'description'         => 'Registar valores liquidacion',
                  'department'           => $claims_id,
                  'procedure_catalog_id' => $procedure->id,
                  'group'                => 3,
                  'seq_number'           => 3,
                  'icon'                 => 'glyphicon glyphicon-edit',
                  'link'                 => '.registrar-valores',
                  'compulsory'           => 1,
              ]
          );

          DB::table('process_prerequisite')->insert(
              [
                  'prs_cat_id'      => $process2->id,
                  'pre_prs_cat_id' => $process1->id
              ]
          );

          DB::table('process_prerequisite')->insert(
              [
                  'prs_cat_id'      => $process3->id,
                  'pre_prs_cat_id' => $process1->id
              ]
          );

          $process4=ProcessCatalog::create(
              [
                  'name'                => 'SettlementRefund',
                  'description'         => 'Register Payment',
                  'department'           => $claims_id,
                  'procedure_catalog_id' => $procedure->id,
                  'group'                => 4,
                  'seq_number'           => 4,
                  'icon'                 => 'glyphicon glyphicon-usd',
                  'link'                 => '.registrar-pago',
                  'compulsory'           => 1,
              ]
          );

          DB::table('process_prerequisite')->insert(
              [
                  'prs_cat_id'      => $process4->id,
                  'pre_prs_cat_id' => $process1->id
              ]
          );

          $process5=ProcessCatalog::create(
              [
                  'name'                => 'SettlementFinish',
                  'description'         => 'Close Settlemnt',
                  'department'           => $claims_id,
                  'procedure_catalog_id' => $procedure->id,
                  'group'                => 5,
                  'seq_number'           => 5,
                  'icon'                 => 'glyphicon glyphicon-ok-circle',
                  'link'                 => '.terminar-liquidacion',
                  'compulsory'           => 1,
                  'last_process'           => 1
              ]
          );

          DB::table('process_prerequisite')->insert(
              [
                  'prs_cat_id'      => $process5->id,
                  'pre_prs_cat_id' => $process1->id
              ]
          );

          Model::reguard();
      }
}
