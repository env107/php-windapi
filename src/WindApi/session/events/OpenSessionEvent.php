<?php

namespace WindApi\session\events;


use WindApi\session\BaseSessionModel;

class OpenSessionEvent implements SessionEventsImpl
{

    /**
     * 会话事件触发方法
     * 该方法接受一个会话数据模型，开发者可以调用该方法处理会话数据模型
     * @param BaseSessionModel $sessionModel
     * @return BaseSessionModel
     */
    public function run(BaseSessionModel $sessionModel)
    {
        return $sessionModel;
    }
}