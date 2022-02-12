<?php

namespace Hs\Db;

/**
 * PDO для мигратора, кидает исключения, если одновременно происходит вставка данных и создание теблиц
 * 
 * @see \Migrator
 * @package Hs\Db
 * @see MigratorDbConnection
 */
class MigratorPDO extends \NestedPDO
{

    protected $tableStatements = ["ALTER", "CREATE"];
    protected $dataStatements = ["INSERT", "UPDATE", "DELETE"];
    protected static $updatedData = false;
    protected static $updatedTables = false;
    protected static $failed = false;

    /**
     * Проверка запроса на наличие вставки данных и изменения таблиц
     * 
     * @param String $statement
     * @throws \Exception
     */
    public function checkQuery($statement)
    {
        //\bug::dump($statement,static::$updatedTables,static::$updatedData);
        if (!static::$updatedTables)
        {
            foreach ($this->tableStatements as $part)
            {
                if (strpos($statement, $part) === 0)
                {
                    //\bug::show("updatedTables"); 
                    static::$updatedTables = true;
                    break;
                }
            }
        }

        if (!static::$updatedData)
        {
            foreach ($this->dataStatements as $part)
            {
                if (strpos($statement, $part) === 0)
                {
                    //\bug::show("updatedData"); 
                    static::$updatedData = true;
                    break;
                }
            }
        }
        //\bug::dump($statement,static::$updatedTables,static::$updatedData);

        if (static::$updatedData && static::$updatedTables)
        {

            //\bug::show("Exception");
            static::$failed = true;
            throw new \Exception("Нельзя создавать таблицы и данные в одной миграции");
        }
    }

    /**
     * Очищает счетчики типов запросов
     */
    public function resetCounters()
    {
        // \bug::show("reset");
        static::$updatedData = false;
        static::$updatedTables = false;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @link http://php.net/manual/en/pdo.prepare.php
     * @param string $statement <p>
     * This must be a valid SQL statement template for the target database server.
     * </p>
     * @param array $driver_options [optional] <p>
     * This array holds one or more key=&gt;value pairs to set
     * attribute values for the PDOStatement object that this method
     * returns. You would most commonly use this to set the
     * PDO::ATTR_CURSOR value to
     * PDO::CURSOR_SCROLL to request a scrollable cursor.
     * Some drivers have driver specific options that may be set at
     * prepare-time.
     * </p>
     * @return PDOStatement If the database server successfully prepares the statement,
     * <b>PDO::prepare</b> returns a
     * <b>PDOStatement</b> object.
     * If the database server cannot successfully prepare the statement,
     * <b>PDO::prepare</b> returns <b>FALSE</b> or emits
     * <b>PDOException</b> (depending on error handling).
     * </p>
     * <p>
     * Emulated prepared statements does not communicate with the database server
     * so <b>PDO::prepare</b> does not check the statement.
     */
    public function prepare($statement, $driver_options = array())
    {
        $this->checkQuery($statement);
        return parent::prepare($statement, $driver_options);
    }

}
