<?php namespace Modules\Renewal\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class RenewalController extends Controller {
	
	public function index()
	{
		return view('renewal::index');
	}
	
}