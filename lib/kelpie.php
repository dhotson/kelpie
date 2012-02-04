<?php

spl_autoload_register(function($class) {
	if (strpos($class, 'Kelpie') !== 0)
		return;

	$parts = explode('_', $class);
	$path = __DIR__ . '/' . implode('/', $parts) . '.php';

	if (!file_exists($path))
		return false;

	require_once $path;
});
