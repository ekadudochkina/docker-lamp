<?php
/**
 * Объект содержащий данные первичного ключа.
 *
 * @see CodeGenHelper
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Db
 */
class ForeignKey
{
    /**
     * Запретить удаление / обновление внешней запиcи
     */
    const RESTRICT = "RESTRICT";
    
    /**
     * При удалении / обновлении внешней запиcи выставить поле в NULL
     */
    const SET_NULL = "SET NULL";
    
    /**
     *  При удалении / обновлении внешней запиcи удалить / обновить текущую запись
     */
    const CASCADE = "CASCADE";
    
    /**
     * Поведение при обновлении внешней записи по умолчанию.
     * Каскадно обновить значение внешнего ключа.
     */
    const DEFAULT_UPDATE = self::CASCADE;
    
    /**
     * Поведение при удалении внешней записи по умолчанию.
     * Выставить поле в NULL
     */
    const DEFAULT_DELETE = self::SET_NULL;
    
    /**
     * Название таблицы
     * @var String 
     */
    public $table;
    /**
     * Название базы данных
     * @var String 
     */
    public $database;
    /**
     * Название поля
     * @var String 
     */
    public $column;
    /**
     * Имя внешнего ключа
     * @var String 
     */
    public $name;
    /**
     * Внешняя база данных
     * @var String 
     */
    public $referencedDatabase;
    /**
     * Внешняя таблица
     * @var String 
     */
    public $referencedTable;
    /**
     * Внешнее поле
     * @var String 
     */
    public $referencedColumn;
    /**
     * Поведение во время обновления внешней записи
     * @var String 
     */
    public $update;
    /**
     * Поведение во время удаления внешней записи
     * @var String 
     */
    public $delete;
    
    /**
     * Парсит константу для ключа из строки
     * 
     * @param String $string Строка содержащая название поведения
     * @return String Контстанта поведения
     */
    public function parseForeignKeyBehavior($string)
    {
        $arr = array(self::CASCADE,self::RESTRICT,self::SET_NULL);
        $pos = array_search(trim($string),$arr);
        if($pos === false)
            throw new Exception("Не удалось определить поведение '$string'");
        
        $result = $arr[$pos];
        return $result;
    }
}
