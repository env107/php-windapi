<?php

namespace WindApi\checker;


use WindApi\request\RequestProvider;

/**
 * Interface ApiCheckerImpl
 * 校验器接口
 * @package WindApi\checker
 */
interface ApiCheckerImpl
{
    /**
     * 校验签名数据
     * @param RequestProvider $requestProvider 请求提供器
     * @return array $package
     * 约定检验完成后返回签名数据解包后的数据
     */
    public function check(RequestProvider $requestProvider);
}