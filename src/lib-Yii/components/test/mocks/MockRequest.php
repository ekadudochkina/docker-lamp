<?php

namespace Hs\Test\Mocks;
use Hs\Test\Exceptions\RedirectionException as RedirectionException;

class MockRequest extends \CHttpRequest
{

    public function redirect($url, $terminate = true, $statusCode = 302)
    {
        throw new RedirectionException($url,"Redirected",$statusCode);
    }
}