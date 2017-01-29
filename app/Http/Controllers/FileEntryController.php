<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\FileEntry;
use Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

class FileEntryController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$entries = FileEntry::all();

		return view('fileentryindex', compact('entries'));
	}

	public function add() {

		$files = Request::file('filefields');
		print_r($files);
		foreach ($files as $file) {
			$extension = $file->getClientOriginalExtension();
			Storage::disk('local')->put($file->getFilename().'.'.$extension,  File::get($file));
			$entry = new Fileentry();
			$entry->mime = $file->getClientMimeType();
			$entry->original_filename = $file->getClientOriginalName();
			$entry->filename = $file->getFilename().'.'.$extension;

			$entry->save();
		}
		return redirect('fileentry');
		
	}

	public function get($filename){
	
		$entry = Fileentry::where('filename', '=', $filename)->firstOrFail();
		$file = Storage::disk('local')->get($entry->filename);
 
		return (new Response($file, 200))
              ->header('Content-Type', $entry->mime);
	}
}