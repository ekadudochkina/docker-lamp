<?php

namespace Hs\Db;

/**
 * Mysqldump, который логирует информацию
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MysqlDumpWithLog extends \Ifsnop\Mysqldump\Mysqldump
{
    /**
     * Подключение к базе данных
     * @var \CDbConnection
     */
    protected $db;
    
    /**
     * Счетчик обработанных таблиц
     * @var Integer 
     */
    protected $counter = 0;
    
    /**
     * Общее количество таблиц
     * @var Integer
     */
    protected $tablesTotal = 0;

    /**
     * Constructor of Mysqldump. Note that in the case of an SQLite database
     * connection, the filename must be in the $db parameter.
     *
     * @param string $dsn        PDO DSN connection string
     * @param string $user       SQL account username
     * @param string $pass       SQL account password
     * @param array  $dumpSettings SQL database settings
     * @param array  $pdoSettings  PDO configured attributes
     */
    public function __construct(\CDbConnection $db, $dsn = '', $user = '', $pass = '', $dumpSettings = array(), $pdoSettings = array())
    {
        $this->db = $db;
        parent::__construct($dsn, $user, $pass, $dumpSettings, $pdoSettings);
    }

    /**
     * Main call
     *
     * @param string $filename  Name of file to write sql dump to
     * @return null
     */
    public function start($filename = '')
    {
        $this->counter = 0;
        $data = $this->db->createCommand("SHOW TABLES;")->queryColumn();
        $this->tablesTotal = count($data);
        return parent::start($filename);
    }

    /**
     * Table rows extractor, append information prior to dump
     *
     * @param string $tableName  Name of table to export
     *
     * @return null
     */
    public function prepareListValues($tableName)
    {
        $this->counter++;
        \Yii::log("{$this->counter}/{$this->tablesTotal}:  processing '$tableName'");
        return parent::prepareListValues($tableName);
    }

}
