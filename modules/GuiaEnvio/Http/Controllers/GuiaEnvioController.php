<?php namespace Modules\Guiaenvio\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class GuiaEnvioController extends Controller {
	
	public function index()
	{
		return view('guiaenvio::index');
	}
	
}