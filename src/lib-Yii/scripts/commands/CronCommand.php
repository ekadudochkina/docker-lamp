<?php

/**
 * Команда для управления кроном
 *
 * @package Hs\Shell\Commands
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class CronCommand extends ConsoleCommand
{
    protected $args;
    
    /**
     * Запуск команды
     * @param String[] $args Аргументы
     */
    public function run($args)
    {
        $this->args = $args;
        return parent::run($args);
    }
    
    /**
     * Добавление команды в крон
     * Имеет 2 параметра: имя файла (в папке компонентов) и интервал
     */
    public function actionAdd()
    {
        $manager = new ConsoleParamManager($this->args);

        $pathToComponents = realpath(__DIR__ . "/../../../protected/components");

        $filename = $manager->getParamAt(1);
        $interval = $manager->getParamAt(2);

        if (!is_writable(__DIR__))
            die("Нельзя писать в директорию " . __DIR__);

        if (!$filename || !$interval)
            die("Необходимо указать имя файла и интервал запуска! \n");

        $filePath = $pathToComponents . "/" . $filename;

        if (!file_exists($filePath))
            die("Файл $filePath не существует!");

        $tempfile = __DIR__ . "/temp";
        if (file_exists($tempfile))
            unlink($tempfile);

        exec("crontab -l", $arr);
        $existingCmds = join("\n", $arr);
        file_put_contents($tempfile, $existingCmds);

        $cmd = "php " . $filePath;
        $cronTask = "\n*/$interval * * * * $cmd\n";
        file_put_contents($tempfile, $cronTask, FILE_APPEND);

        exec("crontab $tempfile");
        unlink($tempfile);
    }

}
