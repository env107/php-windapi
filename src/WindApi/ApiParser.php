<?php


namespace WindApi;
use WindApi\checker\ApiCheckerImpl;
use WindApi\exceptions\CheckerException;
use WindApi\exceptions\ParserException;
use WindApi\request\RequestProvider;

/**
 * WindApi签名校验器
 * 该签名校验器提供执行具体校验器的检测的方法，并可以指定校验器版本
 * 开发者只需要调用run方法即可以执行校验器进行签名校验
 * Class ApiParser
 * @package WindApi
 */
class ApiParser
{

    /**
     * Api校验器版本
     * 指定Api签名校验版本号，例如 1.0 ,程序将会查找对应 checker/v1_0 里的校验器
     * @var string
     */
    public static $version = '';

    /**
     * 服务端当前时间
     * 为了保证时间的统一性，故使用该变量统一
     * @var int
     */
    public static $server_time = 0;

    /**
     * ApiParser实例对象
     * @var ApiParser
     */
    private static $_instance = null;

    /**
     * ApiParser constructor.
     * 构造器，指定校验器版本
     * @param string $version
     */
    private function __construct($version)
    {
        if(empty($version)) {
            return true;
        }
        self::$version = self::parseApiVersion($version);
    }

    /**
     * 转化版本号
     * 将版本号转换为路径可识别的字符串
     * @param string $version
     * @return string
     */
    public static function parseApiVersion($version){
        return "v".str_replace(".","_",$version);
    }

    /**
     * 反转化版本号
     * 将路径识别的版本号转化为普通版本号表现形式
     * @return bool|string
     */
    public static function reverseApiVersion(){
        return substr(str_replace("_",".",self::$version),1);
    }

    /**
     * 获取ApiParser实例对象
     * @param $version
     * @return ApiParser
     */
    private static function getInstance($version){
        if(self::$_instance == null) {
            self::$server_time = time();
            self::$_instance = new self($version);
        }
        return self::$_instance;
    }

    /**
     * 创建校验器
     * @param string $class_name
     * 可指定校验器的类名，如果不指定则默认指定校验器的类名为AuthApiChecker
     * @return ApiCheckerImpl
     * 该方法处理后会返回一个 ApiCheckerImpl
     */
    private function _buildApiChecker($class_name = null){
        if(empty($class_name)) {
            $class_name = "AuthApiChecker";
        }
        $classPath = "\\WindApi\\checker\\".self::$version."\\".$class_name;
        $checker = new $classPath();
        if(! ($checker instanceof ApiCheckerImpl)) {
            throw new ParserException("类'{$classPath}'必须实现'".ApiCheckerImpl::class."'接口");
        }
        return $checker;
    }

    /**
     * 校验Api签名数据
     * 默认的获取签名数据方式是从$_SERVER['HTTP_AUTHORIZATION']中取得
     * @param RequestProvider $requestProvider
     * 请求提供器,提供相关的请求数据
     * @param string $version
     * 指定校验器版本号，程序会自动将该版本号将自动转换为路径识别的版本号，并自动查找对应的校验器
     * 指定Api校验器版本,程序将自动加载对应的校验器进行校验
     * @param string $checkerClassName
     * 指定校验器类的名字，程序将会自动查找checker/{$version}/下的校验器，如果不指定则使用AuthApiChecker作为默认校验器类名
     * @return array
     * 要求校验成功后返回已解包的签名数据数组
     */
    public static function run(RequestProvider $requestProvider,$version = '1.0',$checkerClassName = null){
        try {
            return self::getInstance($version)
                            ->_buildApiChecker($checkerClassName)
                            ->check($requestProvider);
        } catch (CheckerException $checkerException) {
            throw new ParserException($checkerException->getMessage());
        }
    }







}