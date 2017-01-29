<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcessEntry;

class ProcedureNewPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $prodedureCat=DB::table('procedure_catalog')
                    ->where('name','newpolicy')
                    ->first();

        if($prodedureCat!=null){
            $idCat = $prodedureCat->id;
            $idProd=DB::table('procedure_entry')
                    ->where('procedure_catalog_id',$idCat)
                    ->pluck('id');
            $idCatalogProcess = DB::table("process_catalog")
                                    ->where("procedure_catalog_id",$idCat)
                                    ->pluck('id');
            //delete associated pre requisites
            foreach( $idCatalogProcess as $x ){
                DB::table('process_prerequisite')
                            ->where('prs_cat_id',$x)
                            ->delete();
            }
            DB::table('send_document')
                        ->delete();   
            DB::table('process_entry')
                        ->whereIn('procedure_entry_id',$idProd)
                        ->delete();
            DB::table('process_catalog')
                        ->where('procedure_catalog_id',$idCat)
                        ->delete();
        }
        


        $emission_id=DB::table('role')
        				->where('name','emision')
        				->select('id')
                        ->first()
                        ->id;
        $reception_id=DB::table('role')
        				->where('name','recepcion')
        				->select('id')
                        ->first()
                        ->id;

        $procedure = ProcedureCatalog::where("name","newpolicy")->first();
        if($procedure==null){
            $procedure=ProcedureCatalog::create(
                [
                    'name'        => 'newpolicy',
                    'description' => 'New Policy',
                ]
            );
        }

       //la persona de recpecion recibe la aplicacion para nueva poliza
        $process1=ProcessCatalog::create(
            [
                'name'                => 'InitialDocumentation',
                'description'         => 'Inicio del Trámite',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 1,
                'icon'                 => 'glyphicon glyphicon-tag',
                'link'                 => '.nueva-poliza-ver',
                'compulsory'           => 1,
            ]
        );

        //si se recibe cheque se debe mandar cheque a best doctors
        $process2=ProcessCatalog::create(
            [
                'name'                => 'SendCheckIC',
                'description'         => 'Enviar Cheque Pago',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 2,
                'icon'                 => 'glyphicon glyphicon-envelope',
                'link'                 => '.enviar-cheque',
                'compulsory'           => 0,
            ]
        ); 

        //la persona en emision cargar los datos de solicitud de la nueva poliza en
        //el sistema
        $process3=ProcessCatalog::create(
            [
                'name'               => 'UploadPolicyRequest',
                'description'        => 'Ingresar Solicitud',
                'department'        => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'              => 2,
                'seq_number'         => 2,
                'icon'               => 'glyphicon glyphicon-pencil',
                'link'               => '.revisar-formulario.paso-1',
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

        //id 2 is emision
        //Se enivia los datos y formularios para la emision de la poliza 
        /*$process4=ProcessCatalog::create(
            [
                'name'                 => 'ChangeEffectiveDate',
                'description'          => 'Enviar Solicitud BD',
                'department'           => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 3,
                'seq_number'           => 3,
                'icon'                 => 'glyphicon glyphicon-envelope',
                'link'                 => '.enviar-solicitud',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process4->id,
                'pre_prs_cat_id' => $process3->id
            ]
        );*/

        //El proceso inicia con el envio de la poliza que se solicito a BD
        //y termina cuando se recibe la aplicacion desde BD
        $process5=ProcessCatalog::create(
            [
                'name'               => 'RequestAppNewPolicyBD',
                'description'        => 'Req App BD',
                'department'        => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'              => 4,
                'seq_number'         => 4,
                'icon'               => 'glyphicon glyphicon-envelope',
                'link'               => '.email-poliza-BD',
                'display'            => 0,
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process5->id,
                'pre_prs_cat_id' => $process3->id
            ]
        );

        //El proceso inicia con el envio de la poliza que se solicito a BD
        //y termina cuando se recibe la aplicacion desde BD (fisica)
        $process6=ProcessCatalog::create(
            [
                'name'                 => 'ReceivePolicyBD',
                'description'          => 'Recibir Póliza BD',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 5,
                'seq_number'           => 5,
                'icon'                 => 'glyphicon glyphicon-copy',
                'link'                 => '.recibir-documentos-bd',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process6->id,
                'pre_prs_cat_id'  => $process5->id
            ]
        );

        //El proceso inicia desde que la poliza ya se recibio hasta que el usuario
        //de emision la da por aceptada y envia un correo al agente 
        //de como quedo la poliza
        $process7=ProcessCatalog::create(
            [
                'name'                 => 'ReviewProspectPolicy',
                'description'          => 'Confirmar Datos Póliza BD',
                'department'           => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 5,
                'seq_number'           => 5,
                'icon'                 => 'glyphicon glyphicon-eye-open',
                'link'                 => '.revisar-poliza',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process7->id,
                'pre_prs_cat_id' => $process5->id
            ]
        );

        //El proceso inicia desde que la respuesta del usuario llego
        //hasta que se registra la respuesta del usuario en el sistema
        $process8=ProcessCatalog::create(
            [
                'name'                 => 'RegisterCustomerResponse',
                'description'          => 'Respuesta Cliente',
                'department'           => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 6,
                'seq_number'           => 6,
                'icon'                 => 'glyphicon glyphicon-edit',
                'link'                 => '.registrar-respuesta',
                'compulsory'           => 1,
                'display'              => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process8->id,
                'pre_prs_cat_id' => $process7->id
            ]
        );

        //Confirma la forma de pago del cliente y anade formularios de pago
        //en caso de que no este completos. 
        //Enviar Informacion de pago a best doctors
        //NOTA LA POLIZA PUEDE ESTAR EMITIDA Y HABER SIDOC OMPLETAMENTE PROCESADA
        //Y EL PAGO SE PUEDE RECEPTAR HASTA 60 DIAS DUESPUES
        $process9=ProcessCatalog::create(
            [
                'name'                 => 'ResgisterCustomerPayment',
                'description'          => 'Registrar Pago',
                'department'           => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 7,
                'seq_number'           => 7,
                'icon'                 => 'glyphicon glyphicon-usd',
                'link'                 => '.registrar-pago',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process9->id,
                'pre_prs_cat_id' => $process8->id
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process2->id,
                'pre_prs_cat_id' => $process9->id
            ]
        );

        //Registra que best doctors recibió el pago y envía la factura al cliente  
        $process10=ProcessCatalog::create(
                [
                    'name'                 => 'RegisterInvoice',
                    'description'          => 'Confirmación Pago y envío Factura',
                    'department'           => $emission_id,
                    'procedure_catalog_id' => $procedure->id,
                    'group'                => 8,
                    'seq_number'           => 8,
                    'icon'                 => 'glyphicon glyphicon-send',
                    'link'                 => '.enviar-factura',
                    'compulsory'           => 1,
                ]
            );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process10->id,
                'pre_prs_cat_id' => $process9->id
            ]
        );

        //El proceso inicia desde que la respuesta del usuario llego
        //hasta que se registro la respuesta del usaurio en el sistema
        $process11=ProcessCatalog::create(
            [
                'name'                 => 'SendDocsReception',
                'description'          => 'Enviar Documentos Recepción',
                'department'           => $emission_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 9,
                'seq_number'           => 9,
                'icon'                 => 'glyphicon glyphicon-print',
                'link'                 => '.enviar-docs-recepcion',
                'compulsory'           => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process11->id,
                'pre_prs_cat_id' => $process8->id
            ]
        );

        $process12=ProcessCatalog::create(
            [
                'name'                  => 'SendPolicyCustomer',
                'description'           => 'Enivar Póliza al Cliente',
                'department'            => $reception_id,
                'procedure_catalog_id'  => $procedure->id,
                'group'                 => 10,
                'seq_number'            => 10,
                'icon'                  => 'glyphicon glyphicon-send',
                'link'                  => '.enviar-poliza-cliente',
                'compulsory'            => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process12->id,
                'pre_prs_cat_id' => $process11->id
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process12->id,
                'pre_prs_cat_id'  => $process6->id
            ]
        );

        $process13=ProcessCatalog::create(
            [
                'name'                 => 'UploadSignedPolicy',
                'description'          => 'Cargar Póliza Firmada',
                'department'           => $reception_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 11,
                'seq_number'           => 11,
                'icon'                 => 'glyphicon glyphicon-upload',
                'link'                 => '.cargar-poliza-firmada',
                'compulsory'            => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process13->id,
                'pre_prs_cat_id' => $process12->id
            ]
        );

        $process14=ProcessCatalog::create(
            [
                'name'                  => 'SendDocumentsBD',
                'description'           => 'Enivar Póliza Best Doctors',
                'department'            => $reception_id,
                'procedure_catalog_id'  => $procedure->id,
                'group'                 => 12,
                'seq_number'            => 12,
                'icon'                  => 'glyphicon glyphicon-send',
                'link'                  => '.enviar-poliza-bd',
                'compulsory'            => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process14->id,
                'pre_prs_cat_id' => $process13->id
            ]
        );

        $process15=ProcessCatalog::create(
            [
                'name'                  => 'UploadReceipt',
                'description'           => 'Cargar Acuse Recibido',
                'department'            => $reception_id,
                'procedure_catalog_id'  => $procedure->id,
                'group'                 => 13,
                'seq_number'            => 13,
                'icon'                  => 'glyphicon glyphicon-upload',
                'link'                  => '.cargar-acuse-recibido',
                'compulsory'            => 1,
                'last_process'          => 1,
            ]
        );

        DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process15->id,
                'pre_prs_cat_id' => $process14->id
            ]
        );

        Model::reguard();
    }
}
