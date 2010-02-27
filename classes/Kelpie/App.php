<?php

abstract class Kelpie_App
{
	protected $_env;
	protected $_request;
	protected $_response;

	private $_routes;

	public function __construct()
	{
		$this->_initRoutes();
	}

	public function call($env)
	{
		$this->_env = $env;
		$this->_request = new Kelpie_App_Request($env);
		$this->_response = new Kelpie_App_Response();

		$this->_dispatch();

		return $this->_response->toArray();
	}

	protected function error404()
	{
		$this->_response->setStatus(404);
		$this->_response->setContent('<html><body>404 Not Found</body></html>');
	}

	protected function isPost()
	{
		return 'POST' == $this->_request->getRequestMethod();
	}

	// ---

	private function _initRoutes()
	{
		if (!isset($this->_routes))
		{
			$this->_routes = array();
			$klass = new ReflectionClass(get_class($this));

			foreach ($klass->getMethods() as $action)
			{
				if (($comment = $action->getDocComment())
					&& preg_match_all('/@(get|post) (\/.*)/', $comment, $matches))
				{
					foreach ($matches[1] as $i => $method)
					{
						$route = $matches[2][$i];

						$re = preg_replace('/\//', '\/', $route); // escape slashes
						$re = preg_replace('/(:[a-z]+)/', '([^\/]+)', $re); // capture tokens
						$re = preg_replace('/(#[a-z]+)/', '(\d+)', $re); // capture numeric tokens
						$re = "/^$re$/";

						if (!isset($this->_routes[$method]))
							$this->_routes[$method] = array();

						$this->_routes[$method][$re] = $action;
					}
				}
			}
		}
	}

	private function _dispatch()
	{
		$method = strtolower($this->_request->getRequestMethod());
		$path = $this->_request->getPath();

		if (isset($this->_routes[$method]))
		{
			// find first matching route
			foreach ($this->_routes[$method] as $route => $method)
			{
				if (preg_match($route, $path, $matches))
				{
					array_shift($matches);
					$result = $method->invokeArgs($this, $matches);

					if (is_string($result))
					{
						$this->_response->setContent($result);
					}

					return;
				}
			}
		}

		$this->error404();
	}
}
