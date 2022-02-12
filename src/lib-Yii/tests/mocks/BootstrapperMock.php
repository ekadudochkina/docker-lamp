<?php

/**
 * Объект бутстраппера, некоторые части, которого можно подменять
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class BootstrapperMock extends Bootstraper
{

    /**
     * Имя компьюетра
     * @var String
     */
    public static $machineName;
    public $config = null;
    public $createApp = false;

    /**
     * Получает полное имя сервера на котором запущено приложение.
     * Примерно тоже, что возвращает uname -a под UNIX.
     * 
     * @return Sting
     */
    protected function getMachineName()
    {
        if (static::$machineName)
        {
            return static::$machineName;
        }

        return parent::getMachineName();
    }

    /**
     * Подключение фреймворка Yii
     */
    public function includeYii()
    {
        if (class_exists("Yii"))
        {
            return;
        }

        parent::includeYii();
    }

    /**
     * Тут определяются настройки, которые специфичные именно для данного проекта
     */
    public function defineCustomYiiOptions()
    {
        if (defined("YII_TRACE_LEVEL"))
            return;
        return parent::defineCustomYiiOptions();
    }

    /**
     * Заменяем обработку варнингов на эксепшены
     */
    public function alterErrorHandling()
    {
        if (defined("YII_ENABLE_ERROR_HANDLER"))
            return;
        return parent::alterErrorHandling();
    }

    /**
     * Создание приложения Yii.
     * 
     * @param Mixed[] Конфигурация Yii
     * @return CAplication
     */
    protected function generateYiiWebApplication($config)
    {
        if ($this->createApp)
        {
            return parent::generateYiiWebApplication($config);
        }

        return Yii::app();
    }

    /**
     * Получение массива конфигурации приложения
     *
     * @param String $mode
     * @return Mixed[]
     */
    public function getConfig($mode = null)
    {
        return $this->config ? $this->config : parent::getConfig($mode);
    }

}
