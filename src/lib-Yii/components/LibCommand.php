<?php

/**
 * Базовый класс для библиотечных комманд Lib-Yii
 *
 * @package Hs\Shell
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class LibCommand extends ConsoleCommand
{
    public function __construct($name, $runner)
    {
        parent::__construct($name, $runner);
    }

    /**
     * Создает подлючение к базе данных
     * 
     * @param String[] $dbConfig
     * @return DbConnection
     */
    public function openDbConnection($dbConfig)
    {
        $db = Yii::createComponent($dbConfig);
        $db->init();
        return $db;
    }

    /**
     * Получение конфигурации базы данных ['components']['db'] для проекта.
     * 
     * @see Bootstraper
     * @param String $mode Режим приложения 
     * @return String[]
     */
    public function getDbConfig($mode)
    {
        $config = EnvHelper::getConfig($mode);
        $result = $config['components']['db'];
        return $result;
    }

    /**
     * Reads input via the readline PHP extension if that's available, or fgets() if readline is not installed.
     *
     * @param string $message to echo out before waiting for user input
     * @param string $default the default string to be returned when user does not write anything.
     * Defaults to null, means that default string is disabled. This parameter is available since version 1.1.11.
     * @return mixed line read as a string, or false if input has been closed
     *
     * @since 1.1.9
     */
    public function prompt($message,$default=null)
    {
        if($default!==null)
            $message.=" [$default] ";
        else
            $message.=' ';


        echo $message;
        $input=fgets(STDIN);

        if($input===false)
            return false;
        else{
            $input=trim($input);
            return ($input==='' && $default!==null) ? $default : $input;
        }
    }
}
