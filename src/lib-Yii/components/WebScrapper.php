<?php

/**
 * Объект для парсинга HTML страниц
 *
 * @todo Необходимо доработать объект
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs
 */
abstract class WebScrapper
{

    /**
     * Режим дебага 
     * @var Bool
     */
    public $debug = false;

    /**
     * Клиент HTTP для совершения запросов
     * 
     * @var \GuzzleHttp\Client 
     */
    private $client = null;

    /**
     * Получение HTTP клиента
     * 
     * @param String[] Ассоциативный массив параметров клиента
     * @return \GuzzleHttp\Client
     */
    protected function getClient($options = null)
    {
        //Задача данной функции возвращать один и тот же клиент, чтобы
        //сохранялись куки и он вел себя как браузер

        if ($this->client)
        {
            return $this->client;
        }

        $clientOptions = array();
        $clientOptions['cookies'] = true;
        //$clientOptions['allow_redirects'] = true;
        $clientOptions['debug'] = $this->debug;
        if($options)
            foreach($options as $key => $value)
                $clientOptions[$key] = $value;
        
        $client = new GuzzleHttp\Client($clientOptions);

        $this->client = $client;
        // bug::show("creating client");
        return $client;
    }

    /**
     * Shortcut. Получение HTML страницы.
     * 
     * @example $this->getHTML($this->getUrl("auth/login"));
     * 
     * @param String $url Полный адрес страницы
     * @return SimpleXMLElement
     */
    protected function getHTML($url)
    {
        $response = $this->getClient()->get($url);
        $html = $response->getBody()->getContents();
        $xml = HtmlHelper::toXml($html);
        return $xml;
    }

    /**
     * Получение абсолютного адреса из релятивного
     * 
     * @param String $path Относительный адрес, например /auth/login
     * @return String Абсолютный адрес
     */
    protected function getUrl($path = null)
    {
        if ($path === null)
            return $this->getBaseUrl();

        $final = FileHelper::joinPaths($this->getBaseUrl(), $path);
        return $final;
    }

    /**
     * Просит пользователя ввести строку в консоли
     * 
     * @param String $message Сообщение, которое будет отображено пользователю
     * @return String Ответ пользователя
     */
    protected function prompt($message)
    {
        $cmd = new MockConsoleCommand();
        return $cmd->prompt($message);
    }
    
    /**
     * Получение адреса веб сайта с которым работает скраппер.
     * Например http://home-studio.pro
     */
    abstract protected function getBaseUrl();
}
