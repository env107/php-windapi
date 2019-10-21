<?php

namespace WindApi\session;


use WindApi\exceptions\SessionRuntimeException;
use WindApi\session\events\SessionEventsImpl;

/**
 * Class SessionEvents
 * 会话事件对象管理类
 * 该类负责处理会话事件的触发过程
 * @package WindApi\session
 */
class SessionEvents
{

    /**
     * 保存本会话事件管理对象实例
     * @var SessionEvents
     */
    private static $_instance = null;

    /**
     * 保存会话事件对象存放路径
     * @var string
     */
    private $_eventPath = null;

    /**
     * 保存会话事件模型列表
     * @var array
     */
    private $_support = [];


    /**
     * SessionEvents constructor.
     * 查找会话事件对象并保存模型列表
     * @param $path
     */
    private function __construct($path){
        $this->_eventPath = $path;
        //读取事件列表
        $resource =opendir($path);
        if($resource === FALSE) {
            throw new SessionRuntimeException("会话事件对象路径无效");
        }
        while (false !== ($file = readdir($resource))) {
            if($file != '.' && $file != '..') {
                preg_match("/^([a-zA-Z]+)SessionEvent.php$/",$file,$events);
                if(isset($events[1])) {
                    $type = strtolower($events[1]);
                    array_push($this->_support,$type);
                }
            }
        }
    }

    /**
     * 获取实例对象
     * @param $path
     * @return SessionEvents
     */
    private static function getInstance($path){
        if(self::$_instance == null) {
            self::$_instance = new self($path);
        }
        return self::$_instance;
    }


    /**
     * 查找会话事件对象
     * @param $event
     * @return SessionEventsImpl
     */
    private function find($event){

        $event = strtolower($event);

        if(!in_array($event,$this->_support)) {
            throw new SessionRuntimeException("不支持的会话事件'{$event}'");
        }

        $class = "\\WindApi\\session\\events\\".ucfirst($event)."SessionEvent";

        $obj = new $class();

        if(!($obj instanceof SessionEventsImpl)) {
            throw new SessionRuntimeException("会话事件'{$event}'没有实现'".SessionEvents::class."'");
        }

        return $obj;

    }

    /**
     * 触发会话事件
     * @param $event
     * @param BaseSessionModel $sessionModel
     * @return BaseSessionModel
     */
    public static function trigger($event,BaseSessionModel $sessionModel = null){
        $instance = self::getInstance(__DIR__."/events");
        return $instance->find($event)->run($sessionModel);
    }
}