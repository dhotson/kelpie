<?php

require_once 'lib/kelpie.php';

$server = new \Kelpie\Server('0.0.0.0', 3512);
$server->start(function($env) {
	return array(
		200,
		array("Content-Type" => "text/plain"),
		"Hello World!"
	);
});
