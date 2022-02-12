<?php

/**
 * Отправляет информацию логов в консоль. 
 * Необходим для консольных приложений, чтобы все общение с пользоватем вносилось в логи.
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Yii
 */
class ConsoleLogRoute extends CLogRoute
{

    /**
     * Processes log messages and sends them to specific destination.
     * Derived child classes must implement this method.
     * @param array $logs list of messages. Each array element represents one message
     * with the following structure:
     * array(
     *   [0] => message (string)
     *   [1] => level (string)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true));
     */
    protected function processLogs($logs)
    {
        $STDOUT = fopen("php://stdout", "w");
        foreach ($logs as $log)
        {
            $message = date("H:i:s") . " - ".$log[0];
            if(StringHelper::hasSubstring($message,"end:system.db.CDbCommand"))
            {
                continue;
            }
            $level = $log[1];
            
            $text = $this->colorize("$message \n", $level);
            fwrite($STDOUT, $text);
        }
        fclose($STDOUT);
    }

    /**
     * Формирование подкрашенной строки для консоли
     * 
     * @param String $text Текст сообщения
     * @param String $level Уровень сообщения (success,error,warning)
     * @return String Цветное сообщение
     */
    protected function colorize($text, $level = null)
    {
        $out = "";
        switch ($level)
        {
            case "success":
                $out = "[32m"; //Green background
                break;
            case "error":
                $out = "[31m"; //Red background
                break;
            case "warning":
                $out = "[33m"; //Yellow background
                break;
            case "profile":
                $out = "[36m"; //Blue background
                $text = "Time: " . time() . ", Memory: " . memory_get_usage() . ", " . $text;
                break;
            default:
                return $text;
        }
        return "\033$out" . "$text\033[0m";
    }

//put your code here
}
