<?php

/**
 * Драйвер для БД, обеспечивающий вложенные транзакции
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Db
 * @link http://www.yiiframework.com/wiki/38/how-to-use-nested-db-transactions-mysql-5-postgresql/ Источник
 */
class NestedPDO extends PDO
{

    // Database drivers that support SAVEPOINTs.
    protected static $savepointTransactions = array("pgsql", "mysql");
    // The current transaction level.
    protected $transLevel = 0;

    protected function nestable()
    {
        return in_array($this->getAttribute(PDO::ATTR_DRIVER_NAME), self::$savepointTransactions);
    }

    public function beginTransaction()
    {
        if ($this->transLevel == 0 || !$this->nestable())
        {
            parent::beginTransaction();
        } else
        {
            $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
        }

        $this->transLevel++;
    }

    public function commit()
    {
        $this->transLevel--;

        if ($this->transLevel == 0 || !$this->nestable())
        {
            parent::commit();
        } else
        {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
        }
    }

    public function rollBack()
    {
        $this->transLevel--;

        if ($this->transLevel == 0 || !$this->nestable())
        {
            parent::rollBack();
        } else
        {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
        }
    }

}
