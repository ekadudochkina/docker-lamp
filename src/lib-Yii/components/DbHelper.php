<?php
/**
 * Хелпер для операция с базой данных
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class DbHelper
{ 
    /**
     * Получение внешних ключей, которые объявлены в таблице. То есть внешние ключи для других таблиц.
     * 
     * @param String $tableName Имя таблицы
     * @param String $dbName Имя базы данных
     * @return ForeignKey[] Массив внешних ключей
     */
    public static function getForeignKeys($tableName,$dbName)
    {
        $cmd = static::getForeignKeySearchCommand();
        $where = "KCU.TABLE_NAME = :tableName";
        $params = [":tableName" => $tableName];
        if($dbName)
        {
            $where .= " and KCU.TABLE_SCHEMA = :dbName";
            $params[":dbName"] = $dbName;
        }
        $cmd->where($where,$params);
        $result = static::createForeignKeysFromCommand ($cmd);
        return $result;
    }
    
    /**
     * Получение внешних ключей, которые объявлены в поле.
     * 
     * @param String $tableName Имя таблицы
     * @param String $fieldName Имя поля
     * @param String $dbName Имя базы данных
     * @return ForeignKey Внешний ключ
     */
    public static function getForeignKey($tableName,$fieldName,$dbName = null)
    {
        $keys = static::getForeignKeys($tableName,$dbName);
        foreach($keys as $key)
            if($key->column == $fieldName)
                return $key;
        return null;
    }
    
    /**
     * Создание команды для поиска внешних ключей.
     * Алиас для таблицы внешних ключей - KCU
     * 
     * @return CDbCommand Объект команды Yii
     */
    protected static function getForeignKeySearchCommand()
    {
        $connection = static::getDatabaseConnection();
        $cmd = $connection->createCommand();
        $cmd->select("*");
        $cmd->from("INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU");
        $cmd->join("INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC", "KCU.`CONSTRAINT_NAME` = RC.`CONSTRAINT_NAME`");     
        return $cmd;
    }
    
    /**
     * Превращение результата команды в массив внешних ключей
     * 
     * @param CDbCommand $cmd Команда для поиска внешних ключей
     * @return ForeignKey[] Массив внешних ключей
     */
    protected static function createForeignKeysFromCommand($cmd)
    {
        $rows = $cmd->queryAll();
        $result = array();
        foreach($rows as $row)
        {
            $foreignKey = new ForeignKey();
            $foreignKey->name = $row["CONSTRAINT_NAME"];
            $foreignKey->database = $row['TABLE_SCHEMA'];
            $foreignKey->table = $row['TABLE_NAME'];
            $foreignKey->column = $row['COLUMN_NAME'];
            $foreignKey->referencedDatabase = $row['REFERENCED_TABLE_SCHEMA'];
            $foreignKey->referencedTable = $row['REFERENCED_TABLE_NAME'];
            $foreignKey->referencedColumn = $row['REFERENCED_COLUMN_NAME'];
            $foreignKey->update = $foreignKey->parseForeignKeyBehavior($row['UPDATE_RULE']);
            $foreignKey->delete = $foreignKey->parseForeignKeyBehavior($row['DELETE_RULE']);
            $result[] = $foreignKey;
        }
        return $result;
    }
    
    /**
     * Получение внешних ключей, которые объявлены для таблицы. То есть внешние ключи из других таблиц.
     * 
     * @param String $tableName Имя таблицы
     * @param String $fieldName Имя поля
     * @return ForeignKey[] Массив внешних ключей
     */
    public static function getOutterForeignKeys($tableName)
    {
        $cmd = static::getForeignKeySearchCommand();
        $cmd->where("KCU.REFERENCED_TABLE_NAME = :tableName",array(":tableName" => $tableName));
        if($fieldName)
            $cmd->where("KCU.REFERENCED_COLUMN_NAME = :fieldName",array(":fieldName" => $fieldName));
       
        $result = static::createForeignKeysFromCommand ($cmd);
        return $result;
    }
    
    /**
     * Получение имени текущей базы данных
     * 
     * @return String Имя базы данных
     */
    public static function getCurrentDatabaseName()
    {
        return EnvHelper::getDatabaseName();
    }
    
    /**
     * Получение подключение к базе данных
     * 
     * @return CDbConnection Подключение к базе данных
     */
    public static function getDatabaseConnection()
    {
        return Yii::app()->getDb();
    }
    
    /**
     * Создает или получает текущую транзакцию
     * 
     * @param CDbConnection $connection
     * @return CDbTransaction Транзакция
     */
    public static function generateTransaction(CDbConnection $connection)
    {
        $transaction = $db->getCurrentTransaction();
        $hasOuterTransaction = $transaction !== null;
        if(!$hasOuterTransaction)
            $transaction = $db->beginTransaction();
        
        
        return $transaction;
    }
    
    /**
     * Переводит значение в литерал для базы данных.
     * 
     * @param Mixed Значение в исходном типе данных
     * @return String Строка, которую можно использовать в качестве литерала
     */
    public static function valueToString($value)
    {
        $type = gettype($value);

        switch ($type)
        {
            case "boolean" : return $value ? "1" : "0";
                break;
            case "NULL" : return "null";
            case "string" : return "'$value'";
                break;
            default : return $value;
        }
    }
}
