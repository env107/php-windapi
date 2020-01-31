<?php


namespace WindApi\session\driver;
use WindApi\session\BaseSessionModel;
use WindApi\session\driver\options\FileOptions;
use WindApi\session\driver\options\Options;

class FileSessionDriver implements SessionExtract
{

    private $options = null;

    private static $_instance = null;

    /**
     * FileSessionDriver constructor.
     * 文件会话驱动器只适用于独立服务器应用，不适用于多台服务器部署。建议适用redis驱动器取代
     * @param FileOptions $options
     */
    public function __construct(FileOptions $options)
    {
        $this->options = $options;

        //检测储存目录,不存在则创建
        if(!is_dir($options->getSavePath())) {
            mkdir($options->getSavePath(),0777,true);
        }
    }

    /**
     * 查找并读取会话文件
     * @param $session_id
     * @return mixed|null
     */
    private function _findSessionFile($session_id){
        $file = $this->_getSessionFile($session_id);
        if(!file_exists($file)) {
            return null;
        }
        $data = file_get_contents($file);
        if(!preg_match("/^{(.*?)+}$/",$data)) {
            return null;
        }
        return json_decode($data,true);
    }

    /**
     * 获取会话文件路径
     * @param $session_id
     * @return string
     */
    private function _getSessionFile($session_id){
        $options = $this->options;
        $path = $options->getSavePath().DIRECTORY_SEPARATOR.$options->getSessionFilePrefix().$session_id.$options->getSessionFileExt();
        $pathinfo = pathinfo($path);
        $file = $pathinfo['dirname'].DIRECTORY_SEPARATOR.$pathinfo['basename'];
        return $file;
    }

    /**
     * 写入会话文件
     * @param $session_id
     * @param array $data
     * @return bool|int
     */
    private function _writeSessionFile($session_id,array $data){
        $file = $this->_getSessionFile($session_id);
        if(empty($data)) {
            return false;
        }
        return file_put_contents($file,json_encode($data)) > 0 ? true : false;
    }

    /**
     * 删除会话文件
     * @param $session_id
     * @return bool
     */
    private function _removeSessionFile($session_id) {
        if(is_array($session_id)) {
            //多项删除
            foreach ($session_id as $id){
                $file = $this->_getSessionFile($id);
                unlink($file);
            }
            return true;
        } elseif(!empty($session_id) && is_string($session_id)) {
            //单项删除
            $file = $this->_getSessionFile($session_id);
            if(file_exists($file)) {
                unlink($file);
            }
            return true;
        } else {
            return false;
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
        $session_id = $fitModel->session_id;
        if(empty($session_id)) {
            return null;
        }
        $data = $this->_findSessionFile($session_id);
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
        $session_id = $model->session_id;
        return $this->_writeSessionFile($session_id,$model->toArray());
    }

    /**
     * 更新会话
     * @param BaseSessionModel $model
     * 开发者构造该会话数据模型之后传递到该方法中，随后会话数据驱动会将该模型的数据保存到持久化服务中
     * @return bool
     */
    public function updateSession(BaseSessionModel $model)
    {
        $session_id = $model->session_id;
        $data = $this->_findSessionFile($session_id);
        if(empty($data)){
            return false;
        }
        $data['session_data'] =$model->session_data;
        $data['session_time'] = $model->session_time;
        return $this->_writeSessionFile($session_id,$data);
    }

    /**
     * 删除会话
     * @param BaseSessionModel $removeModel 删除的会话模型
     * @return bool
     */
    public function removeSession(BaseSessionModel $removeModel)
    {
        $session_id = $removeModel->session_id;
        return $this->_removeSessionFile($session_id);
    }

    /**
     * 删除指定会话片段
     * 该接口用于SessionManager中的gc()方法
     * @param array $group 指定会话ID的数组
     * @return bool
     */
    public function removeSessionGroup(array $group)
    {
       return $this->_removeSessionFile($group);
    }

    /**
     * 获取已经失效的会话片段
     * 该接口用于SessionManager中的gc()方法
     * @param integer $timestamp 参照该时间戳之前的会话作为已经失效的片段
     * @return array 返回失效片段的session_id
     */
    public function getLostSession($timestamp)
    {
        $options = $this->options;
        $path = $options->getSavePath();
        $group = [];
        foreach (glob($path.DIRECTORY_SEPARATOR.$options->getSessionFilePrefix()."*") as $file){
            $pathinfo = pathinfo($file);
            $pattern = "/".addslashes($options->getSessionFilePrefix())."(.*?)$/";
            if(filemtime($file) < $timestamp && preg_match($pattern,$pathinfo['filename'],$match)){
                array_push($group,$match[1]);
            }
        }
        return $group;
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

    /**
     * 单对象实例模式
     * @param $options
     * @return SessionExtract
     */
    public static function getSessionDriverInstance(Options $options)
    {
        if(self::$_instance == null){
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }
}