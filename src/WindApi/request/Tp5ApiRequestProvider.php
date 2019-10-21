<?php

namespace WindApi\request;


class Tp5ApiRequestProvider extends ApiRequestProvider
{
    public function getQueryData()
    {
       //由于thinkphp机制不同，因此需要重写该Request提供器
        return $_GET;
    }
}