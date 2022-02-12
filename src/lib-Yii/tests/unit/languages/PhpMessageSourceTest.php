<?php

/**
 * Проверка настроек переводов, использующих массивы
 *
 * @see PhpMessageSource
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class PhpMessageSourceTest extends \Hs\Test\WebUnitTestCase
{

    /**
     * Язык приложения по-умолчанию должен быть английский
     */
    public function testEnglishIsDefault()
    {
        $bs = new BootstrapperMock();
        $bs->config = $bs->getDefaultConfig();
        $bs->createApp = true;
        $this->switchToWebApplication($bs);
        $this->assertEquals("en_Us", Yii::app()->sourceLanguage, "Не установился язык в Yii");
        $this->assertEquals("en_Us", Yii::app()->getLanguage(), "Не установился язык в Yii");
    }

    /**
     * Тестируем английский язык для панели управления
     */
    public function testEnglishControlPanel()
    {
        $data = $this->createRequestData(['login' => "", "password" => ""], "SimpleLoginForm");
        $bs = new BootstrapperMock();
        $bs->createApp = true;
        $this->switchToWebApplication($bs);
        Yii::app()->setLanguage("en");

        //Запрос
        $html = $this->postRequest("admin/login", $data);
        //bug::show($html);
        $msg = "Dear user, log in to access the admin area!";
        $this->assertContains($msg, $html, "Страница не переведена");
        $this->assertContains("cannot be blank", $html, "Ошибки не переведены");
    }

    /**
     * Тестируем русский язык для панели управления
     */
    public function testRussianControlPanel()
    {
        $data = $this->createRequestData(['login' => "", "password" => ""], "SimpleLoginForm");
        $bs = new BootstrapperMock();
        $bs->createApp = true;
        $this->switchToWebApplication($bs);
        Yii::app()->setLanguage("ru");

        //Запрос
        $html = $this->postRequest("admin/login", $data);
        $msg = "Добро пожаловать в панель управления!";
        $this->assertContains($msg, $html, "Страница не переведена");
        $this->assertContains("Необходимо заполнить поле", $html, "Страница не переведена");
    }

}
