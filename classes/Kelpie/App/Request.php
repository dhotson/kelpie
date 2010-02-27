<?php

class Kelpie_App_Request
{
	private $_env;

	public function __construct($env)
	{
		$this->_env = $env;
	}

	public function getRequestMethod()
	{
		return $this->_env['REQUEST_METHOD'];
	}

	public function getHost()
	{
		return $this->_env['HTTP_HOST'];
	}

	public function getPath()
	{
		return $this->_env['PATH_INFO'];
	}

	public function getQueryString()
	{
		return isset($this->_env['QUERY_STRING'])
			? $this->_env['QUERY_STRING']
			: '';
	}

	public function getBody()
	{
		return isset($this->_env['REQUEST_BODY'])
			? $this->_env['REQUEST_BODY']
			: '';
	}
}
