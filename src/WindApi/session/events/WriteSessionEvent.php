<?php

namespace WindApi\session\events;


use WindApi\session\BaseSessionModel;


class WriteSessionEvent implements SessionEventsImpl
{
    public function run(BaseSessionModel $sessionModel)
    {
        //向模型添加额外的保存参数
        //....
        //例如添加ip地址
        $sessionModel->remote_ip = '127.0.0.1';
        return $sessionModel;
    }

}