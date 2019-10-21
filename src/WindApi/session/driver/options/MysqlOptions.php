<?php


namespace WindApi\session\driver\options;


class MysqlOptions implements Options
{

    private $host = 'localhost';

    private $port = '3306';

    private $user = 'root';

    private $pwd = '';

    private $dbname = '';

    private $charset = '';

    private $session_table = '';

    public function __construct()
    {

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * @param string $pwd
     * @return $this
     */
    public function setPwd($pwd)
    {
        $this->pwd = $pwd;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * @param string $dbname
     * @return $this
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionTable()
    {
        return $this->session_table;
    }

    /**
     * @param string $session_table
     * @return $this
     */
    public function setSessionTable($session_table)
    {
        $this->session_table = $session_table;
        return $this;
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
    public function getPort()
    {
        return $this->port;
    }


    /**
     * 将配置转换为属性数组
     * @return array
     */
    public function toArray()
    {
        return [
            'host' => $this->host,
            'user' => $this->user,
            'pwd' => $this->pwd,
            'port' => $this->port,
            'charset' => $this->charset,
            'dbname' => $this->dbname,
            'session_table' => $this->session_table
        ];
    }
}