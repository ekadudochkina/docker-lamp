<?php

/**
 * Запускает Unit тесты для классов библиотеки.
 * 
 * @package Hs\Shell\Commands
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class TestCommand extends ConsoleCommand
{
    const MODE_ALL = "all";
    const MODE_LIB = "lib";
    const MODE_PROJECT = "project";
    const TYPE_UNIT = "unit";
    const TYPE_FUNCTIONAL = "functional";
    const TYPE_ALL = "all";

    /**
     * Запуск команды
     * 
     * @param String[] $args Массив аргументов
     */
    public function run($args)
    {
        $mode = self::MODE_ALL;
        if (isset($args[0]))
        {
            switch ($args[0])
            {
                case self::MODE_LIB :
                    $mode = self::MODE_LIB;
                    break;
                case self::MODE_PROJECT :
                    $mode = self::MODE_PROJECT;
                    break;
                case self::MODE_ALL :
                    $mode = self::MODE_ALL;
                    break;
            }
        }
        $manager = new ConsoleParamManager($args);
        ;
        $type = self::TYPE_ALL;
        if (isset($args[1]))
        {
            switch ($args[1])
            {
                case self::TYPE_FUNCTIONAL : $type = self::TYPE_FUNCTIONAL;
                    break;
                case self::TYPE_UNIT : $type = self::TYPE_UNIT;
                    break;
                case self::TYPE_ALL : $type = self::TYPE_ALL;
                    break;
            }
        }

        $coverage = $manager->hasFlag("--coverage-html") ? "--coverage-html " . Yii::getPathOfAlias("root") . "/report" : "";
        $coverage = $manager->hasFlag("--coverage") ? "--coverage-text" : $coverage;
        $filter = $manager->hasFlag("--filter") ? "--filter " . $manager->getParam("--filter") : "";
        $isolation = $manager->hasFlag("--fast") ? "" : "--process-isolation";

        $upperDir = __DIR__ . "/../../";
        $baseDir = realpath($upperDir);


        $suits = "--testsuite " . $mode . "_" . $type;

        $configPath = "$baseDir/tests/phpunit.xml";

        //Даем возможность перегрузить тесты
        $overridePath = Yii::getPathOfAlias("application.tests")."/phpunit.xml";
        if(file_exists($overridePath))
        {
            $configPath = $overridePath;
        }


        $cmd = PHP_BINARY . " $baseDir/utils/phpunit/phpunit-5.7.19.phar $isolation $coverage --verbose $filter --configuration $configPath $suits";
        //Это необходимо потому что чей-то автолодер использует working directory для подгрузки файлов
        //И если вызвать данный скрипт не весть от куда он будет подгружать
        //PHPUnit из папки Yii вместо  того, чтобы использовать тот, что лежит в папке protected/tests.
        $phpUnitPath = "$baseDir/utils/phpunit";
        //echo $phpUnitPath."\n";
        chdir($phpUnitPath);
        echo "Cmd: " . $cmd . "\n";
        passthru($cmd);
    }

}
