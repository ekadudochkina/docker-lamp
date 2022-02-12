<?php

/**
 * Тестируем объект блокуирующий веб-сайт во время обновлений
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MaintananceTest extends \Hs\Test\WebUnitTestCase
{
    protected $checkString  = "Sorry for the inconvenience";
    protected $defaultRoute = "maintenanceMock/index";
    protected $controllerPath;
    protected $bootstrapper = null;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->controllerPath = Yii::getPathOfAlias("root.lib-Yii.tests.mocks.maintenance");
        parent::__construct($name, $data, $dataName);
    }
    
    /**
     * Возвращаем все на места
     */
    protected function tearDown()
    {
        //Отключаем режим обслуживания
        $manager = new MaintenanceManager();
        $manager->turnOn();
        parent::tearDown();
    }


    /**
     * Успешный тест, что веб-сайт блокируется и разблокируется
     */
    public function testMaintance()
    {
        $manager = new MaintenanceManager();
        $manager->turnOn();
        
        $html = $this->forward($this->defaultRoute,$this->controllerPath);
        $hasString = StringHelper::hasSubstring($html,$this->checkString);
        $this->assertFalse($hasString,"Изначально отображается режим обсулживания");

        $manager->turnOff();
        $html = $this->forward($this->defaultRoute,$this->controllerPath);
        $this->assertFalse($manager->isAvailable(),"Режим не включился");
        $this->assertFalse($manager->isWhiteList(),"Ip в белом списке");
        //bug::drop($html);
        $hasString = StringHelper::hasSubstring($html,$this->checkString);
        $this->assertTrue($hasString,"Режим обслуживания не отобразил HTML");
        
        $manager->turnOn();
        $html = $this->forward($this->defaultRoute,$this->controllerPath);
        $hasString = StringHelper::hasSubstring($html,$this->checkString);
        $this->assertFalse($hasString,"Изначально отображается режим обсулживания");
        //$this->log($this->forward($this->defaultRoute));
    }
    
    
    /**
     * Тест, что режим обслуживания не подведет, даже, если база данных не работает.
     */
    public function testNoDbMaintenance()
    {
        //В данном тесте подменяется бутстраппер getBootstrapper();
        $this->throwExceptions = true;
        
        try
        {
            $html = $this->forward($this->defaultRoute,$this->controllerPath);
            $this->fail("Не было исключения базы данных. Тест бессмысленный.");
        }
        catch(CDbException $ex)
        {
            $check = "failed to open the DB connection";
            $hasString = StringHelper::hasSubstring($ex->getMessage(), $check);
            $this->assertTrue($hasString,"Было выданно не верное исключение. Тест сомнителен.");
            $this->log("Успешно поймано исключение");
        }
        
        //Влючаем режим обслуживания
        $manager = new MaintenanceManager();
        $manager->turnOff();
        $this->assertFalse($manager->isAvailable(),"Режим не включился");
        $this->assertFalse($manager->isWhiteList(),"Ip в белом списке");
        $html = $this->forward($this->defaultRoute,$this->controllerPath);
        //bug::drop($html);
        $hasString = StringHelper::hasSubstring($html,$this->checkString);
        $this->assertTrue($hasString,"Режим обслуживания не отобразил HTML");
        
        //Yii::app()->getDb()->createCommand("Show databases");
    }
    
    /**
     * Функция возвращающая бутстрапер для приложения.
     * 
     * @return Bootstraper
     */
    public function getBootstrapper()
    {
       
        if($this->getName() == "testNoDbMaintenance")
        {
            Yii::import("root.lib-Yii.tests.mocks.*");
        //Используем бутстраппер со сломанным конфигом БД
        $bs = new BadDbBootstrapper();
        $bs->createApp = true;
            return $bs;
        }
        return parent::getBootstrapper();
    }
    
}
