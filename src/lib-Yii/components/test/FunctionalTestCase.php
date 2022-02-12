<?php

namespace Hs\Test;

/**
 * Базовый класс для функциональных тестов
 *
 * @package Hs\Test
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class FunctionalTestCase extends WebUnitTestCase
{

    protected $defaultTimeout = 30;
    protected $debug = false;
    /**
     * Функциональный тест запускается в первый раз.
     * В этом случае необходимо вырубить старый селениум
     * @var Boolean
     */
    protected static $firstRun = true;

    /**
     * 
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver 
     */
    private $browser;
    private $seleniumServerTimeout = 2;

    /**
     * Набор действий перед запуском теста
     */
    protected function setUp()
    {
        
        $this->startSeleniumServer();
        $this->backupApplication();
        $this->switchToWebApplication();
        $this->browser = $this->startBrowser();
        parent::setUp();
    }

    /**
     * Набор действий после запуска теста
     */
    protected function tearDown()
    {
        if (!$this->debug)
        {
            $this->log("Closing browser");
            $this->browser->quit();
        }
        $this->restoreApplication();
        parent::tearDown();
    }

    /**
     * Получение текущего объекта браузера
     * 
     * @return \Hs\Test\Selenium\WebDriver
     */
    protected function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Запуск браузера
     * 
     * @param String $proxy Прокси
     * @return \Hs\Test\Selenium\WebDriver
     */
    public function startBrowser($proxy = null)
    {
        $this->log("Запускаем браузер");
        $host = 'http://localhost:4444/wd/hub';
        $cap = \Facebook\WebDriver\Remote\DesiredCapabilities::chrome();

        if ($proxy)
        {
            // For Chrome
            $options = new \Facebook\WebDriver\Chrome\ChromeOptions();
            $args = ["--proxy-server=socks5://$proxy"];
            $options->addArguments($args);
            $cap->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $options);
        }
        $driver = Selenium\WebDriver::create($host, $cap, $this->defaultTimeout * 1000, $this->defaultTimeout * 1000);


        // $driver->manage()->timeouts()->pageLoadTimeout($this->defaultTimeout);
        //bug::reveal($driver,$cap);
//        $desiredCapabilities = new Nearsoft\SeleniumClient\DesiredCapabilities("chrome");
//        if($proxy)
//        {
//            $proxyConf = [];
//            $proxyConf['proxyType'] = "manual";
//            $proxyConf['socksProxy'] = $proxy;
//            $desiredCapabilities->setCapability(\Nearsoft\SeleniumClient\CapabilityType::PROXY, $proxyConf);
//        }
//        
//        $driver = new \Facebook\WebDriver\Remote\RemoteWebDriver($desiredCapabilities);

        return $driver;
    }

    /**
     * Запуск сервера Селениум. Он необходим для работы браузера в функциональных тестах
     * 
     * @return boolean True, в случае успеха
     */
    protected function startSeleniumServer()
    {
        $this->log("Запускаем Selenium");
        if(self::$firstRun)
        {
            $this->log("Пытаемся выключить старый Selenium");
            $shutdownUrl = "http://localhost:4444/extra/LifecycleServlet?action=shutdown";
            @file_get_contents($shutdownUrl);
            sleep(1);
            self::$firstRun = false;
        }
        \EnvHelper::enableComposer();
        $client = new \GuzzleHttp\Client();
        try
        {
            $result = $client->get("localhost:4444");
            $this->log("Selenium уже запущен");
            return true;
        } catch (\Exception $ex)
        {
            $this->log("Selenium не запущен, запускаем");
        }
        //bug::drop($result);
        $utilsPath = \Yii::getPathOfAlias("root.lib-Yii.utils.browsers");
        
        if (!\EnvHelper::isWindows())
        {
        
            $javapath = 'java';
            $path = \FileHelper::joinPaths($utilsPath, "chromedriver");
        } else
        {   
            
            $files = ['пиздец','jre1.8.0', 'пиздец'];
            $javaFolderPath = 'C:\"Program Files"\Java';
            
            foreach ($files as $file)
            {
                $javaFolderPathChanged=str_replace('"', '', $javaFolderPath);
                $jrePart = \FileHelper::findFile($javaFolderPathChanged, $file);
                if ($jrePart!==null) { break; }
            }
            if($jrePart == null)
            {
                
                throw new \Exception("Путь к Java не найден");
                
            }
            $innerPath = '\bin\java.exe';
            $javapath = \FileHelper::joinPaths($javaFolderPath, $jrePart,$innerPath);
            $path = \FileHelper::joinPaths($utilsPath, "chromedriver.exe");
        }
        //
        $seleniumserverpath = \FileHelper::joinPaths($utilsPath, "selenium-server-standalone-3.3.0.jar");
        $driverPath = " -Dwebdriver.chrome.driver=$path";
        $javapath .= $driverPath;

        $this->log("Path to java: $javapath");
        $this->log("Path to selenium server: $seleniumserverpath");

        $cmd = "$javapath -jar $seleniumserverpath  -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port 4444";
        $this->log("Running cmd in background: '$cmd'");
        flush();
        \EnvHelper::execInBackground($cmd);
        $this->log("Selenium server started");
        sleep($this->seleniumServerTimeout);
    }

    /**
     * Проверяет наличие HTML на странице
     * 
     * @param String $text HTML для поиска
     * @param String $message Сообщение об ошибке в случае неудачи
     */
    public function assertPageHtml($text, $message = "")
    {
        $source = $this->getBrowser()->getPageSource();
        $this->assertContains($text, $source, $message);
    }

    /**
     * Проверяет отсутствие HTML на странице
     * 
     * @param String $text HTML для поиска
     * @param String $message Сообщение об ошибке в случае неудачи
     */
    public function assertPageNoHtml($text, $message = "")
    {
        $source = $this->getBrowser()->getPageSource();
        $this->assertNotContains($text, $source, $message);
    }
    
    /**
     * Установка текущего браузера
     * @param \Facebook\WebDriver\Remote\RemoteWebDriver  $browser
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
    }

}
