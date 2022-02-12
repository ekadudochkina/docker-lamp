<?php

namespace Hs\Test;

/**
 * Базовый класс для тестов, прослойка
 *
 * @package Hs\Test
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * If true, DB will bre reloaded from scratch before the test
     * @var bool
     */
    protected $shouldResetDb = true;


    /**
     * Набор действий перед запуском теста
     */
    protected function setUp()
    {
        error_reporting(E_ALL);
        if (\EnvHelper::isProduction())
        {
            error_reporting(E_ALL);
            $this->fail("Обнаружен режим Production. Останавливаю тест во избежание потери данных.");
        }

        echo "\n";
        $this->log("Запуск " . $this->getName());
        if($this->shouldResetDb)
        {
            $this->resetDb();
        }
        \EnvHelper::resetNow();
        parent::setUp();
    }

    /**
     * Набор действий после запуска теста
     */
    protected function tearDown()
    {
        $this->log("Завершение " . $this->getName());
        \EnvHelper::resetNow();
        parent::tearDown();
    }

    /**
     * Логирование сообщения
     * 
     * @param String $msg
     */
    public function log($msg)
    {
        $msg = get_called_class() . ": " . $msg;
        echo "$msg \n";
    }

    /**
     * Очистка базы данных
     */
    public function resetDb()
    {
        //Очищаем базу данных
        $this->log("Очищаем БД");
        \Yii::import("root.lib-Yii.scripts.commands.*");
        $command = new \DbCommand("remigrate", "");
        $command->actionRemigrate([]);
        \Yii::app()->getDb()->getSchema()->refresh();
    }

}
