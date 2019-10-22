<?php


namespace WindApi\request;


abstract class ApiRequestProvider implements RequestProvider
{

    /**
     * 获取query数据
     * query数据默认是从$_GET中获取，你也可以新建一个请求提供器，并重写该方法，自定义返回query数据
     * @return array
     */
    public function getQueryData()
    {
        return (array)$_GET;
    }

    /**
     * 获取post数据
     * 默认的post数据是从$_POST中获取
     * @return array
     */
    public function getPostData()
    {
       return (array)$_POST;
    }

    /**
     * 获取头信息
     * 默认从$_SERVER['HTTP_*']中获取相关的头信息数据，你也可以自定义获取属于自己的头信息数据
     * 你只需要新建一个请求提供器，并重写该方法
     * @return array
     */
    public function getHeaderInfo()
    {
        $header = [];
        foreach ($_SERVER as $key => $value){
            if(substr($key,0,5) === "HTTP_"){
                $header[strtolower(substr($key,5))] = $value;
            }
        }
        //获取本地访问来源
        if(!isset($header['origin'])) {
            $protocol = explode("/",$_SERVER['SERVER_PROTOCOL']);
            $header['origin'] = strtolower($protocol[0])."://".$_SERVER['HTTP_HOST'];
        }
        return $header;
    }

    /**
     * 获取请求方法
     * 例如 options , get , post等 (建议返回小写字符串)
     * @return string
     */
    public function getRequestMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}