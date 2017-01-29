<?php namespace Modules\Clientservice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcessEntry;

class ProcedureClientServiceSeederTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		 $prodedureCat=\DB::table('procedure_catalog')
                    ->where('name','emergencycs')
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

        $procedure = ProcedureCatalog::where("name","emergencycs")->first();
        if($procedure==null){
            $procedure=ProcedureCatalog::create(
                [
                    'name'        => 'emergencycs',
                    'description' => 'Client Service Emergency',
                ]
            );
        }

       //se registra la emergencia y se solicita carta de garantia
        $process1=ProcessCatalog::create(
            [
                'name'                => 'CSInputData',
                'description'         => 'Registrar datos de la Emergencia',
                'department'           => $client_service_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 1,
                'icon'                 => 'glyphicon glyphicon-edit',
                'link'                 => '.ingreso-datos',
                'compulsory'           => 1,
            ]
        );

        //se recibe carta de best doctor para ser revisada
        $process2=ProcessCatalog::create(
            [
                'name'                => 'CSWarratyLetter',
                'description'         => 'Registrar recibido de carta de garantia',
                'department'           => $client_service_id,
                'procedure_catalog_id' => $procedure->id,
                'group'                => 1,
                'seq_number'           => 2,
                'icon'                 => 'glyphicon glyphicon-list-alt',
                'link'                 => '.carta-garantia',
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