<?php

/**
 * Небольшой хелпер для дебага
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
class Debug
{
    /**
     * Какой тип вывода использовать.
     * Принтр плохо показывает пустые значения, но зато не обрезает строки.
     * var_dump выводит информацию о классах
     * var_export позволяет быстро перенести вывод в код, но он не умеет работать с циклическими ссылками
     * 
     * @var Bool 
     */
    protected static $outputType = 1;
    
    /**
     * Использоватать STDERR
     * @var Bool 
     */
    protected static $useStderr = false;
    
    /**
     * Счетчики для дебага бесконечных циклов
     * 
     * @var String[]
     */
    protected static $counters = [];
    
    const TYPE_VAR_DUMP = 1;
    const TYPE_PRINTR = 2;
    const TYPE_VAR_EXPORT = 3;
    /**
     * Выводит значение на экран используя var_dump.
     */
    public static function dump()
    {
        if(!static::$useStderr)
            static::write("<pre>");
        $args = func_get_args();
        foreach($args as $arg)
        {
            static::outputVariable($arg);
            static::write("\n");
        }
        if(!static::$useStderr)
            static::write("</pre>");
    }
    
    /**
     * Выводит значения на экран, используя print_r.
     */
    public static function show()
    {
        $prevType = static::$outputType;
        static::$outputType = static::TYPE_PRINTR;
        $callable = array("Debug","dump");
        call_user_func_array($callable, func_get_args());
        static::$outputType = $prevType;
    }

    /**
     * Выводит значение с помощью функции var_export.
     * Вывод данный функции стилизован под код PHP.
     */
    public static function export()
    {
        $prevType = static::$outputType;
        static::$outputType = static::TYPE_VAR_EXPORT;
        $callable = array("Debug","dump");
        call_user_func_array($callable, func_get_args());
        static::$outputType = $prevType;
    }
    
    /**
     * Выводит значения на экран используя var_dump и останаваливает приложение
     * @param Mixed $val  Значение
     */
    public static function drop()
    {
        $args = func_get_args();
        foreach($args as $arg)
            static::dump($arg);
        
        static::stop();
    }
    
    /**
     * Выводит значения на экран при помощи print_r и останаваливает приложение
     * @param Mixed $val  Значение
     */
    public static function reveal()
    {
        $args = func_get_args();
        foreach($args as $arg)
            static::show($arg);
        
        static::stop();
    }
    
    /**
     * Останавливает приложение правильно
     * Использовать вместо die()
     */
    public static function stop()
    {
        Yii::app()->end();
        die();
    }

    /**
     * Выводит на экран переменную. Необходимо для перегрузки способа вывода.
     * @param Mixed $value Что выводить
     */
    protected static function outputVariable($value)
    {
        $result = "";
        if(static::$outputType == static::TYPE_PRINTR)
        {
            $result = print_r($value,1);
        }
        elseif(static::$outputType == static::TYPE_VAR_EXPORT)
        {
            $result = var_export($value,1);
        }
        else
        {
            ob_start();
            var_dump($value);
            $result = ob_get_clean();
        }
        static::write($result);
    }
    
    
    /**
     * Делает безопасную точку останова. Не выдает ошибки, если нет Xdebug.
     */
    public static function safeBreak()
    {
	$enabled = defined("XDEBUG_TRACE_HTML");
	if(YII_DEBUG && $enabled)
	    xdebug_break();
    }
    
    /**
     * Выводит html на экран.
     * 
     */
    public static function html()
    {
        $args = func_get_args();
        foreach($args as $arg)
            static::show(htmlspecialchars($arg));
    }
    
    /**
     * Использовать канал ошибок для вывода сообщений. 
     * Они не будут отображаться в верстке, но отобразятся в выводе Netbeans.
     */
    public static function useStderr()
    {
        static::$useStderr = true;
    }
    
    /**
     * Использовать стандартный канал для вывода сообщений
     * Они отобразяться в верстке
     */
    public static function useStdout()
    {
        static::$useStderr = false;
    }

    /**
     * Выводит текст
     * 
     * @param String $text
     */
    protected static function write($text)
    {
        if(static::$useStderr)
        {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, $text);
            fclose($stderr);
        }
        else {
            echo $text;
        }
    }

    /**
     * Отключает функцию лога Yii, отображаемого в верстке.
     * Полезно для консольных приложений, где его сложно прочитать,
     * а также для контроллеров возврщающих JSON. Так как лог не даст прочесть ответ со стороны браузера.
     */
    public static function disableWebLog()
    {
        foreach (Yii::app()->log->routes as $route)
	{
		if ($route instanceof CWebLogRoute)
		{
			$route->enabled = false;
		}
	}
    }

    /**
     * Выводит на экран информацию ограниченное количество раз, затем останавливает приложение.
     * Удобно для дебага бесконечных циклов
     * 
     * @param Mixed $value Значение
     * @param Integer $timer Количество выводов на экран до завершения приложения
     * @param String $namespace Имя счетчика. Необходимо заполнять, если нужно несколько счетчиков, работающих раздельно друг от друга
     */
    public static function dropOn($value,$timer,$namespace = "none")
    {
        if(!isset(static::$counters[$namespace]))
        {
            static::$counters[$namespace] = 0;
        }
        self::show($value);
        static::$counters[$namespace]++;
        if(static::$counters[$namespace] >= $timer)
        {
            self::stop();
        }
    }

    public static function enableProfilingInConsole()
    {
        foreach (Yii::app()->log->routes as $route)
        {
            if ($route instanceof ConsoleLogRoute)
            {
                $route->levels = 'error,info,profile';
            }
        }
    }

    public static function disableProfilingInConsole()
    {
        foreach (Yii::app()->log->routes as $route)
        {
            if ($route instanceof ConsoleLogRoute)
            {
                $route->levels = 'error,info';
            }
        }
    }

}