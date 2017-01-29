<?php namespace Modules\Menu\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Menu\Entities\Menu;

class MenuController extends NovaController {
	
	function __construct(){
		//call to method start of the 
		parent::__construct();
	}

	public function sideBar()
	{
		$menu=Menu::getArrayMenu();
		$this->novaMessage->setData($menu);
        return $this->returnJSONMessage(200);
	}
	
}