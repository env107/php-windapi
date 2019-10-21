<?php

namespace WindApi\session\events;


use WindApi\session\BaseSessionModel;

/**
 * Interface SessionEventsImpl
 * 会话事件接口
 * 会话事件类必须实现该接口
 * 该接口提供一个run方法，便于会话事件管理类触发该接口方法
 * @package WindApi\session\events
 */
interface SessionEventsImpl
{


    /**
     * 会话事件触发方法
     * 该方法接受一个会话数据模型，开发者可以调用该方法处理会话数据模型
     * @param BaseSessionModel $sessionModel
     * @return BaseSessionModel
     */
    public function run(BaseSessionModel $sessionModel);

}