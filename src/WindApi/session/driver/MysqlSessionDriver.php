<?php


namespace WindApi\session\driver;

use WindApi\exceptions\SessionRuntimeException;
use WindApi\session\BaseSessionModel;
use WindApi\session\driver\options\MysqlOptions;
use WindApi\session\driver\options\Options;


class MysqlSessionDriver implements SessionExtract
{
    /**
     * @var \PDO
     */
    protected $db = null;

    private static $_instance = null;

    private $options = [];

    /**
     * MysqlSessionDriver constructor.
     * @param MysqlOptions $mysqlOptions
     */
    private function __construct(MysqlOptions $mysqlOptions)
    {

        if(!class_exists("pdo")) {
            throw new SessionRuntimeException("Pdo扩展驱动不存在");
        }

        $this->options = $mysqlOptions->toArray();

        try {
            $this->db = new \PDO($this->_createDsn($this->options),$this->options['user'],$this->options['pwd']);

        } catch(\PDOException $exception) {

            throw new SessionRuntimeException("PdoException:".$exception->getMessage());
        }

    }

    /**
     * 单实例
     * @param Options $options
     * @return MysqlSessionDriver|SessionExtract|null
     */
    public static function getSessionDriverInstance(Options $options)
    {
        if(self::$_instance == null){
            return new self($options);
        }

        return self::$_instance;
    }


    /**
     * 创建dsn
     * @param array $opt
     * @return string
     */
    private function _createDsn($opt = []){
        $type = isset($opt['type']) ? $opt['type'] : "mysql";
        $host = isset($opt['host']) && !empty($opt['host']) ? $opt['host'] : '127.0.0.1';
        $port = isset($opt['port']) && !empty($opt['port']) ? $opt['port'] : '3306';
        $dbname = isset($opt['dbname']) && !empty($opt['dbname']) ? $opt['dbname'] : 'test';
        $charset = isset($opt['charset']) && !empty($opt['charset']) ? $opt['charset'] : 'utf8';
        return "{$type}:host={$host}:{$port};dbname={$dbname};charset={$charset}";
    }

    /**
     * 查询会话ID获取会话数据
     * @param $session_id
     * @param $sessionModel
     * @return array
     */
    private function _getSession($session_id,BaseSessionModel $sessionModel = null){

        if(empty($session_id)) {
            return null;
        }

        $columns = ["*"];
        if(!empty($sessionModel)) {
           $arr = $sessionModel->toArray();
           $columns = array_keys($arr);
        }

        $stmt = $this->db->prepare("SELECT ".implode(",",$columns)." FROM ".$this->options['session_table']." WHERE session_id = :session_id LIMIT 1");

        $result = $stmt->execute([
            ':session_id' => $session_id
        ]);


        if($result === FALSE){
            $errinfo = $stmt->errorInfo();
            throw new SessionRuntimeException("[{$errinfo[0]}] {$errinfo[1]} $errinfo[2]");
        }

        $object =  $stmt->fetchObject();

        if($object === FALSE){
            return [];
        }
        $data = [];
        foreach ($object as $key => $value){
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * 保存会话数据
     * @param BaseSessionModel $sessionModel
     * @param bool $is_update
     * @return bool
     */
    private function _saveSession(BaseSessionModel $sessionModel,$is_update = false){
        $data = $sessionModel->toArray();
        $keys = array_keys($data);
        $bindKey = array_map(function($k){
            return ":".$k;
        },$keys);

        if($is_update === FALSE) {
            $sql = "INSERT INTO {$this->options['session_table']}(".implode(",",$keys).") VALUES(".implode(",",$bindKey).")";

        } else {
            $sql = "UPDATE {$this->options['session_table']} SET ";
            $setData = [];
            foreach ($keys as $index => $key){
                if($key != 'session_id') {
                    array_push($setData, $key . " = " . $bindKey[$index]);
                }
            }
            $sql .= implode(",",$setData)." WHERE `session_id` = :session_id";
        }
        $stmt = $this->db->prepare($sql);
        $params = [];
        foreach ($keys as $index => $key) {
            $str = $data[$key];
            $params[$bindKey[$index]] = $str;
        }

        $result = $stmt->execute($params);

        if($result === FALSE) {
            $errinfo = $stmt->errorInfo();
            throw new SessionRuntimeException("[{$errinfo[0]}] {$errinfo[1]} $errinfo[2]");
        }

        return true;

    }

    /**
     * 删除会话数据
     * @param string | array $session_id
     * @return bool
     */
    private function _removeSession($session_id){
        if(!is_array($session_id)) {
            $sql = "DELETE FROM {$this->options['session_table']} WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":session_id",$session_id,\PDO::PARAM_STR);
            $result = $stmt->execute();
        } else {
            $sql = "DELETE FROM {$this->options['session_table']} WHERE session_id in (".implode(",",$session_id).")";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute();
        }

        if($result === FALSE) {
            $errinfo = $stmt->errorInfo();
            throw new SessionRuntimeException("[{$errinfo[0]}] {$errinfo[1]} $errinfo[2]");
        }
        return true;
    }

    private function _getBeforeTimeSession($time){
        if(empty($time)) {
            return [];
        }
        $sql = "SELECT GROUP_CONCAT(session_id,',') as id_group FROM {$this->options['session_table']} WHERE `session_time` < {$time} LIMIT 1";
        $stmt = $this->db->query($sql);
        $object = $stmt->fetchObject();
        if($object === FALSE) {
            return [];
        }
        return array_filter(explode("," , $object->id_group));
    }


    /**
     * 读取会话
     * @param BaseSessionModel $fitModel
     * @return BaseSessionModel | null
     */
    public function loadSession(BaseSessionModel $fitModel)
    {
        $session = $this->_getSession($fitModel->session_id,$fitModel);
        if(empty($session)) {
            return null;
        }
        return $fitModel->fit($session);
    }

    /**
     * 新增会话
     * @param BaseSessionModel $model
     * @return bool
     */
    public function insertSession(BaseSessionModel $model)
    {
         return $this->_saveSession($model);
    }

    /**
     * 更新会话
     * @param BaseSessionModel $session_model
     * @return bool
     */
    public function updateSession(BaseSessionModel $session_model)
    {
        return $this->_saveSession($session_model,true);
    }

    /**
     * 删除会话
     * @param $removeModel
     * @return bool
     */
    public function removeSession(BaseSessionModel $removeModel)
    {
        return $this->_removeSession($removeModel->session_id);
    }

    /**
     * 删除指定会话片段
     * @param array $group
     * @return bool
     */
    public function removeSessionGroup(array $group)
    {
        return $this->_removeSession($group);
    }

    /**
     * 获取已经失效的会话片段
     * @param integer $timestamp 参照该时间戳之前的会话作为已经失效的片段
     * @return array 返回失效片段的session_id
     */
    public function getLostSession($timestamp)
    {
        return $this->_getBeforeTimeSession($timestamp);
    }

    /**
     * 关闭会话操作
     * @param BaseSessionModel $sessionModel 会话数据模型
     * @return bool
     */
    public function close(BaseSessionModel $sessionModel)
    {
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
}