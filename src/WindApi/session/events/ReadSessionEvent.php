<?php

namespace WindApi\session\events;


use WindApi\session\BaseSessionModel;


class ReadSessionEvent implements SessionEventsImpl
{
    public function run(BaseSessionModel $sessionModel)
    {
        return $sessionModel;
    }

}