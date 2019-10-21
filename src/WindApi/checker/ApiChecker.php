<?php


namespace WindApi\checker;
use WindApi\exceptions\CheckerException;
use WindApi\request\RequestProvider;

/**
 * Class ApiChecker
 * 抽象的校验器类
 * 该类为衍生的校验器类提供辅助工具，开发者可以继承该校验器开发自己的校验方法
 * @package WindApi\checker
 */
abstract class ApiChecker implements ApiCheckerImpl
{

    /**
     * 签名数据包key值数组
     * 该数组指定签名数据包的key包含那些
     * @var array
     */
    protected $keySort = ['session_id','host','method','query','client_time','version','signature'];

    /**
     * 签名key值
     * 该变量指定程序应该从Header的某个key值中获取到签名数据
     * 假如填写signature,则从$_SERVER['HTTP_SIGNATURE'] 中访问
     * @var string
     */
    protected $signatureKey = 'authorization';

    /**
     * 校验签名数据方法
     * 默认的校验方法，支持重写
     * @param RequestProvider $requestProvider
     * 请求提供器，用来获取相关的请求信息
     * @return array $package
     * 返回解析后的签名数据包，以供后备使用
     */
    public function check(RequestProvider $requestProvider)
    {
        return [];
    }

    /**
     * 获取源签名数据
     * @param RequestProvider $requestProvider 请求提供器
     * @return string 签名数据
     */
    protected function signatureExtract(RequestProvider $requestProvider) {
        $header = $requestProvider->getHeaderInfo();
        if(!isset($header[$this->signatureKey])) {
            throw new CheckerException("Signature data not found in Request-Header");
        }
        return $header[$this->signatureKey];
    }

    /**
     * 生成签名包键值排列签名
     * @param array|null $keys
     * @return string
     */
    protected function keysSignature(array $keys = null){
        if(empty($keys)) {
            $keys = $this->keySort;
        }
        sort($keys);
        return sha1(implode("&", $keys));
    }


    /**
     * 拆解签名数据包
     * 支持重写解包程序
     * @param string $authorization 已加密的签名数据
     * @return array
     * 返回解包后的签名数据包
     */
    protected function unpack($authorization) {
        $unpack = null;

        if(! preg_match("/^{(.*?)+}$/",$unpack = base64_decode($authorization)) ) {
            throw new CheckerException("签名数据识别错误");
        }

        if( ($unpack = json_decode($unpack , true)) === NULL ){
            throw new CheckerException("签名数据解包错误");
        }

        return (array)$unpack;
    }

    /**
     * 加密签名数据包
     * @param array $package
     * @return string
     */
    protected function pack(array $package){
        $authorization = null;
        if(!is_array($package)) {
            throw new CheckerException("签名数据包不合法");
        }
        return base64_encode(json_encode($package));
    }
}