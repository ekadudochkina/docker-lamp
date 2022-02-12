<?php
Yii::import("root.lib-Yii.tests.functional.controlPanel.*");
/**
 * Тесты для авторизации панели управления
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ControlPanelAuthTest extends ControlPanelTest 
{
    /**
     * Тест, что панель управления присутствует в проекте
     */
    public function testControlPanelExists()
    {
        $this->getBrowser()->getRoute($this->indexRoute);
        $el = $this->getBrowser()->findElementBySelector(".btn.btn-primary");
        $this->assertNotNull($el,"Не найдена кнопка логина");
    }
    
    /**
     * Тест входа в панель управления
     */
    public function testLogin()
    {
       //Добавление администратора
       $user = new SimpleAdmin();
       $user->setName("Admin admin");
       $user->setLogin($this->defaultAdminLogin);
       $user->setPassword($this->defaultAdminPassword);
       $user->setEmail($this->defaultAdminLogin);
       MigrationHelper::saveModel($user);
        
       $this->login();
       $this->assertPageHtml("You successfully logged in","Не удалось залогиниться");
    }

    

}

