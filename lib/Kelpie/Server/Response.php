<?php

namespace Kelpie\Server;

use \IteratorAggregate;
use \Kelpie\Server\Headers;

class Response implements IteratorAggregate
{
	const CONNECTION = 'Connection';
	const CLOSE = 'close';
	const KEEP_ALIVE = 'keep-alive';
	const SERVER = 'Server';
	const CONTENT_LENGTH = 'Content-Length';

	private $_status;
	private $_body;
	private $_headers;
	private $_persistent;

	public function __construct()
	{
		$this->_headers = new Headers();
		$this->_status = 200;
		$this->_persistent = false;
	}

	/**
	 * String representation of the headers
	 * to be sent in the response.
	 */
	public function headersOutput()
	{
		// Set default headers
		$this->_headers[self::CONNECTION] = $this->isPersistent()
			? self::KEEP_ALIVE
			: self::CLOSE
			;

		return "$this->_headers";
	}

	/**
	 * Top header of the response,
	 * containing the status code and response headers.
	 */
	public function head()
	{
		return sprintf(
			"HTTP/1.1 %s %s\r\n%s\r\n",
			$this->_status,
			self::$HTTP_STATUS_CODES[$this->_status],
			$this->headersOutput()
		);
	}

	public function setStatus($status)
	{
		$this->_status = $status;
		return $this;
	}

	public function setBody($body)
	{
		$this->_body = $body;
		return $this;
	}

	/**
	 * array($key => $value)
	 * $key must be a string
	 * $value can be either an array, or a string separated by newlines
	 */
	public function setHeaders($headers)
	{
		foreach($headers as $key => $value)
		{
			if (is_string($value))
				$value = explode("\n", $value);

			foreach ($value as $v)
			{
				$this->_headers[$key] = $v;
			}
		}
	}

	public function getIterator()
	{
		$it = new \AppendIterator();

		$it->append(new \ArrayIterator(array($this->head())));

		if (is_string($this->_body))
			$it->append(new \ArrayIterator(array($this->_body)));
		elseif (is_array($this->_body))
			$it->append(new \ArrayIterator($this->_body));
		elseif ($this->_body instanceof Iterator)
			$it->append($this->_body);

		return $it;
	}

	public function persistent()
	{
		$this->_persistent = true;
	}

	public function isPersistent()
	{
		return $this->_persistent && isset($this->_headers[self::CONTENT_LENGTH]);
	}

	private static $HTTP_STATUS_CODES = array(
		100  => 'Continue',
		101  => 'Switching Protocols',
		200  => 'OK',
		201  => 'Created',
		202  => 'Accepted',
		203  => 'Non-Authoritative Information',
		204  => 'No Content',
		205  => 'Reset Content',
		206  => 'Partial Content',
		300  => 'Multiple Choices',
		301  => 'Moved Permanently',
		302  => 'Moved Temporarily',
		303  => 'See Other',
		304  => 'Not Modified',
		305  => 'Use Proxy',
		400  => 'Bad Request',
		401  => 'Unauthorized',
		402  => 'Payment Required',
		403  => 'Forbidden',
		404  => 'Not Found',
		405  => 'Method Not Allowed',
		406  => 'Not Acceptable',
		407  => 'Proxy Authentication Required',
		408  => 'Request Time-out',
		409  => 'Conflict',
		410  => 'Gone',
		411  => 'Length Required',
		412  => 'Precondition Failed',
		413  => 'Request Entity Too Large',
		414  => 'Request-URI Too Large',
		415  => 'Unsupported Media Type',
		500  => 'Internal Server Error',
		501  => 'Not Implemented',
		502  => 'Bad Gateway',
		503  => 'Service Unavailable',
		504  => 'Gateway Time-out',
		505  => 'HTTP Version not supported'
	);
}
