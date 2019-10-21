<?php

namespace WindApi\exceptions;


use Throwable;

class SessionTimeoutException extends WindApiException
{
    private $timeout_session_id = '';

    public function __construct($timeout_session_id , $message = "", $code = 0, Throwable $previous = null)
    {
        $this->timeout_session_id = $timeout_session_id;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getTimeoutSessionId()
    {
        return $this->timeout_session_id;
    }
}