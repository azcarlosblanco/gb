<?php namespace Modules\Claim\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class DeleteClaimsSeederTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		try{
			//DB::statement('BEGIN');
			DB::statement('SET FOREIGN_KEY_CHECKS=0');

			$process_catalog_ids = array();
			$process_catalog_array = DB::select("select * from process_catalog where name like 'Claims%'");
			if( count($process_catalog_array) < 1 ){
				throw new \Exception('no process catalog');
			}

			foreach($process_catalog_array as $item){
				$process_catalog_ids[] = $item->id;
			}

			$process_catalog_ids = implode($process_catalog_ids, ',');

			$procedure_catalog_ids = array();
			$procedure_catalog_array = DB::select("select * from procedure_catalog where name like 'claims%'");
			if( count($procedure_catalog_array) < 1 ){
				throw new \Exception('no procedure catalog');
			}

			foreach($procedure_catalog_array as $item){
				$procedure_catalog_ids[] = $item->id;
			}

			$procedure_catalog_ids = implode($procedure_catalog_ids, ',');


			$procedure_entry_ids = array();
			$procedure_entry_array = DB::select("select * from procedure_entry where procedure_catalog_id in (".$procedure_catalog_ids.")");
			if( count($procedure_entry_array) < 1 ){
				return;
				//throw new \Exception('no procedure entry');
			}

			foreach($procedure_entry_array as $item){
				$procedure_entry_ids[] = $item->id;
			}

			$procedure_entry_ids = implode($procedure_entry_ids, ',');


			//deletes
			DB::delete('delete from process_entry where process_catalog_id in ('.$process_catalog_ids.')');
			DB::delete('delete from procedure_entry where procedure_catalog_id in ('.$procedure_catalog_ids.')');
			DB::delete('delete from file_entry where table_type="procedure_entry" and table_id in ('.$procedure_entry_ids.')');
			DB::delete('delete from file_upload_entry');
			DB::delete('delete from claim_file');
			//DB::delete('delete from claim');

			DB::statement('SET FOREIGN_KEY_CHECKS=1');
			//DB::statement('COMMIT');
		}catch( \Exception $e ){
			//DB::statement('ROLLBACK');
			throw $e;
		}
	}

}
