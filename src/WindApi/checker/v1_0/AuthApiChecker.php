<?php


namespace WindApi\checker\v1_0;


use WindApi\ApiParser;
use WindApi\checker\ApiChecker;
use WindApi\exceptions\CheckerException;
use WindApi\request\RequestProvider;

/**
 * Class AuthApiChecker
 * 初代衍生的校验器
 * @package WindApi\checker\v1_0
 */
class AuthApiChecker extends ApiChecker {

    private  $_client_keys = '';

    private $_server_keys = '';

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
        $authorization = $this->signatureExtract($requestProvider);
        $package = $this->checkAuthorization($authorization); //校验签名数据完整性
        $this->checkRequest($package,$requestProvider); //校验请求信息
        $this->checkCompleteness($package,$requestProvider); //完整性校验
        return $package;
    }

    /**
     * 验证签名数据的完整性
     * @param string $authorization 签名
     * @return array
     * 验证成功后返回解包后的数据包
     */
    private function checkAuthorization($authorization){
        $package = $this->unpack($authorization);
        $this->_client_keys = $this->keysSignature(array_keys($package));
        $this->_server_keys = $this->keysSignature();
        if($this->_client_keys !== $this->_server_keys) {
            throw new CheckerException("签名数据不合法");
        }
        return $package;
    }

    /**
     * 验证query签名数据是否完整
     * @param $package
     * @param RequestProvider $requestProvider
     * @return bool
     */
    private function checkRequest($package,RequestProvider $requestProvider){
        $header = $requestProvider->getHeaderInfo();
        if(ApiParser::parseApiVersion($package['version']) != ApiParser::$version) {
            throw new CheckerException("签名版本不正确");
        }
        if(!isset($header['origin'])){
            throw new CheckerException("无法获取访问来源");
        }

        if($package['host'] !== urlencode($header['origin'])) {
            throw new CheckerException("访问来源信息不正确");
        }
        if(strtolower($package['method']) !== $requestProvider->getRequestMethod()) {
            throw new CheckerException("请求方法不正确");
        }

        if($package['client_time'] - ApiParser::$server_time > 1){
            throw new CheckerException("签名有效期超时");
        }
        return true;
    }

    /**
     * 检查签名数据包是否完整
     * 通过服务端自己组装的签名包数据与客户端提供的签名包数据进行对比，校验是否完整
     * @param $client_package
     * @param RequestProvider $requestProvider
     * @return bool
     */
    private function checkCompleteness($client_package,RequestProvider $requestProvider){
        $server_package = $this->_buildServerPackage($client_package,$requestProvider);
        $server_package = $this->pack($this->_queuePackage($server_package));
        $client_package = $this->pack($this->_queuePackage($client_package));

        if($server_package !== $client_package) {
            throw new CheckerException("客户端签名数据被不完整");
        }
        return true;
    }

    /**
     * 构建服务端的package
     * @param $package
     * @param RequestProvider $requestProvider
     * @return array
     */
    private function _buildServerPackage($package,RequestProvider $requestProvider){
        $header = $requestProvider->getHeaderInfo();
        $session_id = $this->_getKey($package,"session_id");
        $host = urlencode($header['origin']);
        $method = $requestProvider->getRequestMethod();
        $query = $this->_getEncodeQueryString($requestProvider->getQueryData());
        $client_time = strval($package['client_time']);
        $version = ApiParser::reverseApiVersion();
        $signature = $this->_buildServerQuerySignature($requestProvider,$client_time);
        return array_combine($this->keySort,[$session_id,$host,$method,$query,$client_time,$version,$signature]);
    }

    /**
     * 排序package
     * @param array $package
     * @return array
     */
    private function _queuePackage(array $package){
        $key_package = array_keys($package);
        sort($key_package);
        $data = [];
        foreach ($key_package as $key){
            $data[$key] = $package[$key];
        }
        return $data;
    }

    /**
     * 返回数据包的值，如果不存在则返回空字符串
     * @param array $package
     * @param string $key
     * @return string
     */
    private function _getKey(array $package,$key){
        return isset($package[$key]) ? $package[$key] : '';
    }

    /**
     * 获取已经进行编码的query数据
     * @param array $query 未进行编码的query数组
     * @return string
     */
    private function _getEncodeQueryString(array $query){
        $sort_query = [];
        if(empty($query)){
            return "";
        }
        //1.将键进行字典排序
        $keys = array_keys($query);
        sort($keys);
        //2.将键和键值进行uri编码
        foreach ($keys as $key){
            array_push($sort_query,urlencode($key)."=".urlencode($query[$key]));
        }
        return implode("&",$sort_query);
    }


  /**
     * 生成本地Query签名字符串
     * @param RequestProvider $requestProvider 请求提供器
     * @param integer $server_time 当前服务端时间
     * @return string 返回签名字符串
     */
    private function _buildServerQuerySignature(RequestProvider $requestProvider,$server_time){
        $query = $requestProvider->getQueryData();
        $header = $requestProvider->getHeaderInfo();
        $querySha = sha1($this->_getEncodeQueryString($query));
        $queryUrl = urlencode($header['origin'])."#".$requestProvider->getRequestMethod()."?query=".$querySha;
        return hash_hmac("sha256",$queryUrl,$server_time);
    }

    /**
     * 模拟生成客户端的Query签名字符串
     * 用于与本地生成的Query签名字符串进行
     * @param $package
     * @return string
     */
    private function _buildClientQuerySignature($package){
        $query = $package['query'];
        $queryData = sha1($query);
        $queryUrl = $package['host']."#".$package['method']."?query=".$queryData;
        return hash_hmac("sha256",$queryUrl,$package['client_time']);
    }

}