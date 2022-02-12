<?php

namespace Hs\Test;

/**
 * Функциональный тест, проверяющий, что все страницы работают и не кидают исключений.
 *
 * @package Hs\Test
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class FunctionalSanityTest extends FunctionalTestCase
{
    /**
     * Получение массива url, которые необходимо протестировать на отсутствие ошибок
     * 
     * @return String[] Массив URL для тестирования
     */
    abstract function getUrls();
    
    /**
     * Проверят отображена ли ошибка 404
     * 
     * @return Bool True, если отображается ошибка 404
     */
    public function is404() {
        $source = $this->getBrowser()->getPageSource();
        $search = ">404</";
        $has = \StringHelper::hasSubstring($source, $search);
        return $has;
    }
    
    /**
     * Тестирует наличие ошибки 404
     */
    public function test404()
    {
        $this->getBrowser()->getRoute("site/dsdadsadasdasd");
        if(!$this->is404())
        {
            $this->fail("Страница 404 не отобразилась");
        }
        
    }
    
    /**
     * Тестирует, что на страницах не отображаются исключения и ошибки 404
     */
    public function testNoExceptions()
    {
       $urls = $this->getUrls();
       foreach($urls as $url)
       {
           $this->getBrowser()->get($url);
           $this->assertPageNoHtml("<title>ErrorException</title>","На странице присутствует исключение");
           $this->assertPageNoHtml("<h2>Stack Trace</h2>","На странице присутствует исключение");
           if($this->is404())
           {
               $this->fail("На странице присутствует ошибка 404");
           }
           
       }
    }
}
