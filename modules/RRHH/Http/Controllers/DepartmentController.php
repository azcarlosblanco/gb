<?php namespace Modules\RRHH\Http\Controllers\;

use Pingpong\Modules\Routing\Controller;

class DepartmentController extends Controller {
	
	public function index()
	{
		return view('department::index');
	}
	
}