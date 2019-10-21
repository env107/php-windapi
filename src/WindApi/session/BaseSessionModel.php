<?php
namespace WindApi\session;
use WindApi\exceptions\SessionRuntimeException;
use WindApi\session\models\ExtraProperties;
use WindApi\session\models\SessionModel;

/**
 * 会话数据模型
 * 该模型为一个抽奖的会话数据模型，它定义了基本不可删除的属性
 * 并提供一系列获取和生成会话属性的方法
 * Class BaseSessionModel
 * @package WindApi\sessi on
 */
abstract class BaseSessionModel implements SessionModel
{

    /**
     * 指定会话模型基本属性
     * 该属性不可被删除，如果该属性被删除或者存在null则会抛出异常
     * @var array
     */
    private $_must = ['session_id','session_data','session_time','expires_time'];

    /**
     * 会话数据模型数据
     * 该属性储存会话数据的基本属性，开发者如果创建了一个会话数据模型，可以实现ExtraProperties接口进行添加属性
     * 该属性可通过toArray()方法转化为数据模型数组
     * @var array
     */
    private $_properties = [
        'session_id' => '',
        'session_data' => '',
        'session_time' => 0,
        'expires_time' => 0
    ];

    /**
     * BaseSessionModel constructor.
     * 检查必需属性以及初始化属性
     */
    public function __construct()
    {
        //检查模型必须的字段
        try {
           $reflect = new \ReflectionClass($this);
           //如果实现了ExtraProperties接口则直接调用接口append方法
           if($reflect->implementsInterface(ExtraProperties::class)){
               $extra = $this->append($this);
               foreach ($extra as $key => $value) {
                   if(!isset($this->_properties[$key])) {
                       $this->_properties[$key] = $value;
                   }
               }
           }
           //判断必需属性是否存在
           foreach ($this->_must as $value) {
               if(!key_exists($value,$this->_properties)) {
                   throw new SessionRuntimeException("属性'{$value}'必须在SessionModel中");
               }
           }
        }catch (\ReflectionException $reflectionException) {
            throw new SessionRuntimeException("ReflectionError:".$reflectionException->getMessage());
        }
    }

    /**
     * 修改器
     * 开发者可以直接使用实例对象指定properties中的值
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function __set($name, $value)
    {
        return $this->setProperty($name,$value);
    }

    /**
     * 获取器
     * 开发者可以直接使用实例对象获取properties中的值
     * @param $name 键名
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getProperty($name);
    }

    /**
     * 设定properties中的值
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setProperty($key,$value)
    {
        if(isset($this->_properties[$key])) {
            $this->_properties[$key] = $value;
        }
        return true;
    }

    /**
     * 获取properties中的值
     * @param string $key
     * @return mixed
     */
    public function getProperty($key)
    {
        if(isset($this->_properties[$key])) {
            return $this->_properties[$key];
        }
        return null;
    }


    /**
     * 返回properties数据
     * @return array
     */
    public function toArray()
    {
        return $this->_properties;
    }

    /**
     * 将数据注入会话数据模型中
     * @param array $session
     * 数据数组，该数组的数据将会被注入到会话数据模型中
     * @return $this|BaseSessionModel
     */
    public function fit(array $session)
    {
        if(empty($session)) {
            return $this;
        }
        foreach ($session as $key => $value) {
            $this->_properties[$key] = $value;
        }
        return $this;
    }
}