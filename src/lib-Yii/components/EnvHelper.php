<?php

/**
 *  Хелпер для работы с данными окружения.
 *  К ним относятся IP адреса, директории на сервере, заголовеи и так далее.
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class EnvHelper
{
    /**
     * Объект запускающий приложение
     * @var Bootstraper
     */
    protected static $bootstrapper;

    /**
     * Подключен ли автозагрузчик Home-Studio
     *
     * @var Bool
     */
    protected static $hsEnabled;

    /**
     * @var Текущее время (для юнит тестов)
     */
    protected static $now;


    /**
     * Назначение объекта приложения, который запустил приложение
     * @param Bootstraper $bs
     */
    public static function setBootstrapper(Bootstraper $bs)
    {
        self::$bootstrapper = $bs;
    }

    /**
     * Получение IP пользователя. Функцию можно использовать даже, если сервер находится под прокси.
     *
     * @param null|string $ip_param_name Ключ элемента _SERVER, в котором нужно искать IP адрес. Если не задано ищем по стандартным индексам.
     * @return string|null Ip адрес клиента или null
     */
    public static function getClientIp($ip_param_name = null, array $non_trusted_param_names = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'))
    {
        $ip = null;
        //используем нужную переменную, если задана
        if (!is_null($ip_param_name) && !empty($_SERVER[$ip_param_name]) && filter_var($_SERVER[$ip_param_name], FILTER_VALIDATE_IP))
        {
            // если переменная подошла как надо
            $ip = $_SERVER[$ip_param_name];
            return $ip;
        }

        //ищем переменную в стандартных записях
        foreach ($non_trusted_param_names as $ip_param_name_nt)
        {
            if ($ip_param_name === $ip_param_name_nt)
                // мы уже проверяли эту переменную
                continue;
            if (!empty($_SERVER[$ip_param_name_nt]) && filter_var($_SERVER[$ip_param_name_nt], FILTER_VALIDATE_IP))
            {
                // если переменная подошла как надо
                $ip = $_SERVER[$ip_param_name_nt];
                break;
            }
        }

        return $ip;
    }

    /**
     * Определяет запущен ли проект на локальной машине
     *
     * @param String $mode Режим приложения, если не передан, то используется текущий режим
     * @return Bool
     */
    public static function isLocal($mode = null)
    {
        $mode = $mode ? $mode : self::$bootstrapper->getCurrentMode();
        $localModes = array_merge(self::$bootstrapper->getLocalModes(),self::$bootstrapper->getTestModes());
        $result =  in_array($mode,$localModes);
        return $result;
    }

    /**
     * Определяет запущен ли проект в режиме тестирования.
     *
     * @param String $mode Режим приложения, если не передан, то используется текущий режим
     * @return Bool
     */
    public static function isTest($mode = null)
    {
        $mode = $mode ? $mode : self::$bootstrapper->getCurrentMode();
        $result =  in_array($mode,self::$bootstrapper->getTestModes());
        return $result;
    }

    /**
     * Определяет запущен ли проект в режиме релиза
     *
     * @param String $mode Режим приложения, если не передан, то используется текущий режим
     * @return Bool
     */
    public static function isProduction($mode = null)
    {
        $mode = $mode ? $mode : self::$bootstrapper->getCurrentMode();
        $result =  in_array($mode,self::$bootstrapper->getProductionModes());
        return $result;
    }

    /**
     * Определяет запущен ли проект на демо сервере
     *
     * @param String $mode Режим приложения, если не передан, то используется текущий режим
     * @return Bool
     */
    public static function isDemo($mode = null)
    {
        $mode = $mode ? $mode : self::$bootstrapper->getCurrentMode();
        $result = in_array($mode, self::$bootstrapper->getDemoModes());
        return $result;
    }

    /**
     * Возвращает все доступные режимы приложения
     *
     * @return String[]
     */
    public static function getAllModes()
    {
        return self::$bootstrapper->getAllModes();
    }

    /**
     * Определяет запущен ли проект под Windows.
     * @return Bool
     */
    public static function isWindows()
    {
        return self::$bootstrapper->isWindows();
    }

    /**
     * Определяет запущен ли проект под Mac.
     * @return Bool
     */
    public static function isMac()
    {
        $str = php_uname();
        $flag = strpos($str, "Darwin Kernel");
        return $flag;
    }

    /**
     * Получает режим в котором запущенно приложение
     * @return String Значение контанты режима приложения
     */
    public static function getCurrentMode()
    {
        return self::$bootstrapper->getCurrentMode();
    }

    /**
     * Генерация строки, например для пароля
     * @param Number $length Длина строки
     * @return String Строка из случайных цифр и букв
     */
    public static function generateString($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++)
        {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    /**
     * Исполняет команду в фоновом режиме, как на Windows, так и под Unix.
     * @param type $cmd
     */
    public static function execInBackground($cmd)
    {
        if (substr(php_uname(), 0, 7) == "Windows")
        {
            pclose(popen("start /B " . $cmd, "r"));
        } else
        {
            exec($cmd . " > /dev/null &");
        }
    }

    /**
     * Проверка запущен ли процесc. (Unix)
     * @param String $cmd Команда запустившая процесс
     * @return boolean True, если процесс запушен
     */
    public static function isProcessRunning($cmd)
    {
        exec("ps -aux",$arr);
        $ps = join(" ", $arr);
        $result = strpos($ps,$cmd) !== false;

        return $result;
    }

    /**
     * Получение идентификатора процесса по команде
     *
     * @param String $cmd Команда, запустившая процесс
     * @return Number Идентификатор процесса
     */
    public static function getProcessId($cmd){
        exec("ps -aux",$arr);
        foreach($arr as $line){
            if(strpos($line,$cmd) !== false)
            {
                $spaceDelimered = preg_replace('!\s+!', ' ', $line);
                $parts = explode(" ",$spaceDelimered);
                return $parts[1];
            }
        }
        return null;
    }

    /**
     * Останавливает процесс (Unix)
     *
     * @param Number $pid Идентификатор процесса
     */
    public static function stopProcess($pid){
        exec("kill -9 ".$pid);
    }

    /**
     * Превращает исключение в строку, которую далее можно отправить в лог или на почту
     *
     * @param Exception $ex Объект исключения
     * @return String Строка, содержащая данные исключения
     */
    public static function exceptionToString(Exception $ex){
        $str = $ex->getMessage();
        $str .="\n";
        $str .= $ex->getFile().": ".$ex->getLine();
        $str .="\n";
        $str .= $ex->getTraceAsString();
        return $str;
    }

    /**
     * Возвращает имя базы данных при стандартном использовании Yii
     *
     * @return String Имя базы данных
     */
    public static function getDatabaseName()
    {
        $curdb  = explode('=', Yii::app()->getDb()->connectionString);
        $schemaName = $curdb[2];
        return $schemaName;
    }

    /**
     * Узнает является ли текущая база данных сервером SQlite.
     */
    public static function isSQLite()
    {
        $db = Yii::app()->getDb();
        $connectionString = $db->connectionString;
        if(StringHelper::hasSubstring($connectionString,"sqlite",true))
            return true;
        return false;
    }

    /**
     * Узнает является ли текущая база данных сервером MySQL.
     */
    public static function isMysql()
    {
        $db = Yii::app()->getDb();
        $connectionString = $db->connectionString;
        if(StringHelper::hasSubstring($connectionString,"mysql",true))
            return true;
        return false;
    }

    /**
     * Подключает загрузку классов внешних библиотек.
     */
    public static function enableComposer()
    {
        $path =  Yii::getPathOfAlias("root")."/lib-Yii/vendor/autoload.php";
        //Debug::drop($path);
        spl_autoload_unregister(array('YiiBase','autoload'));

        require $path;
        spl_autoload_register(array('YiiBase','autoload'));
    }

    /**
     * Изспользует ли текущий проект базу данных
     *
     * @return Bool
     */
    public static function hasDatabase()
    {
        return self::$bootstrapper->hasDatabase();
    }

    /**
     * Подключает загрузку классов Home-Studio, содержащих неймспейсы
     */
    public static function enableHs()
    {
        if(static::$hsEnabled)
        {
            return;
        }

        static::$hsEnabled = true;
        $rawPath =  Yii::getPathOfAlias("application")."/../lib-Yii/autoload.php";
        $path = realpath($rawPath);
        //Debug::drop($path);
        spl_autoload_unregister(array('YiiBase','autoload'));

        require $path;
        spl_autoload_register(array('YiiBase','autoload'));
    }

    /**
     * Получение текущего уровня контроля ошибок PHP
     *
     * @return String
     */
    public static function getErrorReportingLevel()
    {
        $separator = ",";
        $intval = error_reporting();
        $errorlevels = array(
            E_ALL => 'E_ALL',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            E_DEPRECATED => 'E_DEPRECATED',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_STRICT => 'E_STRICT',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_NOTICE => 'E_NOTICE',
            E_PARSE => 'E_PARSE',
            E_WARNING => 'E_WARNING',
            E_ERROR => 'E_ERROR');
        $result = '';
        foreach ($errorlevels as $number => $name)
        {
            if (($intval & $number) == $number)
            {
                $result .= ($result != '' ? $separator : '') . $name;
            }
        }
        return $result;
    }

    public static function getConfig($mode)
    {
        $config = self::$bootstrapper->getConfig($mode);
        return $config;
    }

    public static function now()
    {
        $now = self::$now ? self::$now : time();
        return $now;
    }

    public static function resetNow()
    {
        self::$now = null;
    }

    public static function setNow($stamp)
    {
        self::$now = $stamp;
    }

}
