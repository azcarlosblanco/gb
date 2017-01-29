<?php
namespace App;
interface Procedure{
	
	/*
	 * Method: start
	 * This function must be called when a procedure start.
	 * This function create a register in the table procedure_entry 
	 * and call the fisrt process in the procedure  
	 */
	public function start();

	public function finish();

	public function cancell();

	/*public function getCurrentProcess();

	public function getNextProcess();

	public function assignResponsible();

	public static function getlistActionButtons($idTramite);*/

}
?>