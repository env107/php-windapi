<?php


namespace WindApi\session\driver\options;


class RedisOptions implements Options
{

    private $session_hash = "session";

    private $host = 'localhost';

    private $port = '6379';

    private $auth = '';

    private $timeout = '';

    /**
     * @return string
     */
    public function getSessionHash()
    {
        return $this->session_hash;
    }

    /**
     * @param string $session_hash
     * @return $this
     */
    public function setSessionHash($session_hash)
    {
        $this->session_hash = $session_hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param string $auth
     * @return $this
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeout()
    {
        return floatval($this->timeout);
    }

    /**
     * @param string $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }





    public function toArray()
    {
        return [];
    }
}