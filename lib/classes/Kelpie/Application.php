<?php

interface Kelpie_Application
{
	/**
	 * @param $env array of CGI like environment
	 * @return array(int, array, array)
	 * @example
	 * 	array(
	 * 		200,
	 * 		array('Content-Type' => 'text/plain'),
	 * 		array('hello world')
	 * 	)
	 */
	public function call($env);
}
