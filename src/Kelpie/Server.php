<?php

namespace Kelpie;

use \Kelpie\Server\Exception;
use \Kelpie\Server\Response;

/**
 * A simple single-threaded tcp server for running Kelpie apps
 */
class Server
{
    private $_host;
    private $_port;

    public function __construct($host = '0.0.0.0', $port = 8000, $maxChildren=8, $maxRequests=1000)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_maxChildren = $maxChildren;
        $this->_maxRequests = $maxRequests;

        $this->_preForkCallbacks = array();
        $this->_postForkCallbacks = array();
    }

    public function beforeFork($callback)
    {
      $this->_preForkCallbacks []= $callback;
    }

    public function afterFork($callback)
    {
      $this->_postForkCallbacks []= $callback;
    }

    /**
     * Start serving requests to the application
     * @param callable
     */
    public function start($app)
    {
        if (!($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if (!socket_bind($socket, $this->_host, $this->_port)) {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        if (!socket_listen($socket)) {
            throw new Exception(socket_strerror(socket_last_error()));
        }

        $children = array();
        for (;;) {
            if (count($children) > $this->_maxChildren) {
              if (-1 === ($pid = pcntl_wait($status))) {
                throw new \Exception('pcntl_wait failed');
              }
              unset($children[$pid]);
              $code = pcntl_wexitstatus($status);
              // TODO do something with return code
            }

            foreach ($this->_preForkCallbacks as $callback) {
              call_user_func($callback);
            }

            if (-1 === ($pid = pcntl_fork())) {
              throw new \Exception('Could not fork');
            } elseif ($pid) {
                $children[$pid] = true;
            } else {
                $this->_child($app, $socket);
                exit(0);
            }
        }
    }

    private function _child($app, $socket)
    {
        foreach ($this->_postForkCallbacks as $callback) {
          call_user_func($callback);
        }

        $i = $this->_maxRequests;
        while ($i-- && ($client = socket_accept($socket))) {
            $data = '';
            $nparsed = 0;

            $h = new \HttpParser();

            while ($d = socket_read($client, 1024 * 1024 * 1024)) {
                $data .= $d;
                $nparsed = $h->execute($data, $nparsed);

                if ($h->isFinished()) {
                    break;
                }

                if ($h->hasError()) {
                    socket_close($client);
                    continue 2; // Skip to accept next connection..
                }
            }

            $env = $h->getEnvironment();

            global $argv;
            $env['SERVER_NAME'] = $this->_host;
            $env['SERVER_PORT'] = $this->_port;
            $env['SCRIPT_FILENAME'] = $argv[0];
            socket_getpeername($client, $address);
            $env['REMOTE_ADDR'] = $address;

            list($status, $headers, $body) = call_user_func($app, $env);

            $response = new Response();
            $response->setStatus($status);
            $response->setHeaders($headers);
            $response->setBody($body);

            foreach ($response as $chunk) {
                $len = strlen($chunk);
                $offset = 0;
                while ($offset < $len) {
                    if (false === ($n = socket_write($client, substr($chunk, $offset), $len-$offset)))
                        break 2;

                    $offset += $n;
                }
            }

            socket_close($client);
        }
    }
}
