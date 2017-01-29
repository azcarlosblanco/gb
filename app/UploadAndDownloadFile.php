<?php namespace App;

use Zizaco\Entrust\EntrustRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\FileEntry;
use App\FileEntryTemp;
use JWTAuth;
use Modules\Authorization\Entities\Role;


trait UploadAndDownloadFile{

	public function uploadFiles(Request $request, $params, $temp=false) {
		//storageLocation
		if(!isset($params['fieldname'])){
			return 0;
		}

		//set some values to avoid undefined key
		$params['multiple'] = ( isset($params['multiple']) ) ? $params['multiple'] : false;
		$params['data'] = array_get($params, 'data', '');

		if( $params['multiple'] ){
			$request['description_files'] = array_get($request, 'description_files', false);			$params['description_files'] = array_get($params, 'description_files', false);
		}
		else{
			$request['description'] = array_get($request, 'description', false);
			$params['description'] = array_get($params, 'description', false);
		}

		$entry = ($temp) ? new FileEntryTemp() : new FileEntry();

		if($params['multiple']){
			$files = $request->file($params['fieldname']);
			$descriptions = array();
			if($request['description_files']){
				$descriptions = $request['description_files'];
			}elseif($params['description_files']){
				$descriptions = $params['description_files'];
			}
		}else{
			$files[0] = $request->file($params['fieldname']);
			$descriptions[0] = "";
			if($request['description']){
				$descriptions[0] = $request['description'];
			}elseif($params['description']){
				$descriptions[0] = $params['description'];
			}
		}

		$numFiles = count($files);
		$entryIDs = array();
		$uploadedFiles = 0;
		
		foreach ($files as $file) {
			if(!is_null($file)){
				$description = isset($descriptions[$uploadedFiles]) ? $descriptions[$uploadedFiles] : "";
				$extension = $file->getClientOriginalExtension();

				//build the pathFile
				//if pathFile does not exist create the subfolder
				$path_file = $this->getCompanyDirectory().'/'.$params['subfolder'];
				if(!Storage::disk('local')->exists($path_file)){
					Storage::makeDirectory($path_file);
				}

				$file_relative_driver=$path_file.'/'.$file->getFilename().'.'.$extension;
				Storage::disk('local')->put($file_relative_driver,  File::get($file));

				$entry->mime = $file->getClientMimeType();
				$entry->original_filename = $file->getClientOriginalName();
				$entry->filename = $file_relative_driver;

				if(!$temp){
					$entry->table_type = $params['table_type'];
					$entry->table_id = $params['table_id'];
					$entry->crontask = array_get($params, 'cronid', 0);
				}

				$entry->description = $description;
				$entry->data = $params['data'];
				//TODO: add support to specific the driver
				$entry->driver='local';
				$entry->complete_path=
					Storage::disk('local')->getDriver()
						->getAdapter()->getPathPrefix().$file_relative_driver;
				$entry->save();

				$entryIDs[] = $entry->id;

			}//end if is_null

			$uploadedFiles++;
		}//end foreach

		return $entryIDs;
	}

	public function uploadTempFiles(Request $request, $param_name, $multiple=false) {
		$params = array();
		$params['fieldname'] = $param_name;
		$params['subfolder'] = 'temp';
		$params['multiple'] = $multiple;



		$uploadedFiles = $this->uploadFiles($request, $params, true);

		return $uploadedFiles;
	}

	public function moveTempFileWithID($tempid, string $path_to, $category='', $category_id='', $new_descrip='', $driverto='local'){
		$company_directory = $this->getCompanyDirectory();
		$path = $company_directory.'/'.$path_to;
		$fs_to = Storage::disk($driverto);

		if(!$fs_to->exists($path)){
			$fs_to->makeDirectory($path, 493, true);
		}

		try{
			$temp_entry = FileEntryTemp::findOrFail($tempid);
			$fs_from = Storage::disk($temp_entry->driver);

			$file_relative_driver = $path.'/'.substr(strrchr($temp_entry->filename, "/"), 1);
			$exists = $fs_from->exists($temp_entry->filename);

			if( !$exists ){
				throw new \Exception('file_not_found');
			}

			$fs_to->put($file_relative_driver, $fs_from->get($temp_entry->filename));

			$entry = new FileEntry();
			$entry->mime = $temp_entry->mime;
			$entry->original_filename = $temp_entry->original_filename;
			$entry->filename = $file_relative_driver;
			$entry->table_type = $category;
			$entry->table_id = $category_id;
			$entry->description = (!empty($new_descrip)) ? $new_descrip : $temp_entry->description;
			$entry->driver = $driverto;
			$entry->complete_path = $fs_to->getDriver()
					->getAdapter()->getPathPrefix().$file_relative_driver;
			$entry->save();

			//delete from entry and file system
			$temp_filename = $temp_entry->filename;
			$temp_entry->delete();
			$fs_from->delete($temp_filename);
			return $entry->id;
		}catch( \Exception $e ){
			return 0;
		}
	}

	public function getFile($id){
		$entry = FileEntry::findOrFail($id);
		$file = Storage::disk('local')->get($entry->filename);
  	}

  	public function deleteFile($id){
		$entry = FileEntry::find($id);
		if(!isset($entry)){
			throw new \Exception("El archivo no existe");
		}

		$driver = $entry->dirver;
		$exists = Storage::disk($driver)->exists($entry->filename);

		//delete file entry before deleting the physical file
		$filename = $entry->filename;
		$entry->delete();

		if($exists){
			Storage::disk('local')->delete($filename);
		}

		return true;
  	}

  	public function deleteFileTemp($id){
		$entry = FileEntryTemp::find($id);
		if(!isset($entry)){
			throw new \Exception("El archivo no existe");
		}

		$driver = $entry->driver;
		$exists = Storage::disk($driver)->exists($entry->filename);

		//delete file entry before deleting the physical file
		$filename = $entry->filename;
		$entry->delete();

		if($exists){
			Storage::disk('local')->delete($filename);
		}

		return true;
  	}

	/**
	 * method: deleteByFileEntryBatch
	 * Description: This function deletes a set of files by id,
	 * if it is used inside a transaction should b called at the end
	 * Date created: 08-06-2016
	 * Cretated by : Alex Mero
	 *               [amero][@][novatechnology.com.ec]
	 */
	public function deleteByFileEntryBatch($ids){
		$filenames = array();

		if( !is_array($ids) || ( count($ids) < 1 ) ){
			return false;
		}

		//we process all or nothing
		try{
			\DB::beginTransaction();

			foreach( $ids as $id ){
				$entry = FileEntry::findOrFail($id);
				$filenames[] = $entry->filename;
				$entry->delete();
			}

			\DB::commit();
		}
		catch( \Exception $e ){
			\DB::rollback();
			return false;
		}

		//delete physical files
		foreach( $filenames as $filename ){
			$exists = Storage::disk('local')->exists($filename);
			if($exists){
				Storage::disk('local')->delete($filename);
			}
		}

		return true;
	}

    //return true if the user as the correct permission to access
    //the files in that directory
	private function validateAcces(){
		//user must belong to the same company to which want to access the file
		//Add validation to distinguish betwwen a customer and a employee
		return true;
	}

	//if the directory does not exist this function create one
	public function getCompanyDirectory(){
		//company to which the user that if doing the service belong
		//$user=Auth::user();
		//$companyId=$user->company_id;
		$companyId=1;
		//$storage_path=Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
		//$companyDirectory=$storage_path.$companyId;
		if(!Storage::disk('local')->exists($companyId)){
			Storage::disk('local')->makeDirectory($companyId."");
		}
		return $companyId;
	}

	public function createDir($dirname){
		$path_file = $this->getCompanyDirectory().'/'.$dirname;

		if(!Storage::disk('local')->exists($path_file)){
			Storage::makeDirectory($path_file);
		}

		return $path_file;
	}

	public function updateFile(Request $request, $id, $params, $temp=false) {
		//set some values to avoid undefined key
		$resp = false;
		$params['data'] = array_get($params, 'data', '');
		$params['description'] = array_get($params, 'description', false);
		$params['subfolder'] = array_get($params, 'subfolder', '');
		$params['table_type'] = array_get($params, 'table_type', '');
		$params['table_id'] = array_get($params, 'table_id', 0);

		try{
			if( $temp ){
				$entry = FileEntryTemp::findOrFail($id);
			}
			else{
				$entry = FileEntry::findOrFail($id);
			}

			$file = $request->file($params['fieldname']);
			if( is_null($file) ){
				throw new \Exception('no file');
			}

			$extension = $file->getClientOriginalExtension();
			$old_filename = $entry->filename;
			$driver = ( !empty($entry->driver) ) ? $entry->driver : 'local';
			//build the pathFile
			//if pathFile does not exist create the subfolder
			$path_file = $this->getCompanyDirectory().'/'.$params['subfolder'];
			if(!Storage::disk('local')->exists($path_file)){
				Storage::makeDirectory($path_file);
			}

			$file_relative_driver = $path_file.'/'.$file->getFilename().'.'.$extension;
			Storage::disk($driver)->put($file_relative_driver,  File::get($file));

			$entry->mime = $file->getClientMimeType();
			$entry->original_filename = $file->getClientOriginalName();
			$entry->filename = $file_relative_driver;

			if(!$temp){
				$entry->table_type = $params['table_type'];
				$entry->table_id = $params['table_id'];
				$entry->crontask = array_get($params, 'cronid', 0);
			}

			$entry->description = $params['description'];
			$entry->data = $params['data'];
			$entry->driver = $driver;
			$entry->complete_path=
				Storage::disk($driver)->getDriver()
					->getAdapter()->getPathPrefix().$file_relative_driver;
			$entry->save();

			$resp = $id;

			//optional - delete previous file if exists
			$exists = Storage::disk($driver)->exists($old_filename);
			if($exists){
				Storage::disk($driver)->delete($old_filename);
			}

		}catch(\Exception $e){
			$resp = false;
		}

		return $resp;
	}

	public function countFilesInDir($dirname, $driver=false){
		$counter = 0;
		$dirname = $this->getCompanyDirectory().'/'.$dirname;

		if( $driver ){
			$counter = count(Storage::disk($driver)->files($dirname));
		}
		else{
			//default file system
			$counter = count(Storage::files($dirname));
		}

		return $counter;
	}

}
