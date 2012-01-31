<?php

/**
 * A simple preforking server for running Kelpie apps
 */
class Kelpie_Server
{
	private $_host;
	private $_port;

	public function __construct($host = '0.0.0.0', $port = 8000)
	{
		$this->_host = $host;
		$this->_port = $port;
	}

	/**
	 * Start serving requests to the application
	 * @param Kelpie_Application
	 */
	public function start($app)
	{
		if (!($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
		{
			throw new Kelpie_Exception(socket_strerror(socket_last_error()));
		}

		if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1))
		{
			throw new Kelpie_Exception(socket_strerror(socket_last_error()));
		}

		if (!socket_bind($socket, $this->_host, $this->_port))
		{
			throw new Kelpie_Exception(socket_strerror(socket_last_error()));
		}

		if (!socket_listen($socket))
		{
			throw new Kelpie_Exception(socket_strerror(socket_last_error()));
		}

		while ($client = socket_accept($socket))
		{
			$data = '';
			$nparsed = 0;

			$h = new HttpParser();

			while ($d = socket_read($client, 1024 * 1024))
			{
				$data .= $d;
				$nparsed = $h->execute($data, $nparsed);

				if ($h->isFinished())
				{
					break;
				}

				if ($h->hasError())
				{
					socket_close($client);
					continue 2; //
				}
			}

			$env = $h->getEnvironment();

			$result = $app->call($env);

			$response = new Kelpie_Server_Response();
			$response->setStatus($result[0]);
			$response->setHeaders($result[1]);
			$response->setBody($result[2]);

			foreach ($response as $chunk)
			{
				$len = strlen($chunk);
				$offset = 0;
				while ($offset < $len)
				{
					if (false === ($n = @socket_write($client, substr($chunk, $offset), $len-$offset)))
						break 2;

					$offset += $n;
				}
			}

			socket_close($client);
		}
	}
}
