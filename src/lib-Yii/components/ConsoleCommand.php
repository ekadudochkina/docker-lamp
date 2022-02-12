<?php

/**
 * Базовый класс для консольных комманд. Прослойка.
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Yii
 */
class ConsoleCommand extends CConsoleCommand
{

    /**
     * Logs a message.
     * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
     * and may be recorded in different media, such as file, email, database, using
     * {@link CLogRouter}.
     * @param string $msg message to be logged
     * @param string $level level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
     */
    public function log($msg, $level = CLogger::LEVEL_INFO)
    {
        $newMsg = "cmd: " . $msg;
        Yii::log($newMsg, $level);
    }

    /**
     * Запрашивает пароль у пользователя. 
     * Скрывает символы в консоле и не позволяет занести их в историю командной строки.
     * 
     * @param String $prompt Сообщение, которое будет выведенно пользователю
     * @return String Пароль, который ввел пользователь
     */
    public function promptPassword($prompt)
    {
        if (preg_match('/^win/i', PHP_OS))
        {
            $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
            file_put_contents(
                    $vbscript, 'wscript.echo(InputBox("'
                    . addslashes($prompt)
                    . '", "", "password here"))');
            $command = "cscript //nologo " . escapeshellarg($vbscript);
            $password = rtrim(shell_exec($command));
            unlink($vbscript);
            return $password;
        } else
        {
            $command = "/usr/bin/env bash -c 'echo OK'";
            if (rtrim(shell_exec($command)) !== 'OK')
            {
                trigger_error("Can't invoke bash");
                return;
            }
            $command = "/usr/bin/env bash -c 'read -s -p \""
                    . addslashes($prompt)
                    . "\" mypassword && echo \$mypassword'";
            $password = rtrim(shell_exec($command));
            echo "\n";
            return $password;
        }
    }

}
