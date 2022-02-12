<?php
/**
 * Схема таблицы для модели. 
 * Используется для автоматических миграций для моделей.
 * Мне понадобился наследник, так как нет комментариев к таблицам.
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Db\Migrations
 */
class MigrationTableSchema extends CMysqlTableSchema
{
    /**
     * Комментарий к таблице
     * @var String  
     */
    public $comment;
    
    /**
     * Добавление колонки в схему
     * 
     * @param CMysqlColumnSchema $schema
     */
    public function addColumn(CMysqlColumnSchema $schema){
	$name = $schema->name;
	if(!$name)
	    throw new Exception("Can't add schema without name");	
	$this->columns[$name] = $schema;
	if($schema->isPrimaryKey)
	    $this->primaryKey = $schema->name;
    }
    
    /**
     * Добавление внешнего ключа в схему
     * 
     * @see ForeignKey
     * @param String $ownColumn Название поля, которое является внешник ключом в данной схеме
     * @param String $foreignTable Название внешней таблицы
     * @param String $foreignColumn Название внешнего поля
     * @param String $update Правило поведения каскадности на Update
     * @param String $delete Правило поведения каскадности на Delete
     * @throws Exception
     */
    public function addForeignKey($ownColumn,$foreignTable,$foreignColumn,$update = null,$delete = null){
        if($update == null)
            $update = ForeignKey::DEFAULT_UPDATE;
        if($delete == null)
            $delete = ForeignKey::DEFAULT_DELETE;
	$schema = $this->getColumn($ownColumn);
	if(!$schema)
	    throw new Exception("Нет ячейки '$ownColumn' в схеме '{$this->name}'");
	$this->foreignKeys[$ownColumn] = array($foreignTable,$foreignColumn,$update,$delete,$schema->comment);
    }
}
