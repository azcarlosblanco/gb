<?php

use Illuminate\Database\Seeder;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use App\ProcedureEntry;
use App\ProcessEntry;

class ChangeLinkClaimProcess extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
    	$pc = ProcessCatalog::where('name','ClaimsReviewDocuments')
    							->first();
    	$pc->link = "root.seguros.revision-clasificacion";
    	$pc->save();

        $pc = ProcessCatalog::where('name','SettlementRegister')
                                ->first();
        $pc->link = "root.seguros.registrar-liquidacion";
        $pc->save();
    	
    }
}
