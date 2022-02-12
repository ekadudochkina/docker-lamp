<?php
/**
 * Схема таблицы для модели. Используется для миграций.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Db\Migrations
 */
class MigrationColumnSchema extends CMysqlColumnSchema
{
    /**
     * Есть ли у колонки значение по-умолчанию
     * @var Bool 
     */
    public $hasDefaultValue = false;
    
    /**
     * Является ли поле составным уникальным индексом
     * По умолчанию нет.
     * @var Bool 
     */
    public $isCompositeIndex = false;
}
