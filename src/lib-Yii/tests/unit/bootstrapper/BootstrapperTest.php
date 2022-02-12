<?php

/**
 * Тестирование объекта запускающего Yii
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class BootstrapperTest extends \Hs\Test\NoDbTestCase
{

    /**
     * Проверка автоматического определения режима
     */
    public function testAutoMode()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");

        //проверяем production
        BootstrapperMock::$machineName = $this->getProductionServerName();
        $bs = new BootstrapperMock();
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_PRODUCTION, $actualMode, "Неверно определен автоматический production");
        $this->assertTrue($bs->isProduction(), "Неверно определен режим production");
        $this->assertFalse($bs->isDemo(), "Неверно определен режим demo");
        $this->assertFalse($bs->isTest(), "Неверно определен режим test");
        $this->assertFalse($bs->isLocal(), "Неверно определен режим local");

        //проверяем demo
        BootstrapperMock::$machineName = $this->getDevServerName();
        $bs = new BootstrapperMock();
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_DEMO, $actualMode, "Неверно определен автоматический demo");
        $this->assertFalse($bs->isProduction(), "Неверно определен режим production");
        $this->assertTrue($bs->isDemo(), "Неверно определен режим demo");
        $this->assertFalse($bs->isLocal(), "Неверно определен режим local");
        $this->assertFalse($bs->isTest(), "Неверно определен режим test");

        //проверяем local
        BootstrapperMock::$machineName = $this->getGenericWindowsName();
        $bs = new BootstrapperMock();
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_LOCAL, $actualMode, "Неверно определен автоматический local");
        $this->assertFalse($bs->isProduction(), "Неверно определен режим production");
        $this->assertFalse($bs->isDemo(), "Неверно определен режим demo");
        $this->assertTrue($bs->isLocal(), "Неверно определен режим local");
        $this->assertFalse($bs->isTest(), "Неверно определен режим test");

        //проверяем test
        BootstrapperMock::$machineName = $this->getWindowsServerName();
        $bs = new BootstrapperMock();
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_TEST, $actualMode, "Неверно определен автоматический test");
        $this->assertFalse($bs->isProduction(), "Неверно определен режим production");
        $this->assertFalse($bs->isDemo(), "Неверно определен режим demo");
        $this->assertFalse($bs->isLocal(), "Неверно определен режим local");
        $this->assertTrue($bs->isTest(), "Неверно определен режим test");
    }

    /**
     * Провряет, что локальный режим является режимом по-умолчанию для приложения
     */
    public function testLocalIsDefault()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");
        BootstrapperMock::$machineName = "";
        $bs = new BootstrapperMock();
        $this->assertTrue($bs->isLocal(), "Неверно определен режим local");
    }

    /**
     * Проверяет режим релиза, который недает менять режим в релизных приложениях
     * @covers Bootstraper::inRelease
     */
    public function testInRelease()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");
        BootstrapperMock::$machineName = $this->getProductionServerName();
        $bs = new BootstrapperMock(true, Bootstraper::MODE_LOCAL);
        $bs->inRelease();
        $this->assertEquals(Bootstraper::MODE_PRODUCTION, $bs->getCurrentMode(), "Удалось сменить режим релизному приложению");
        BootstrapperMock::$machineName = $this->getGenericWindowsName();
        $bs = new BootstrapperMock(true, Bootstraper::MODE_PRODUCTION);
        $bs->inRelease();
        $this->assertEquals(Bootstraper::MODE_LOCAL, $bs->getCurrentMode(), "Удалось сменить режим релизному приложению");

        BootstrapperMock::$machineName = $this->getDevServerName();
        $bs = new BootstrapperMock(true, Bootstraper::MODE_PRODUCTION);
        $bs->inRelease();
        $this->assertEquals(Bootstraper::MODE_DEMO, $bs->getCurrentMode(), "Удалось сменить режим релизному приложению");
    }

    /**
     * Тест, что на локальной машине создается база данных автоматически
     */
    public function testDbCreationOnLocal()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");

        //Удаляем БД
        $db = Yii::app()->getDb();
        $dbName = $db->dbname;
        $showCmd = "SHOW DATABASES WHERE `Database` = \"{$dbName}\"";
        $db->createCommand("DROP database `$dbName`")->execute();
        $result = $db->createCommand($showCmd)->queryAll();
        $this->assertEmpty($result, "База данных '{$dbName}' не удалилась");

        //Проверяем что на продакшен БД не создается
        BootstrapperMock::$machineName = $this->getProductionServerName();
        $app = Yii::app();
        $bs = new BootstrapperMock();
        $this->assertTrue($bs->isProduction(), "Режим не продакшен");
        $app = $bs->createWebApplication();


        $result = $db->createCommand($showCmd)->queryAll();
        $this->assertEmpty($result, "База данных '{$dbName}' создалась в режиме production");


        //Проверяем правильную работу
        BootstrapperMock::$machineName = $this->getGenericMacName();
        $bs = new BootstrapperMock();
        $this->assertTrue($bs->isLocal(), "Режим не локальный");
        $app = $bs->createWebApplication();

        $result = $db->createCommand($showCmd)->queryAll();
        $this->assertNotEmpty($result, "База данных '{$dbName}' не создалась");
        $db->createCommand("USE `{$dbName}`")->execute();
    }

    /**
     * Проверяем установку режима вручную
     */
    public function testImplicitModes()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");

        //Сначала проверим, что поумолчанию будет локальный режим 
        BootstrapperMock::$machineName = $this->getGenericMacName();
        $autoBs = new BootstrapperMock();
        $this->assertTrue($autoBs->isLocal(), "Бутстраппер не установлен в локальный режим перед тестом");

        //production
        $bs = new BootstrapperMock(null, Bootstraper::MODE_PRODUCTION);
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_PRODUCTION, $actualMode, "Неверно определен ручной production");
        $this->assertTrue($bs->isProduction(), "Неверно определен режим production");
        $this->assertFalse($bs->isDemo(), "Неверно определен режим demo");
        $this->assertFalse($bs->isTest(), "Неверно определен режим test");
        $this->assertFalse($bs->isLocal(), "Неверно определен режим local");

        //проверяем demo
        $bs = new BootstrapperMock(null, Bootstraper::MODE_DEMO);
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_DEMO, $actualMode, "Неверно определен ручной demo");
        $this->assertFalse($bs->isProduction(), "Неверно определен режим production");
        $this->assertTrue($bs->isDemo(), "Неверно определен режим demo");
        $this->assertFalse($bs->isLocal(), "Неверно определен режим local");
        $this->assertFalse($bs->isTest(), "Неверно определен режим test");

        //проверяем test
        $bs = new BootstrapperMock(null, Bootstraper::MODE_TEST);
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_TEST, $actualMode, "Неверно определен ручной test");
        $this->assertFalse($bs->isProduction(), "Неверно определен режим production");
        $this->assertFalse($bs->isDemo(), "Неверно определен режим demo");
        $this->assertFalse($bs->isLocal(), "Неверно определен режим local");
        $this->assertTrue($bs->isTest(), "Неверно определен режим test");

        //Для локальнго режима, нужно сменить умолчание
        BootstrapperMock::$machineName = $this->getProductionServerName();
        $autoBs = new BootstrapperMock();
        $this->assertTrue($autoBs->isProduction(), "Бутстраппер не установлен в production режим перед тестом");

        //проверяем local
        $bs = new BootstrapperMock(null, Bootstraper::MODE_LOCAL);
        $actualMode = $bs->getCurrentMode();
        $this->assertEquals(Bootstraper::MODE_LOCAL, $actualMode, "Неверно определен ручной local");
        $this->assertFalse($bs->isProduction(), "Неверно определен режим production");
        $this->assertFalse($bs->isDemo(), "Неверно определен режим demo");
        $this->assertTrue($bs->isLocal(), "Неверно определен режим local");
        $this->assertFalse($bs->isTest(), "Неверно определен режим test");
    }

    /**
     * Получние имя машины для продакшен сервера
     * @return string
     */
    protected function getProductionServerName()
    {
        $name = "Linux hs-production.pro 3.10.0-514.2.2.el7.x86_64 #1 SMP Tue Dec 6 23:06:41 UTC 2016 x86_64 x86_64 x86_64 GNU/Linux";
        return $name;
    }

    /**
     * Получние имя машины для дев сервера
     * @return string
     */
    protected function getDevServerName()
    {
        $name = "Linux dev.home-studio.pro 3.16.0-4-586 #1 Debian 3.16.7-ckt11-1+deb8u5 (2015-10-09) i686 GNU/Linux";
        return $name;
    }

    /**
     * Получние имя машины для виндовс
     * @return string
     */
    protected function getGenericWindowsName()
    {
        $name = "Windows NT XN1 5.1 build 2600";
        return $name;
    }

    /**
     * Получние имя машины для сервера Windows
     * @return string
     */
    protected function getWindowsServerName()
    {
        $name = "Windows NT *Name of machine* 6.0 build 6002 (Windows Server 2008 Standard Edition Service Pack 2) i586";
        return $name;
    }

    /**
     * Получние имя машины для мак
     * @return string
     */
    protected function getGenericMacName()
    {
        $name = "Darwin Macbook-Freddis.local 15.6.0 Darwin Kernel Version 15.6.0: Thu Jun 23 18:25:34 PDT 2016; root:xnu-3248.60.10~1/RELEASE_X86_64 x86_64";
        return $name;
    }

    /**
     * Завершение теста, возвращаем все как было
     */
    protected function tearDown()
    {
        BootstrapperMock::$machineName = null;
        parent::tearDown();
    }

}
