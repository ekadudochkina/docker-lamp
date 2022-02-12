<?php

namespace Hs\Test\Simple;

/**
 * Description of SimpleLoginCredentials
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class SimpleLoginCredentials implements ILoginCredentialsProvider
{
    public $login;
    public $password;
    public $loginRoute;
    public $passwordInputSelector;
    public $loginInputSelector;
    public $submitButtonSelector;

    public function getLoginInputSelector()
    {
        return $this->loginInputSelector;
    }

    public function getLoginRoute()
    {
        return $this->loginRoute;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPasswordInputSelector()
    {
        return $this->passwordInputSelector;
    }

    public function getSubmitLoginFormSelector()
    {
        return $this->submitButtonSelector;
    }

    public function getUserName()
    {
        return $this->login;
    }

}
