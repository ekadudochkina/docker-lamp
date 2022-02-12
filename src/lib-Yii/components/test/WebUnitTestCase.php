<?php

namespace Hs\Test;

use Hs\Test\Exceptions\RedirectionException;
use Hs\Test\Mocks\MockRequest;
use Hs\Test\WebUnit\Response;

/**
 * Класс юнит тестов для веб-контроллеров.
 * Такие тесты быстрее функциональных тестов.
 *
 * Для своей работы таким тестам приходится подменять консольное приложение веб-приложением. Иначе многие функции будут недоступны.
 * 
 * @package Hs\Test
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class WebUnitTestCase extends TestCase
{

    protected $prevApp;
    protected $throwExceptions = true;

    /**
     * Набор действий перед запуском теста
     */
    protected function setUp()
    {
        parent::setUp();
        \Yii::import("root.lib-Yii.tests.mocks.*");
        $this->throwExceptions = true;
        $this->backupApplication();
        $this->switchToWebApplication();
    }

    /**
     * Набор действий после запуска теста
     */
    protected function tearDown()
    {
        $this->restoreApplication();
        parent::tearDown();
    }

    /**
     * Запускает контроллер и возвращает сгенерированный HTML
     * 
     * @param String $route Роут, который необходимо запустить
     * @param String $controllerPath Путь к папке контроллеров (указывается, если контроллер лежит в необычном месте)
     * @return String HTML страницы
     * @throws \Exception
     */
    public function forward($route,$data = [], $controllerPath = null)
    {
        $content = null;
        $this->log("Requesting ".$route);
        $result = null;
        try
        {
            if ($controllerPath)
            {
                \Yii::app()->setControllerPath($controllerPath);
            }

            ob_start();
            $_GET = $data;
//            $_POST = $data;
//            $_REQUEST = $data;
            $request = new MockRequest();
            \Yii::app()->setComponent("request",$request);
            $controller = new \BaseControllerMock("sds");
            $controller->forward($route, false);
            $content = ob_get_clean();
        }
        catch(RedirectionException $ex)
        {
            ob_end_clean();
            $redirectUrl = $ex->getUrl();
            $request = new MockRequest();
            $domain = \UrlHelper::getDomain($redirectUrl);
            $parts = explode($domain,$redirectUrl);
            $_SERVER["REQUEST_URI"] = count($parts) > 1 ? $parts[1] : "/";
            $this->log("Redirecting to '$redirectUrl'");

            //Чистим параметры
            $_POST = [];
            $_REQUEST = [];
            $_GET = [];
            $route= \Yii::app()->getUrlManager()->parseUrl($request);
            $newParams = $_GET;
            return $this->forward($route,$newParams);
        }
        catch (\Exception $e)
        {
            ob_end_clean();
            $this->log("Thrown Exception on forward");
            if ($this->throwExceptions)
            {
                // Если будем кидать ошибку, то приложение нужно восстановить заранее
                //$this->restoreApplication();
                throw $e;
            }
        }
        //Восстанавливаем настройки
        //$this->restoreApplication();
        return $content;
    }

    /**
     * Получение объекта бутстраппера.
     * Возвращается поддельный, так как ему нужно будет пересоздать приложение, но не объявлять дважды классы
     * 
     * @return \Bootstrapper
     */
    public function getBootstrapper()
    {
        \Yii::import("root.lib-Yii.tests.mocks.BootstrapperMock");
        $bs = new \BootstrapperMock();
        $bs->createApp = true;
        return $bs;
    }

    /**
     * Сохраняет текущее приложение
     */
    public function backupApplication()
    {
        $this->prevApp = \Yii::app();
        $this->prevFileName = $_SERVER['SCRIPT_FILENAME'];
        $this->prevScriptName = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Восстанавливает предыдущее приложение
     */
    public function restoreApplication()
    {

        $_SERVER['SCRIPT_FILENAME'] = $this->prevFileName;
        $_SERVER['SCRIPT_NAME'] = $this->prevScriptName;
        \Yii::setApplication(null);
        \Yii::setApplication($this->prevApp);
    }

    /**
     * Подменяет текущее приложение Веб Приложением. 
     * Это необходимо, так как изначально тесты запускаются в консольном приложении, которое не имеет многих функций.
     * 
     * @param Bootstrapper $bootstrapper
     */
    public function switchToWebApplication($bootstrapper = null)
    {
        $newScriptName = \FileHelper::joinPaths(\Yii::getPathOfAlias("root"), "index.php");
        $bs = $bootstrapper ? $bootstrapper : $this->getBootstrapper();
        $_SERVER["SERVER_NAME"] = "127.0.0.1:8000";
        $_SERVER['SCRIPT_FILENAME'] = $newScriptName;
        $_SERVER['SCRIPT_NAME'] = "/index.php";
        \Yii::setApplication(null);
        $bs->createWebApplication();
        error_reporting(E_ALL);
    }

    /**
     * Отправляет POST запрос
     * 
     * @param String $route Роут контроллера
     * @param String[] $data Массив данных для $_POST
     * @return String HTML код ответа контроллера
     */
    public function postRequest($route, $data)
    {
        foreach ($data as $key => $value)
        {
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
            $request = new \CHttpRequest();
            \Yii::app()->setComponent("request", $request);
        }
        //bug::drop($data);
        $result = $this->forward($route);
        return $result;
    }

    /**
     * Создает данные для $_POST запроса для модели.
     * Эта функция необходимо, так как Yii отправляет формы как массивы данных, а не отдельными значениями
     * 
     * @param Sting[] $data Массив данных для $_POST
     * @param String $modelClass Имя класса модели
     * @return String[] Массив данных в стиле Yii
     * @throws \Exception
     */
    public function createRequestData($data, $modelClass)
    {
        if (!class_exists($modelClass))
        {
            throw new \Exception("Класс '$modelClass' не существует");
        }
        $newData = [$modelClass => $data];
        return $newData;
    }

}
