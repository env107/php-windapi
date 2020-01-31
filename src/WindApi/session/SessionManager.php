<?php

namespace WindApi\session;
use WindApi\ApiParser;
use WindApi\exceptions\SessionRuntimeException;
use WindApi\exceptions\SessionTimeoutException;
use WindApi\session\driver\options\Options;


class SessionManager implements \SessionHandlerInterface
{
    /**
     * 保存SessionManager实例对象
     * @var SessionManager
     */
    private static $_instance = null;
    /**
     * 保存会话数据驱动对象
     * @var null|driver\SessionExtract
     */
    protected $driver = null;
    /**
     * 保存会话数据模型对象
     * @var null|BaseSessionModel
     */
    protected $model = null;
    /**
     * 会话过期时间，单位秒
     * @var int
     */
    protected $expires_time = 1800;


    /**
     * SessionManager constructor.
     * 会话管理对象构造器
     * 构建会话驱动对象以及会话驱动模型
     * @param string $type 驱动类型，支持驱动扩展
     * @param Options $options 会话驱动配置
     * @param string $model 会话数据模型
     */
    private function __construct($type,Options $options = null,$model){
        $this->driver = SessionFactory::buildSessionDriver($type,$options);
        $this->model = SessionFactory::buildSessionModel($model);
    }

    /**
     * 设定会话过期时间
     * 若当前系统时间与会话创建时间的差大于此值则说明会话已经过期
     * 随后并系统将会调用删除接口删除该会话
     * @param int $expires_time
     * 指定会话过期时间长度，单位为秒。系统定义最低有效时间为1800秒
     * @return bool
     */
    public function setSessionExpiresTime($expires_time = 1800){
        if($expires_time < 1800) {
            throw new SessionRuntimeException("会话过期时间最低设置为1800秒");
        }
        $this->expires_time = $expires_time;
        return true;
    }

    /**
     * 获取会话管理对象实例
     * @param string $type 会话驱动类型
     * @param Options $options 会话驱动配置
     * @param string $model 会话数据模型类名
     * @return SessionManager
     */
    public static function getInstance($type,Options $options = null,$model){
        if(self::$_instance == null) {
            self::$_instance = new self($type,$options,$model);
        }
        return self::$_instance;
    }

    /**
     * 获取会话驱动对象
     * @return driver\SessionExtract|null
     */
    public function getDriver(){
        return $this->driver;
    }



    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        $model = SessionEvents::trigger("close",$this->model);
        return $this->driver->close($model);
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($session_id)
    {
        $model = SessionEvents::trigger("remove",$this->model);
        return $this->driver->removeSession($model);
    }

    /**
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxlifetime)
    {
        $min_time =  ApiParser::$server_time - $maxlifetime;
        $group = $this->driver->getLostSession($min_time);
        if(empty($group)) {
            return true;
        }
        return $this->driver->removeSessionGroup($group);
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $name)
    {
        $model = SessionEvents::trigger("open",$this->model);
        return $this->driver->open($model,$save_path,$name);
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($session_id)
    {
        $this->model->session_id = $session_id;
        $model = $this->driver->loadSession($this->model);

        if(empty($model) ) {
           return '';
        }
        $this->model = $model;
        $model = SessionEvents::trigger("read",$this->model);

        //过期后自动删除并抛出过期异常
        $time = time();
        if( ($time - $model->expires_time) > $model->session_time){
            $this->driver->removeSession($model);
            throw new SessionTimeoutException($session_id);
        }
       return $this->model->session_data;
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($session_id, $session_data)
    {
        $this->model->session_id = $session_id;
        $model = $this->driver->loadSession($this->model);

        $is_update = true;
        if(empty($model)) {
            $this->model->expires_time = $this->expires_time;
            $is_update = false;
        }

        $this->model->session_id = $session_id;
        $this->model->session_time = time();
        $this->model->session_data = $session_data;
        $this->model = SessionEvents::trigger("write",$this->model);

        if($is_update === FALSE){

            $result = $this->driver->insertSession($this->model);
        } else {

            $result = $this->driver->updateSession($this->model);
        }

        return $result;

    }

    /**
     * 判断会话是否存在
     * 通过调用sessionSaveHandler接口的read方法判断
     * @param $session_id
     * @return bool 存在则返回true
     */
    public static function exist($session_id){
        //临时创建一个BaseSessionModel
        $_model = SessionFactory::buildSessionModel(get_class(self::$_instance->model));
        $_model->session_id = $session_id;
        $_model = self::$_instance->driver->loadSession($_model);
        if(empty($_model)) {
            return false;
        }
        return  true;
    }


    /**
     * 判断会话状态
     * @return bool
     */
    public static function isActive()
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }
}