<?php
namespace Hs\Test\Selenium;

/**
 * Класс прослойка для веб-драйвера. 
 * Позволяет упростить управление драйвером и использовать стиль Yii.
 *
 * @package Hs\Test\Selenium
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class WebDriver extends \Facebook\WebDriver\Remote\RemoteWebDriver
{
    /**
     * Открытие страницы с запросом GET
     * 
     * @param String $url Адрес страницы
     */
    public function get($url)
    {
        $this->log($url);
        return parent::get($url);
    }
    
   /**
     * Открытие страницы с запросом GET используя роут, вместо URL
     * 
     * @param String $route Роут контроллера в стиле Yii
     */
    public function getRoute($route)
    {
        $url = \Yii::app()->createAbsoluteUrl($route);
        $this->log($route." >> ".$url);
        return parent::get($url);
    }

    /**
     * Логирование сообщения
     * 
     * @param String $msg Сообщение
     */
    public function log($msg)
    {
        $msg = get_called_class().": ".$msg;
        echo "$msg \n";
    }

    /**
     * Получение элемента по Css селектору
     * 
     * @param String $cssSelector Селектор для элемента
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findElementBySelector($cssSelector)
    {
        $by = \Facebook\WebDriver\WebDriverBy::cssSelector($cssSelector);
        $result = $this->findElement($by);
        return $result;
    }
    
    /**
     * Получение элементов по Css селектору
     * 
     * @param String $cssSelector Селектор для элемента
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findElementsBySelector($cssSelector)
    {
        $by = \Facebook\WebDriver\WebDriverBy::cssSelector($cssSelector);
        $result = $this->findElements($by);
        return $result;
    }

    /**
     * Ожидает появления элемента на странице
     * 
     * @param String $cssSelector Селектор для элемента
     * @param Number $time Время ожидания в милисекундах
     */
    public function waitForElement($cssSelector,$time = 5000)
    {
        $secs = floor($time/1000);
        $milisecs = $time - $secs*1000;
        $this->log("wait for element for {$secs}s {$milisecs}ms '$cssSelector'");
        $by = \Facebook\WebDriver\WebDriverBy::cssSelector($cssSelector);
        $condition = \Facebook\WebDriver\WebDriverExpectedCondition::presenceOfElementLocated($by);
        
        $this->wait($secs,$milisecs)->until($condition);
    }
    
    /**
     * Получает текст страницы. Не HTML, а именно видимый текст.
     * 
     * @return String
     */
    public function getPageText()
    {
        $result = $this->executeScript("return document.body.innerText");
        return $result;
    }

}
