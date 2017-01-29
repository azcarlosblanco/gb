<?php

namespace App\Http\Controllers\Nova;

use App\NovaMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Http\Middleware\AddJsonAcceptHeader;

class NovaController extends Controller
{
	public $novaMessage;

	function __construct()
	{
		$this->novaMessage=new NovaMessage();
		$this->middleware('addJSONHeader');
	}

	function returnJSONMessage($responseCode=200){
		return response($this->novaMessage->toJSON(),$responseCode)
                    		->header('Content-Type', 'application/json')
                    		->header("Access-Control-Allow-Origin"," *");
	}
}