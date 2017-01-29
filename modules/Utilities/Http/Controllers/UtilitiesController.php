<?php namespace Modules\Utilities\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class UtilitiesController extends Controller {
	
	public function index()
	{
		return view('utilities::index');
	}
	
}