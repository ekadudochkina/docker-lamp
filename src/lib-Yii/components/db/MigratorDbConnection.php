<?php
namespace Hs\Db;

/**
 * Подючение к базе данных для мигратора. Возвращает специальный объект PDO, который позволяет лучше контролировать запросы
 * 
 * @see \Migrator
 * @package Hs\Db
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MigratorDbConnection extends \DbConnection
{
    public $pdoClass = "\Hs\Db\MigratorPDO";
    
    public function __construct(\DbConnection $connection)
    {
        parent::__construct("","","");
        $this->cloneFrom($connection);
        
    }
    
    /**
     * Сброс счетчиков типов запросов на объекте PDO
     */
    public function resetCounters()
    {
        $pdo = $this->getPdoInstance();
        $pdo->resetCounters();
    }
    
    /**
     * Проверяет завершилась ли опрация ошибкой
     * 
     * @return Bool True, если ошибок нет
     */
    public function isFailed()
    {
        $pdo = $this->getPdoInstance();
        $result = $pdo->isFailed();
        return $result;
    }
    
    /**
     * Клонирует данные для подключения из другого подключения к БД
     * 
     * @param \DbConnection $connection Подключеие к БД
     */
    protected function cloneFrom(\DbConnection $connection)
    {
        $this->host = $connection->host;
        $this->username = $connection->username;
        $this->password = $connection->password;
        $this->port  = $connection->port;
        $this->dbname = $connection->dbname;
        $this->type = $connection->type;
    }
}
