<?php

class Kelpie_App_Response
{
	private $_status;
	private $_headers;
	private $_body;

	public function __construct($status = 200, $headers = array(), $body = '')
	{
		$this->_status = $status;
		$this->_headers = $headers;
		$this->_body = $body;

		$this->_headers['Content-Type'] = 'text/html';
	}

	public function getHeader($key)
	{
		return isset($this->_headers[$key])
			? $this->_headers[$key]
			: null;
	}

	public function setStatus($status)
	{
		$this->_status = $status;
	}

	public function setHeader($key, $value)
	{
		$this->_headers[$key] = $value;
	}

	public function setContent($data)
	{
		$this->_body = $data;
		$this->_headers['Content-Length'] = ''.strlen($data);
	}

	public function toArray()
	{
		return array(
			$this->_status,
			$this->_headers,
			array($this->_body)
		);
	}
}
