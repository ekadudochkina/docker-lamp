<?php
namespace Hs\Test\Exceptions;

use Throwable;

class RedirectionException extends \Exception
{
    private $url;

    public function __construct($url, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->url = $url;
        parent::__construct($message, $code, $previous);
    }

    public function getUrl()
    {
        return $this->url;
    }
}