<?php
namespace Hs\Shell;

/**
 * Данный класс содержит функции для обработки параметров в командах Yii.
 * Лучшие практики, так сказать.
 *
 * @package Hs\Shell
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class YiiShellParamManager extends \ConsoleParamManager
{

    /**
     * Ссылка на консольную команду
     * 
     * @var \ConsoleCommand 
     */
    protected $consoleCommand;

    /**
     * 
     * @param String[] $arguments Аргументы команды
     * @param \ConsoleCommand $cmd Текущая команда
     */
    public function __construct($arguments,  \ConsoleCommand $cmd)
    {
        parent::__construct($arguments);
        $this->consoleCommand = $cmd;
    }

    /**
     * Получение параметра под номером.
     * Если параметр не получен, то приложение выходит с ошибкой
     * 
     * @param Integer $number Номер параметра (с нуля, но нулевым является путь файла)
     * @param String $error Текст ошибки
     * @return String Параметр или null
     */
    public function getParamAtOrDie($number, $error)
    {
        $param = $this->getParamAt($number);
        if($param === null)
        {
            $this->consoleCommand->log($error,  \CLogger::LEVEL_ERROR);
            \Yii::app()->end();
        }
        return $param;
    }
}
