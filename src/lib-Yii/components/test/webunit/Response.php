<?php
namespace Hs\Test\WebUnit;

class Response
{
    private $status;
    private $body;

    public function __construct($status, $body)
    {
        $this->status = $status;
        $this->body = $body;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getBody()
    {
        return $this->body;
    }
}