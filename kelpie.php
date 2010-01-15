<?php

require_once 'lib/kelpie_init.php';

class HelloWorldApp
{
	public function call($env)
	{
		return array(
			200,
			array("Content-Type" => "text/plain"),
			array("hello world\n", "this is a test\n")
		);
	}
}

$server = new Kelpie_Server('0.0.0.0', 8000);
$server->start(new HelloWorldApp());
