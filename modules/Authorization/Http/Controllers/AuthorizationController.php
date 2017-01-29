<?php namespace Modules\Authorization\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class AuthorizationController extends Controller {
	
	public function index()
	{
		return view('authorization::index');
	}
	
}