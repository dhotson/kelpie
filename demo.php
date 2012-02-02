<?php

require_once 'kelpie_init.php';

class HelloWorld
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
$server->start(new HelloWorld());
