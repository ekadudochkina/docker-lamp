<?php

/**
 * Прослойка между классами Yii.
 * Позволяет отказаться от использования connection string для базы данных, разбивая его на отдельные параметры.
 *
 * @package Hs\Yii
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class DbConnection extends CDbConnection
{

    /**
     * Адрес сервера баз данных
     * @var String
     */
    public $host = null;

    /**
     * Тип базы данных (mysql)
     * @var String
     */
    public $type = null;

    /**
     * Имя базы данных (необязательно)
     * @var String 
     */
    public $dbname = null;

    /**
     * Порт сервера баз данных
     * 
     * @var Number
     */
    public $port = null;

    /**
     * Initializes the application component.
     * This method overrides the parent implementation by establishing the database connection.
     */
    public function init()
    {
        $this->createConnectionString();
        //bug::drop($this->connectionString);
        parent::init();
    }

    /**
     * Формирует строку подключения из отдельных параметров
     * @throws Exception
     */
    protected function createConnectionString()
    {
        if (!$this->host)
            throw new Exception("Не указан параметр 'host' компонента Yii 'db'.");
        if (!$this->type)
            throw new Exception("Не указан параметр 'type' компонента Yii 'db'.");


        $connectionString = $this->type . ":host=" . $this->host . "";
        if ($this->dbname)
            $connectionString .= ";dbname={$this->dbname}";
        if ($this->port)
            $connectionString .= ";port={$this->port}";

        $this->connectionString = $connectionString;
    }

    public function getDbName()
    {
        return $this->dbname;
    }

}
