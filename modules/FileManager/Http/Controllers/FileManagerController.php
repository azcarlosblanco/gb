<?php namespace Modules\FileManager\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;

class FileManagerController extends Controller {

	public function index(){
		//return view('filemanager::index');
	}

	public function view(Request $request, $id){
		$request['view'] = 1;
		return $this->download($request, $id);
	}

	public function download(Request $request, $id){
		$response = '';
		$view = $request->input('view', false);
		$disp = ($view) ? 'inline' : 'attachment';

		try{
			$file = \App\FileEntry::findOrFail($id);
			//TODO validate file entry against insurance_company | company

			$fs = \Storage::disk($file->driver);

			if( is_null($fs) ){
				throw new \Exception('invalid file system');
			}

			$exists = $fs->exists($file->filename);

			if( !$exists ){
				throw new \Exception('file_not_found');
			}

			$quoted = sprintf('"%s"', addcslashes(basename($file->filename), '"\\'));
			$size = $fs->size($file->filename);

			$content = $fs->get($file->filename);
			$response = response($content, 200)
						//->header('Content-Description','File Transfer')
						->header('Content-Type', $file->mime/*'application/octet-stream'*/)
						->header('Content-Disposition', $disp.'; filename='.$quoted)
						//->header('Content-Transfer-Encoding', 'binary')
						->header('Connection', 'Keep-Alive')
						->header('Expires', '0')
						->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
						->header('Pragma', 'public')
						->header('Content-Length', $size);

		}catch( \Exception $e ){
			$response = response('FILE NOT FOUND'.$e->getMessage(), 404)
	                    		->header('Content-Type', 'text/plain');
		}

		if (ob_get_contents()) 
			ob_end_clean();
		
		return $response->header("Access-Control-Allow-Origin"," *");
	}

	public static function processFileUploadRequests(){
		$requests = \App\CronTask::where('type', 1)
														->where('status', 0)
														->get();
														//->where('date_expire', '<', date('Y-m-d h:i:s'))


		foreach($requests as $req){
			$data = (array)json_decode($req->data);
			$expected = count($data);

			//TODO check for expiration, if not set, use default

			if($expected > 0){
				//search files associated
				$files = \App\FileEntry::where('crontask', $req->id)->get();
				if( ( count($files) == $expected ) && !empty( $req->action ) ){
					//finish task
					$req->finishWithSuccess();
					if( !empty($req->action) ){
						call_user_func($req->action, $req->table_id);
					}
				}
			}
			else{
				//no data to compare, finish task with error
				$req->finishWithError();
			}
		}//end foreach

	}//end processFileUploadRequests

}
