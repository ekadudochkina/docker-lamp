<?php

/**
 * Объект который запускает Yii.
 *
 * @todo Убрать дублирование кода при создании веб, консольных и тестовых приложений.
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
class Bootstraper
{
    /**
     * Количество секунд, которое мы спим, чтобы селениум запустился.
     * @var Int
     */
    protected $seleniumServerTimeout = 5;

    /**
     * Название папки проекта
     * @var String
     */
    protected $appFolder;

    /**
     * Путь к папке, где должен находиться сайт
     * @var String
     */
    protected $basePath;

    /**
     * Имя машины на которой запустили проект
     * @var String
     */
    protected $machine;

    /**
     * Текущий режим работы приложения (Production, Demo, Local..)
     * @var String
     */
    protected $currentMode = null;

    /**
     * Режим работы на боевом сервере
     */
    const MODE_PRODUCTION = "production";

    /**
     * Режим работы на демо стенде (сервера home-studio)
     */
    const MODE_DEMO = "demo";

    /**
     * Режим работы на локальной машине
     */
    const MODE_LOCAL = "local";

    /**
     * Режим работы на сборочном сервере (для выполнения тестов)
     */
    const MODE_TEST = "test";

    /**
     * Не использовать базу данных в приложении
     *
     * @var Bool
     */
    protected $disableDatabase = false;
    protected $inRelease = false;

    /**
     * @param Bool $debug Режим дебага (не работает в режиме production)
     * @param String $mode Режим работы (если не задано, то определится автоматически
     * @param String $appFolder Путь к директории проекта из главной папки
     */
    public function __construct($debug = null, $mode = null, $appFolder = "protected")
    {
        date_default_timezone_set("Europe/Moscow");
        if ($debug !== null && !defined("YII_DEBUG"))
            define("YII_DEBUG", $debug);
        $this->machine = $this->getMachineName();

        $this->currentMode = $this->detectCurrentMode($mode);

        $this->appFolder = $appFolder;
        $this->basePath = realpath(__DIR__ . "/..");
    }

    /**
     * Возвращает текущий режим приложения
     *
     * @return String Значение константы текущего режима приложения
     */
    public function getCurrentMode()
    {
        return $this->currentMode;
    }

    /**
     * Запуск Yii для тестов. PHPUnit встроенный в Yii не используется - это важно!
     *
     * Предполагается, что вызов этого метода будет сделан из bootstraper phpunit.phar
     * При отдельном вызове тесты не будут запущены
     */
    public function createTestApplication($selenium = true, $db = true)
    {
        //Эта часть на похожа на стандартный запуск, но если присмотреться есть отличия
        //Да и само приложение не запускается, так как от него нам нужны только классы

        $indexPath = $this->basePath . "/index.php";
        //die($indexPath);
        $_SERVER['SCRIPT_FILENAME'] = $indexPath;

        //Судя по всему, так как мы не используем тесты Yii, yiit нам не нужен
        $this->includeYii();

        $this->defineCustomYiiOptions();

        //Инфо
        echo "Machine: " . $this->machine . "\n";
        $confpath = $this->getConfigPath(self::MODE_TEST);
        echo "Config: $confpath \n";
        echo 'PHP: ' . phpversion() . "\n";


        //На тестовом сервере, вам базу никто не блюдичке не принесет, поэтому нужно, чтобы Yii не упал на этом
        $config = $this->getConfig();

        //Добавляем логирование
        $log = &$config['components']['log'];
        $log['routes'][] = $this->generateConsoleLogRouteConfig();

        //Запускаем сервер селениум и заполняем БД
        if ($selenium) {
            $this->startSeleniumServer();
        }
        if ($db) {
            $this->createDB($config, true);
        }
        //Создаем приложение, чтобы получить доступ к плюшкам Yii
        Yii::createConsoleApplication($config);
        EnvHelper::setBootstrapper($this);
        EnvHelper::enableHs();
        if ($db) {
            $migrator = new Migrator();
            $migrator->applyNewMigrations();
        }

    }

    /**
     * Запуск Yii для работы вебсайта
     *
     * @return CApplication
     */
    public function createWebApplication()
    {
        //Свои настройки, которые надо сделать до Yii
        $this->alterErrorHandling();
        $this->defineDebug();

        //Подключаем Yii
        $this->includeYii();

        $this->defineCustomYiiOptions();

        //Запускаемся
        $config = $this->getConfig();
        if ($this->isLocal()) {
            $this->createDB($config);
        }

        $app = $this->generateYiiWebApplication($config);

        EnvHelper::setBootstrapper($this);
        EnvHelper::enableHs();
        return $app;
    }

    /**
     * Создает консольное приложение
     *
     * @param String[] $cmdDirs Пути к папке с командами
     * @return CConsoleApplication
     */
    public function createConsoleApplication($cmdDirs = [])
    {
        //Большая часть кода взята из /framework/yiic.php
        if (!is_array($cmdDirs)) {
            throw new Exception("\$cmdDirs should be an array ");
        }

        //Свои настройки, которые надо сделать до Yii
        $this->alterErrorHandling();
        $this->defineDebug();

        // fix for fcgi
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

        //Подключаем Yii
        $this->includeYii();
        $this->defineCustomYiiOptions();

        //Запускаемся
        $config = $this->getConfig();
        if ($this->isLocal()) {
            $this->createDB($config);
        }


        //Добавляем логирование
        $log = &$config['components']['log'];
        $log['routes'][] = $this->generateConsoleLogRouteConfig();

        $path = realpath(__DIR__ . "/..");
        Yii::setPathOfAlias("root", $path);
        //echo Yii::getPathOfAlias("root");die();
        //Yii::import("ConsoleLogRoute");
        //print_r($config);die();
        $app = Yii::createConsoleApplication($config);

        //Это добавление дополнительных команд
        if ($cmdDirs) {
            foreach ($cmdDirs as $dir) {
                $app->commandRunner->addCommands($dir);
            }
        }
//        
//        $env = @getenv('YII_CONSOLE_COMMANDS');
//        if (!empty($env))
//            $app->commandRunner->addCommands($env);

        EnvHelper::setBootstrapper($this);
        EnvHelper::enableHs();
        Yii::getLogger()->autoDump = true;
        Yii::getLogger()->autoFlush = 1;
        $callable = array($this, "swallowExceptionForConsoleApplication");
        $app->onException->add($callable);

        if ($this->hasDatabase()) {
            $migrator = new Migrator();
            $migrator->applyNewMigrations();
        }

        return $app;
    }

    /**
     * Данный метод - функция обратного вызова, которая нужна для того, чтобы в консоле ошибки не выводились дважды.
     * Отображение ошибок не нужно, так как ConsoleLogRoute все выведет на экран.
     *
     * @param CExceptionEvent $ex Исключение
     */
    public function swallowExceptionForConsoleApplication($ex)
    {
        $ex->handled = true;
    }


    /**
     * Создание базы данных для проекта
     * @param String $arr Конфигурация проекта
     * @param Boolean $drop Удалять ли БД перед созданием новой
     */
    protected function createDB($arr, $drop = false)
    {
        if ($this->disableDatabase)
            return;


        $db = $arr['components']['db'];

        //Данный функционал нужен только для приложений использующих MySQl в
        //в качестве сервера базы данных
        if ($db['type'] !== "mysql")
            return;

        $projectName = $arr['name'];
        $connection = mysqli_connect($db['host'], $db['username'], $db['password']);
        if ($drop) {
            $sql = "DROP database `$projectName`";
            mysqli_query($connection, $sql);
        }
        $result = mysqli_query($connection, "SHOW DATABASES;");
        $databases = array();
        while ($row = mysqli_fetch_array($result)) {
            $databases[] = $row[0];
        }
        if (!in_array($projectName, $databases)) {
            $sql = "CREATE DATABASE `$projectName` DEFAULT CHARACTER SET utf8 ";
            mysqli_query($connection, $sql);
        }
    }

    /**
     * Определение переменной Debug. Планируется вынести ее в игнорируемую папку
     */
    public function defineDebug()
    {
        if (!defined("YII_DEBUG")) {
            if ($this->isProduction() || $this->isDemo())
                $value = false;
            else
                $value = true;

            define('YII_DEBUG', $value);
        }

        //В дебаге видим все ошибки, без него нет
        if (YII_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }

    }

    /**
     * Тут определяются настройки, которые специфичные именно для данного проекта
     */
    public function defineCustomYiiOptions()
    {
        CHtml::$errorContainerTag = 'span';
        // specify how many levels of call stack should be shown in each log message
        defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
    }

    /**
     * Заменяем обработку варнингов на эксепшены
     * @todo У меня складывается такое впечатление, что не нужно изменять обработку ошибок - все и так будет работать
     */
    public function alterErrorHandling()
    {
        //Отключаем обработку ошибок (варнингов) Yii, так как так невозможно отловить нотис или варнинг пхп
        //При этом нас всегда отправляет в errorController или errorHandler, а текущий контроллер умирает
        defined('YII_ENABLE_ERROR_HANDLER') || define('YII_ENABLE_ERROR_HANDLER', false);

        //Но при этом пусть обрабатывает эксепшены сам. Таким образом получается, что мы превращаем нотисы и варнинги в эксепшены и можем их обработать
        //но если мы не хотим(не подумали), то этим займется Yii
        //define('YII_ENABLE_EXCEPTION_HANDLER',false);
        set_error_handler(array($this, 'errorToException'));
        register_shutdown_function(array($this, 'fatalErrorToException'));
    }

    /**
     * Обработка критической ошибки
     */
    public function fatalErrorToException()
    {

        $error = error_get_last();

        if (is_array($error) != FALSE) {
            if (isset($error['type']) != FALSE) {
                if ($error['type'] == 1) {

                    $exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);

                    //$this->errorToException(0, $error['message'], $error['file'], $error['line']);
                    $app = Yii::app();
                    if ($app)
                        $app->handleException($exception);

                }
            }
        }
    }

    /**
     * Вспомагательная функция обработки варнингов.
     * @param Number $number Номер ошибки
     * @param String $message Сообщение об ошибке
     * @param String $filename Имя файла
     * @param String $lineno Номер строки
     * @throws ErrorException
     */
    public function errorToException($number, $message, $filename, $lineno)
    {
        if (@class_exists("Debug"))
            Debug::safeBreak();

        //var_dump(error_reporting());die();
        if (error_reporting() == 0) {
            return;
        }
        if (error_reporting() & $number) {
            throw new ErrorException($message, 0, $number, $filename, $lineno);
        }
    }

    /**
     * Получение конфигурационного файла, в зависимоти от режима.
     * @return String Путь к файлу
     */
    public function getConfigPath($mode)
    {
        $path = $this->basePath . "/" . $this->appFolder . "/config/" . strtolower($mode) . ".php";
        return $path;
    }

    /**
     * Запущен ли проект на локальной машине (или на неопознаной)
     * @return Bool
     */
    public function isLocal()
    {
        if ($this->currentMode)
            return $this->currentMode == self::MODE_LOCAL;

        return true;

        $alekseyMac = strpos($this->machine, "Macbook") !== false;
        $dimaUnix = strpos($this->machine, "dimasik") !== false;
        $wind = $this->isWindows();
        $result = $alekseyMac || $dimaUnix || $wind;
        return $result;
    }

    /**
     * Запущен ли проект на сборочном сервере
     * @return Bool
     */
    public function isTest()
    {
        if ($this->currentMode)
            return $this->currentMode == self::MODE_TEST;

        $result = strpos($this->machine, "Windows Server") !== false;
        return $result;
    }

    /**
     * Запущее ли проект на домашнем сервере home-studio.tk
     * @return Bool
     */
    public function isDemo()
    {
        if ($this->currentMode)
            return $this->currentMode == self::MODE_DEMO;

        $result = strpos($this->machine, "home-studio") !== false;
        return $result;
    }

    /**
     * Запущен ли проект на реальном сервере
     * @return Bool
     */
    public function isProduction()
    {
        if ($this->currentMode)
            return $this->currentMode == self::MODE_PRODUCTION;

        $str = "hs-production.pro";
        $result = strpos($this->machine, $str) !== false;
        return $result;
    }

    /**
     * Запущен ли проект под Windows
     * @return Bool
     */
    public function isWindows()
    {
        $result = strpos($this->machine, "Windows") !== false;
        return $result;
    }

    /**
     * Запуск сервера selenium
     * @todo Добавить еще и предварительное выключение, через http://192.168.0.20:4444/selenium-server/driver/?cmd=shutDownSeleniumServer
     */
    protected function startSeleniumServer()
    {
        //Определяем пути
        $javapath = "java";
        //-role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port 4444
        $seleniumserverpath = $this->basePath . "/" . $this->appFolder . "/tests/selenium-server-standalone-2.45.0.jar -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port 4444";
        echo "Path to java: $javapath \n";
        echo "Path to selenium server: $seleniumserverpath \n";

        $cmd = "$javapath -jar $seleniumserverpath";
        echo "Running cmd in background: $cmd \n";

        //Запускаем сервер
        $this->execInBackground($cmd);
        //Поспим чуть чуть, чтобы дать серверу оклиматься
        echo "Selenium server started.";
        sleep($this->seleniumServerTimeout);
    }

    /**
     * Создание и заполнение базы даных при первом запуске
     */
    public function generateDb()
    {
        //Вообщем-то весь секрет в том, что тесты и так генерируют годную базу данных
        $this->createTestApplication();
    }

    /**
     * Исполняет команду в фоновом режиме, как на Windows, так и под Unix.
     * @param type $cmd
     */
    public function execInBackground($cmd)
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $cmd, "r"));
        } else {
            exec($cmd . " > /dev/null &");
        }
    }

    /**
     * Определение режима работы приложения
     *
     * @param String $desiredMode Желаемый режим работы
     * @return String Константа, обозначающая текущий режим
     */
    protected function detectCurrentMode($desiredMode = null)
    {
        $modes = $this->getAllModes();
        if (!$this->inRelease && in_array($desiredMode, $modes, true))
            return $desiredMode;

        if ($this->isProduction())
            $mode = self::MODE_PRODUCTION;
        elseif ($this->isDemo())
            $mode = self::MODE_DEMO;
        elseif ($this->isTest())
            $mode = self::MODE_TEST;
        else
            $mode = self::MODE_LOCAL;

        return $mode;
    }

    /**
     * Исполняет задачу по крону
     *
     * @param String $className Имя класса задачи <b>Задача должна реализовывать интерфейс ICronTask</b>
     */
    public function executeCronTask($className)
    {
        //Потому что Yii назначает корень откуда был запущен первый скрипт, а мы не в корне сейчас
        $index = dirname(__DIR__) . "/index.php";
        $_SERVER['SCRIPT_FILENAME'] = $index;
        //Создаем приложение, чтобы загрузить классы Yii,
        $this->createWebApplication(true);

        $task = new $className();
        if (!$task instanceof ICronTask)
            throw new Exception("Класс '$className' должен реализовывать интерфейс ICronTask.");

        $task->execute();
    }

    /**
     * Отключает базу данных в приложении
     */
    public function disableDatabase()
    {
        $this->disableDatabase = true;
    }

    /**
     * Проверяет использует ли приложение базу данных
     *
     * @return Bool True, если
     */
    public function hasDatabase()
    {
        return !$this->disableDatabase;
    }

    /**
     * Получение массива конфигурации приложения
     *
     * @return Mixed[]
     */
    public function getConfig($mode = null)
    {
        if (!$mode) {
            $mode = $this->currentMode;
        }

        //получаем шаблон конфигруации
        $config = $this->getDefaultConfig();

        //Дополняем шаблон текущей конфигурацией режима
        //production.php будет использовать переменную $config
        $path = $this->getConfigPath($mode);
        $new = include($path);

        //Доплняем конфигурацию в зависимости от текущего режима (всякие логи)
        $result = $this->extendConfig($new);

        //Проверяем все ли на месте
        $this->checkConfig($result);

        return $result;
    }

    /**
     * Получение шаблона конфигурации по-умолчанию
     *
     * @return Mixed[] массив конфигурации
     */
    public function getDefaultConfig()
    {
        $path = __DIR__ . "/templates/config/production.php";
        $conf = include($path);
        return $conf;
    }

    /**
     * Проверяет конфигурацию на предмет ошибок
     *
     * @param Mixed[] $config Массив конфигурации
     * @throws Exception
     */
    public function checkConfig($config)
    {
        $defaultProjectName = "Yii-template";
        if ($config['name'] == $defaultProjectName) {
            throw new Exception("Проект имеет имя по-умолчанию. Переименуйте проект в файле /config/production.php.");
        }

        //Провреяем поля БД
        if (!$this->hasDatabase()) {
            return;
        }

        $db = $config['components']['db'];
        $dbkeys = ['dbname' => 1, "username" => 1, "password" => 1, "host" => 1, "type" => 1, "connectionString" => 0];

        foreach ($dbkeys as $key => $shouldExist) {
            $existing = isset($db[$key]);

            if ($shouldExist && !$existing) {
                throw new Exception("В настройках базы данных нет ключа '$key', обратитесь к архитектору за пояснениями.");
            }

            if ($existing && !$shouldExist) {
                throw new Exception("В настройках БД ключ '$key' устарел, обратитесь к руководителю.");
            }

        }

    }

    /**
     * Дополняет конфигурацию различными классами
     *
     * @param Mixed[] $config Массив конфигураций
     * @return Mixed[] Дополненная конфигурация
     */
    protected function extendConfig($config)
    {
        //Ну пока дополнять нечем
        return $config;
    }

    /**
     * Получает полное имя сервера на котором запущено приложение.
     * Примерно тоже, что возвращает uname -a под UNIX.
     *
     * @return Sting
     */
    protected function getMachineName()
    {
        $name = php_uname();
        return $name;
    }

    /**
     * Подключение фреймворка Yii
     */
    public function includeYii()
    {
        $yii = $this->basePath . '/lib-Yii/framework/yii.php';
        require_once($yii);
    }

    /**
     * Создание приложения Yii.
     *
     * @param Mixed[] Конфигурация Yii
     * @return CAplication
     */
    protected function generateYiiWebApplication($config)
    {
        $app = Yii::createWebApplication($config);
        return $app;

    }

    /**
     * Устанавливает режим релиза. В данном режиме блокируется ручное изменение режимов.
     */
    public function inRelease()
    {
        $this->inRelease = true;
        $this->currentMode = null;
        $this->currentMode = $this->detectCurrentMode();
    }

    protected function generateConsoleLogRouteConfig()
    {
        $arr = array(
            'class' => 'ConsoleLogRoute',
            'levels' => 'error, warning, info',
            'enabled' => true
        );
        return $arr;
    }

    /**
     * Возвращает все доступные режимы приложения
     *
     * @return String[]
     */
    public function getAllModes()
    {
        return array_merge($this->getLocalModes(),$this->getDemoModes(),$this->getTestModes(),$this->getProductionModes());
    }

    /**
     * Возвращает все доступные режимы приложения
     *
     * @return String[]
     */
    public function getProductionModes()
    {
        return [self::MODE_PRODUCTION];
    }

    /**
     * Возвращает все доступные демо режимы приложения
     *
     * @return String[]
     */
    public function getDemoModes()
    {
        return [self::MODE_DEMO];
    }

    /**
     * Возвращает все доступные тестовые режимы приложения
     *
     * @return String[]
     */
    public function getTestModes()
    {
        return [self::MODE_TEST];
    }
    /**
     * Возвращает все доступные локальные режимы приложения
     *
     * @return String[]
     */
    public function getLocalModes()
    {
        return [self::MODE_LOCAL];
    }

}
