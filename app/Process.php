<?php
namespace App;

interface Process{

	public function reasignResponsible();

	public function createTicket();

	public function closeTicket();

	public function finish();

	public function start();

	public function cancel();

}