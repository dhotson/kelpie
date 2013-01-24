<?php

namespace Kelpie\Server;

use \ArrayAccess;

/**
 * @author dennis@99designs.com
 */
class Headers implements ArrayAccess
{
	private static $HEADER_FORMAT = "%s: %s\r\n";
	private static $ALLOWED_DUPLICATES = array('Set-Cookie','Set-Cookie2','Warning','WWW-Authenticate');

	public function __construct()
	{
		$this->_sent = array();
		$this->_out = array();
	}

	public function offsetExists($key)
	{
		return isset($this->_sent[$key]);
	}

	public function offsetGet($key)
	{
		return $this->_sent[$key];
	}

	public function offsetSet($key, $value)
	{
		if (!isset($this->_sent[$key]) || in_array($key, self::$ALLOWED_DUPLICATES))
		{
			$this->_sent[$key] = true;

			if (!isset($value))
				return;
			elseif ($value instanceof DateTime)
				$value = $value->format(DateTime::RFC1123);
			else
				$value = "$value";

			$this->_out []= sprintf(self::$HEADER_FORMAT, $key, $value);
		}
		return $this->_sent[$key] = $value;
	}

	public function offsetUnset($key)
	{
		unset($this->_sent[$key]);
	}

	public function __toString()
	{
		return implode($this->_out);
	}
}
