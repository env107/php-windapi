<?php

namespace WindApi\session\driver;


use WindApi\exceptions\SessionRuntimeException;
use WindApi\session\BaseSessionModel;
use WindApi\session\driver\options\Options;
use WindApi\session\driver\options\RedisOptions;

class RedisSessionDriver implements SessionExtract
{

    private static $_instance = null;

    private $redis = null;

    private $options = null;

    public function __construct(RedisOptions $options)
    {
        if(!class_exists("Redis")) {
            throw new SessionRuntimeException("Redis扩展驱动不存在");
        }
        $this->options = $options;
        try {
            $redis = new \Redis();
            $redis->connect($options->getHost(),$options->getPort(),$options->getTimeout());
            $this->redis = $redis;
        }catch (\Exception $exception){
            throw new SessionRuntimeException($exception->getMessage());
        }
    }

    /**
     * 读取会话
     * 指定会话驱动读取会话信息，并返回会话数据模型
     * @param BaseSessionModel $fitModel
     * 会话数据模型，会话驱动读取到会话数据之后，开发者应该调用该数据模型的fit()方法，将数据注入模型中，并返回
     * @return BaseSessionModel | null
     */
    public function loadSession(BaseSessionModel $fitModel)
    {
       $options = $this->options;
       $session_id = $fitModel->session_id;
       $data = $this->redis->hGet($options->getSessionHash(),$session_id);
        $data = json_decode($data,true);
       if(empty($data)) {
           return null;
       }
       return $fitModel->fit($data);
    }

    /**
     * 新增会话
     * @param BaseSessionModel $model
     * 开发者构造该会话数据模型之后传递到该方法中，随后会话数据驱动会将该模型的数据保存到持久化服务中
     * @return bool
     */
    public function insertSession(BaseSessionModel $model)
    {
        $options = $this->options;
        $session_id = $model->session_id;
        $saveData = $model->toArray();
        return $this->redis->hSet($options->getSessionHash(),$session_id,json_encode($saveData)) !== FALSE ? true : false;
    }

    /**
     * 更新会话
     * @param BaseSessionModel $model
     * 开发者构造该会话数据模型之后传递到该方法中，随后会话数据驱动会将该模型的数据保存到持久化服务中
     * @return bool
     */
    public function updateSession(BaseSessionModel $model)
    {
        $options = $this->options;
        $session_id = $model->session_id;
        $saveData = $model->toArray();
        return $this->redis->hSet($options->getSessionHash(),$session_id,json_encode($saveData)) !== FALSE ? true : false;
    }

    /**
     * 删除会话
     * @param BaseSessionModel $removeModel 删除的会话模型
     * @return bool
     */
    public function removeSession(BaseSessionModel $removeModel)
    {
        return $this->redis->hDel($this->options->getSessionHash(),$removeModel->session_id) !== FALSE ? true:false;
    }

    /**
     * 删除指定会话片段
     * 该接口用于SessionManager中的gc()方法
     * @param array $group 指定会话ID的数组
     * @return bool
     */
    public function removeSessionGroup(array $group)
    {
        return true;
    }

    /**
     * 获取已经失效的会话片段
     * 该接口用于SessionManager中的gc()方法
     * @param integer $timestamp 参照该时间戳之前的会话作为已经失效的片段
     * @return array 返回失效片段的session_id
     */
    public function getLostSession($timestamp)
    {
        return [];
    }

    /**
     * 关闭会话操作
     * @param BaseSessionModel $sessionModel 会话数据模型
     * @return bool
     */
    public function close(BaseSessionModel $sessionModel)
    {
        $this->redis->close();
        return true;
    }

    /**
     * 开启会话操作
     * @param BaseSessionModel $sessionModel
     * @param string $save_path 保存会话的路径
     * @param string $session_name 保存会话的名称
     * @return bool
     */
    public function open(BaseSessionModel $sessionModel, $save_path, $session_name)
    {
        return true;
    }

    /**
     * 单对象实例模式
     * @param Options $options
     * @return SessionExtract
     */
    public static function getSessionDriverInstance(Options $options)
    {
       if(self::$_instance == null){
           self::$_instance =  new self($options);
       }
       return self::$_instance;
    }
}