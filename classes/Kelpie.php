<?php

abstract class Kelpie
{
	public static function autoload($class)
	{
		if (0 !== strpos($class, 'Kelpie'))
		{
			return false;
		}

		$path = dirname(__FILE__).'/'.str_replace('_', '/', $class).'.php';

		if (!file_exists($path))
		{
			return false;
		}

		require_once $path;
	}

	public static function registerAutoload()
	{
		spl_autoload_register(array('Kelpie', 'autoload'));
	}
}
