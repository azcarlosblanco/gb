<?php namespace Modules\Role\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class RoleController extends Controller {
	
	public function index()
	{
		return view('role::index');
	}
	
}