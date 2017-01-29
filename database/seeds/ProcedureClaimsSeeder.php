<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcessEntry;

class ProcedureClaimsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
        /*$idCat=DB::table('procedure_catalog')
                    ->where('name','claims')
                    ->first()
                    ->id;
        $idProd=DB::table('procedure_entry')
                    ->where('procedure_catalog_id',$idCat)
                    ->pluck('id');

        DB::table('procedure_document')
                    ->delete();
        DB::table('process_entry')
                    ->whereIn('procedure_entry_id',$idProd)
                    ->delete();
        DB::table('process_catalog')
                    ->where('procedure_catalog_id',$idCat)
                    ->delete();
        DB::table('procedure_entry')
                    ->where('procedure_catalog_id',$idCat)
                    ->delete();
        DB::table('procedure_catalog')
                    ->where('id',$idCat)
                    ->delete();*/

        DB::table('role')->insert(
            array(
                'name' => 'claims',
                'display_name' => 'User Manage Claims',
                'description' => 'User is allowed to manage claims',
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ));

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

        $procedure=ProcedureCatalog::create(
    		[
        		'name'        => 'claims',
        		'description' => 'Reclamos',
        	]
        );
        
       //la persona de recpecion recibe la aplicacion para nuevo reclamo
        $process1=ProcessCatalog::create(
            [
                'name'                => 'ClaimsInit',
                'description'         => 'Inicio del Trámite',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 1,
                'icon'                 => 'glyphicon glyphicon-upload',
                'link'                 => '.nuevo-reclamo-ver',
                'compulsory'           => 1,
            ]
        );
        
        $process2=ProcessCatalog::create(
            [
                'name'                => 'ClaimsReviewDocuments',
                'description'         => 'Revisón de Documentos',
                'department'           => $claims_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 2,
                'seq_number'           => 2,
                'icon'                 => 'glyphicon glyphicon-edit',
                'link'                 => '.revision-clasificacion.paso-1',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process2->id,
                'pre_prs_cat_id' => $process1->id
            ]
        );
        
        $process3=ProcessCatalog::create(
            [
                'name'                => 'ClaimsPrintLetter',
                'description'         => 'Impresión Carta Reclamos',
                'department'           => $claims_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 3,
                'seq_number'           => 3,
                'icon'                 => 'glyphicon glyphicon-print',
                'link'                 => '.impresion-carta',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process3->id,
                'pre_prs_cat_id' => $process2->id
            ]
        );

        $process4=ProcessCatalog::create(
            [
                'name'                => 'ClaimsSendDocsBD',
                'description'         => 'Envío Reclamo a BD',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 4,
                'seq_number'           => 4,
                'icon'                 => 'glyphicon glyphicon-envelope',
                'link'                 => '.envio-reclamo-bd',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process4->id,
                'pre_prs_cat_id' => $process3->id
            ]
        );

        $process5=ProcessCatalog::create(
            [
                'name'                => 'ClaimsReceiveReceipt',
                'description'         => 'Registrar Acuse Recibido',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 5,
                'seq_number'           => 5,
                'icon'                 => 'glyphicon glyphicon-upload',
                'link'                 => '.registrar-acuse-recibido',
                'compulsory'           => 1,
                'last_process'         => 1
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process5->id,
                'pre_prs_cat_id' => $process4->id
            ]
        );

        Model::reguard();
    }
}