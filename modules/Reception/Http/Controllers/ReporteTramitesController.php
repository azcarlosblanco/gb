<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class ReporteTramitesController extends Controller {
	
	public function index()
	{
		return view('reception::index');
	}
	
}