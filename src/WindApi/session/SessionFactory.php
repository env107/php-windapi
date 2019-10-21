<?php


namespace WindApi\session;


use WindApi\exceptions\SessionRuntimeException;
use WindApi\session\driver\SessionExtract;
use WindApi\session\driver\options\Options;

/**
 * Class SessionFactory
 * 实例生成工厂类
 * 主要生成驱动，数据模型实例
 * @package WindApi\session
 */
class SessionFactory
{
    /**
     * 保存会话处理驱动
     * @var array
     */
    private $support = [];

    private static $instance = null;

    /**
     * SessionFactory constructor.
     *
     * @param null $dirpath
     */
    private function __construct($dirpath = null)
    {
        //查询支持的驱动
        $resource =opendir($dirpath);
        if($resource === FALSE) {
            throw new SessionRuntimeException("驱动路径无效");
        }
        while (false !== ($file = readdir($resource))) {
           if($file != '.' && $file != '..') {
               preg_match("/^([a-zA-Z]+)SessionDriver.php$/",$file,$driver);
               if(isset($driver[1])) {
                   $type = strtolower($driver[1]);
                   array_push($this->support,$type);
               }
           }
        }
        closedir($resource);
    }

    /**
     * 获取SessionFactory实例
     * @return null|SessionFactory
     */
    private static function getInstance(){
        if(self::$instance == null) {
            self::$instance =  new SessionFactory(__DIR__ .DIRECTORY_SEPARATOR."driver");
        }

        return self::$instance;
    }

    /**
     * 构建指定类型的会话驱动对象
     * @param string $type
     * @param Options $options
     * @return SessionExtract
     */
    public static function buildSessionDriver($type = "mysql",Options $options = null){
        $factory = self::getInstance();
        if(!in_array($type,$factory->support)) {
            throw new SessionRuntimeException("'{$type}'驱动不存在");
        }
        $type = strtolower($type);
        $class = "\\WindApi\\session\\driver\\".ucfirst($type)."SessionDriver";
        try{
            $refClass = new \ReflectionClass($class);
            if(!$refClass->implementsInterface(SessionExtract::class)){
                throw new SessionRuntimeException("'{$type}'驱动必须实现'".SessionExtract::class."'接口");
            }
            $method = $refClass->getMethod("getSessionDriverInstance");
            if($method->isStatic()){
               return $method->invokeArgs($refClass,[$options]);
            }else{
                throw new SessionRuntimeException("'{$type}'驱动'getSessionDriverInstance'方法必须为静态");
            }
        }catch (\ReflectionException $reflectionException){
            throw new SessionRuntimeException("ReflectError:".$reflectionException->getMessage());
        }
    }

    /**
     * 构建会话数据模型实例
     * @param string $class 实例模型类
     * @return BaseSessionModel
     */
    public static function buildSessionModel($class){
        if(!class_exists($class)) {
            throw new SessionRuntimeException("类'{$class}'不存在");
        }
        $model = new $class();
        if(! ($model instanceof BaseSessionModel)) {
            throw new SessionRuntimeException("类'{$class}'必须是'".BaseSessionModel::class."'");
        }
        return $model;
    }

}