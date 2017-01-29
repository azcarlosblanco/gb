<?php namespace Modules\Customer\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class CustomerController extends Controller {
	
	public function index()
	{
		return view('customer::index');
	}
	
}