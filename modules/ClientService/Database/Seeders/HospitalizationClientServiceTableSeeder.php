<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcessEntry;

class HospitalizationClientServiceTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */

	public function run()
	{
		Model::unguard();
		
		 $prodedureCat=\DB::table('procedure_catalog')
                    ->where('name','hospitalizations')
                    ->first();

        if($prodedureCat!=null){
            $idCat = $prodedureCat->id;
            $idProd=\DB::table('procedure_entry')
                    ->where('procedure_catalog_id',$idCat)
                    ->pluck('id');
            $idCatalogProcess = \DB::table("process_catalog")
                                    ->where("procedure_catalog_id",$idCat)
                                    ->pluck('id');
            //delete associated pre requisites
            foreach( $idCatalogProcess as $x ){
                \DB::table('process_prerequisite')
                            ->where('prs_cat_id',$x)
                            ->delete();
            }
            
            \DB::table('process_entry')
                        ->whereIn('procedure_entry_id',$idProd)
                        ->delete();
            \DB::table('process_catalog')
                        ->where('procedure_catalog_id',$idCat)
                        ->delete();
        }
        


        $client_service_id=\DB::table('role')
        				->where('name','client_service')
        				->select('id')
                        ->first()
                        ->id;
        $cs_manager_id=\DB::table('role')
        				->where('name','client_service_manager')
        				->select('id')
                        ->first()
                        ->id;

        $procedure = ProcedureCatalog::where("name","hospitalizations")->first();
        if($procedure==null){
            $procedure=ProcedureCatalog::create(
                [
                    'name'        => 'hospitalizations',
                    'description' => 'Client Service Hospitalizations',
                ]
            );
        }

       //se registra la hospitalizacion 
        $process1=ProcessCatalog::create(
            [
                'name'                => 'InputDataHospitalizacion',
                'description'         => 'Registrar datos de la Hospitalización',
                'department'           => $client_service_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 1,
                'icon'                 => 'glyphicon glyphicon-edit',
                'link'                 => '.ingreso-data-hospitalizacion',
                'compulsory'           => 1,
            ]
        );

        //se verifica archivos subidos
        $process2=ProcessCatalog::create(
            [
                'name'                => 'WarrantyLetterHospitalization',
                'description'         => 'Carta de Garantia Hospitalización',
                'department'           => $client_service_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 2,
                'icon'                 => 'glyphicon glyphicon-envelope',
                'link'                 => '.carta-garantia-hospitalizacion',
                'compulsory'           => 1,
                'last_process'         => 1,
            ]
        ); 

        \DB::table('process_prerequisite')->insert(
            [
                'prs_cat_id'      => $process2->id,
                'pre_prs_cat_id' => $process1->id
            ]
        );

        Model::reguard();
	}

}