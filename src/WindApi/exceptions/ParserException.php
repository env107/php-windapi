<?php


namespace WindApi\exceptions;


use Throwable;

class ParserException extends WindApiException
{
    private $helpMsg = '';

    public function __construct($message = "" , $helpMsg = "", $code = 0, Throwable $previous = null)
    {
        $this->helpMsg = $helpMsg;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 帮助文本
     * @return string
     */
    public function getHelpMsg()
    {
        return $this->helpMsg;
    }
}