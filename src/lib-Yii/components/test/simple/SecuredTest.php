<?php
namespace Hs\Test\Simple;

/**
 * Базовый класс для авторизированных тестов
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class SecuredTest extends \Hs\Test\FunctionalTestCase
{
    public function login()
    {
        $login = $this->getDefaultUserName();
        $password = $this->getDefaultPassword();
        $this->loginWithUser($login, $password);
    }
    
    /**
     * 
     * @param String $login
     * @param String $password
     */
    public function loginWithUser($login,$password)
    {
        $loginRoute = $this->getLoginRoute();
        $loginSelector = $this->getLoginInputSelector();
        $passwordSelector = $this->getPasswordInputSelector();
        $submitSelector = $this->getSubmitLoginFormSelector();
        
        $this->getBrowser()->getRoute($loginRoute);
        $this->getBrowser()->findElementBySelector($loginSelector)->sendKeys($login);
        $this->getBrowser()->findElementBySelector($passwordSelector)->sendKeys($password);
        $this->getBrowser()->findElementBySelector($submitSelector)->click();
    }

    abstract public function getDefaultUserName();

    abstract public function getDefaultPassword();

    abstract public function getLoginRoute();

    abstract public function getLoginInputSelector();

    abstract public function getPasswordInputSelector();

    abstract public function getSubmitLoginFormSelector();

}
