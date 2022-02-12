<?php

/**
 * Объект для парсинга сайтов с авторизацией
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs
 */
abstract class SecuredWebScrapper extends WebScrapper
{
    /**
     * Логин
     * @var String  
     */
    protected $login;
    
    /**
     * Пароль
     * @var String 
     */
    protected $password;

    
    private $permanentCookies = false;
    
    /**
     * @param String $login Логин
     * @param String $password Пароль
     */
    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Входит на сайт, где требуется авторизация
     * 
     * @param String $url  Url страницы логина
     * @param String[] $data Данные формы, которые нужно выслать (логин пароль)
     * @return \Psr\Http\Message\MessageInterface
     */
    protected function login()
    {
        $url = $this->getLoginUrl();
        $html = $this->getHTML($url);
        $data = $this->getLoginFormData($html);

        $client = $this->getClient();
        $options = array();
        $options['form_params'] = $data;

        $response = $client->request("POST", $url, $options);
        //bug::show($url,$data,$response->getHeaders());
        if (!$this->isLoggedIn())
            throw new Exception("Unable to login to the website");

        return $response;
    }

    /**
     * Запуск скраппинга
     */
    public function start()
    {
        if (!$this->isLoggedIn())
        {
            Yii::log("Not logged in, attemping to login.",  CLogger::LEVEL_WARNING);
            $this->login();
        }
        $this->execute();
    }

    /**
     * Проверка, является залогинен ли скраппер сейчас.
     * 
     * @return Bool
     */
    abstract protected function isLoggedIn();

    /**
     * Получение url страницы логина
     * 
     * @return String
     */
    abstract protected function getLoginUrl();

    /**
     * Получение данных для логина
     * 
     * @param SimpleXMLElement $html HTML страницы логина
     * @return String[] Ассоциативный массив данных формы для логина
     */
    abstract protected function getLoginFormData($html);

    /**
     * Непосредственно выполнение скраппинга
     */
    abstract protected function execute();
    
    /**
     * Включение режима постоянных кук.
     * В режиме постоянных кук они сохраняются в файл.
     */
    public function enablePermanentCookies()
    {
        $this->permanentCookies = true;
    }
    
    /**
     * Получение HTTP клиента
     * 
     * @param String[] Ассоциативный массив параметров клиента
     * @return \GuzzleHttp\Client
     */
    protected function getClient($options = null)
    {
        if(!$this->permanentCookies)
            return parent::getClient($options);
        
        $filePath = Yii::app()->getBasePath()."/runtime/cookies";
        $cookieJar = new GuzzleHttp\Cookie\FileCookieJar($filePath);
        $options['cookies'] = $cookieJar;
        $client = parent::getClient($options);
        return $client;
    }
}
