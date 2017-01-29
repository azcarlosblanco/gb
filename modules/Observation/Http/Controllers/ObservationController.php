<?php namespace Modules\Observation\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class ObservationController extends Controller {
	
	public function index()
	{
		return view('observation::index');
	}
	
}