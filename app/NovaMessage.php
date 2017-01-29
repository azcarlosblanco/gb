<?php

namespace App;

/**
* Class to present message after an action
* @author [Rocio Mera] <[rmera@novatechnology.com.ec]>
*/
class NovaMessage 
{
	//un arreglo de mensajes
	private $message=[];
	//content of the message we want to return
	//the data must be in format JSON.
	private $data=[];
	//the route where the request must be redirected after its completition
	//If route = # no forwad the page
	private $route='#';

	function __construct()
	{
		//echo(1);
	}

	public function addErrorMessage($reason,$message){
		$this->setMessage('Error',$reason,$message);
	}

	public function addSuccesMessage($reason,$message){
		$this->setMessage('Success',$reason,$message);
	}

	public function addInfoMessage($reason,$message){
		$this->setMessage('Info',$reason,$message);
	}

	public function setMessage($type,$reason,$message){
		$this->message[$type]=[$reason=>$message];
	}

	/*
	 * Set the attribute date to the value pass as parameter
	 * data must be a json object
	 */
	public function setData($data){
		$this->data=$data;
	}

	public function addData($name, $object){
		/*
		 * Add an element to the array that contain the data of the message
		 */
		$data=$object;
		if(!is_string($object) || $this->is_JSON($object)){
			if(method_exists($object,'toJSON')){
				$data=$object->toJSON();
			}else{
				$data=json_encode($object);
			}
		}
		$this->data[$name]=$data;
	}

	public function setRoute($route){
		$this->route=$route;
	}

	public function  getRoute(){
		return $route;
	}

	public static function is_JSON(...$args) {
	    json_decode(...$args);
	    return (json_last_error()===JSON_ERROR_NONE);
	}

	public function getData(){
		return $this->data;
	}

	public function toJSON(){
		return json_encode([
							'message' => $this->message,
						 	'data'    => $this->data,
						 	'route'   => $this->route
						 	]);
	}

}