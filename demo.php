<?php

require_once 'lib/kelpie.php';

class HelloWorldApp
{
	public function call($env)
	{
		return array(
			200,
			array("Content-Type" => "text/plain"),
			array("Hello World!")
		);
	}
}

$server = new Kelpie_Server('0.0.0.0', 8000);
$server->start(new HelloWorldApp());
