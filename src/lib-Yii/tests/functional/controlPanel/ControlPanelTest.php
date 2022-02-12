<?php
/**
 * Тесты для панели управления
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class ControlPanelTest extends \Hs\Test\FunctionalTestCase 
{
    protected $loginRoute = "admin/login";
    protected $indexRoute = "admin/index";
    protected $defaultAdminLogin = "controlpanel@home-studio.pro";
    protected $defaultAdminPassword = "1q2w3e4rDD";
    
    /**
     * Вход в панель управления
     * 
     * @param Sring $login Логин
     * @param Sring $password Парель
     */
    public function login($login = null, $password = null)
    {
        if(!$login)
        {
            $login = $this->defaultAdminLogin;
            $password = $this->defaultAdminPassword;
        }
        
        $this->getBrowser()->getRoute($this->indexRoute);
        $loginInput = $this->getBrowser()->findElementBySelector("#SimpleLoginForm_login");
        $this->getBrowser()->waitForElement(".btn.btn-primary",3000);
        //Еще дополнительно ждем секундочку
        sleep(1);
        $loginInput->sendKeys($login);
        $passwordInput = $this->getBrowser()->findElementBySelector("#SimpleLoginForm_password");
        $passwordInput->sendKeys($password);
        $loginButton = $this->getBrowser()->findElementBySelector(".btn.btn-primary");
        $loginButton->click();
    }

}
