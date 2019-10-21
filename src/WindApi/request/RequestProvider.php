<?php

namespace WindApi\request;

/**
 * Interface RequestProvider
 * 请求提供器接口
 * 该接口类提供获取请求信息的一系列接口，开发者在便携自己的请求提供器时候需要实现该接口
 * @package WindApi\request
 */
interface RequestProvider
{
    /**
     * 获取query数据
     * @return array
     */
    public function getQueryData();

    /**
     * 获取post数据
     * @return array
     */
    public function getPostData();

    /**
     * 获取头信息数据
     * @return array
     */
    public function getHeaderInfo();

    /**
     * 获取请求方法
     * 例如 options , get , post等 (建议返回小写字符串)
     * @return string
     */
    public function getRequestMethod();

}