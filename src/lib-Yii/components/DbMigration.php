<?php

/**
 * Базовый класс для миграций в базе данных.
 * Миграции могут иметь любое имя, но в конце имени класса должен стоять номер задачи, если она имеется.
 * 
 * @see Migrator
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Db\Migrations
 */
abstract class DbMigration
{

    /**
     * Подключение к базе данных
     * 
     * @var CDbConnection 
     */
    protected $connection;

    /**
     * В этом методе должны быть код / запросы на изменение базы данных.
     * Он вызывается при применении миграции
     */
    public abstract function up();

    /**
     * Произведение миграции и откат в случае ошибки
     * @todo Откат не будет работать, потому что в MySQL DQL не транзакционный
     */
    public function execute()
    {
        $connection = Yii::app()->getDb();
        $this->connection = $connection;
        $transaction = $connection->beginTransaction();

        try
        {
            $this->up();
            $transaction->commit();
        } catch (Exception $ex)
        {

            $transaction->rollBack();
            throw $ex;
        }
    }

    /**
     * Откат миграции
     * @throws CException
     */
    public function rollback()
    {
        $connection = Yii::app()->getDb();
        $this->connection = $connection;
        $transaction = $connection->beginTransaction();

        try
        {
            $this->down();
            $transaction->commit();
        } catch (Exception $ex)
        {

            $transaction->rollBack();
            throw $ex;
        }
    }

    /**
     * Тут должен быть код для отката миграции
     */
    public abstract function down();

    /**
     * Получение номера миграции
     * @return Number Порядковый миграции
     */
    public abstract function getNumber();

    /**
     * Сохраняет модель или кидает исключение с пояснением, почему это не удалось
     * 
     * @param СActiveRecord $model Модель
     * @return Bool True, в случае успеха
     * @throws Exception
     */
    public function saveModel(ActiveRecord $model)
    {
        return MigrationHelper::saveModel($model);
    }

    /**
     * Выполняет миграцию для модели
     *
     * @param String $modelName Имя класса модели
     * @param bool $addForeignKeys Добавлять ли внешние ключи
     * @throws Exception
     */
    public function createMigrationForModel($modelName,$addForeignKeys = true)
    {
        return ActiveRecordHelper::executeMigrationForModel($modelName, $this->connection,$addForeignKeys);
    }

    /**
     * Пересоздает таблицу для модели
     * <b>Данный метод является опасным и не протестированным до конца </b>
     * @param ActiveRecord $model Модель
     * @throws Exception
     */
    public function recreateMigrationForModel(ActiveRecord $model)
    {
        $db = $this->connection;
        $db->schema->refresh();

        $db->createCommand("SET foreign_key_checks = 0")->execute();

        $tableName = $model->tableName();
        $schema = $db->getSchema();
        $tableNames = $schema->getTableNames();
        if (!in_array($tableName, $tableNames))
        {
            throw new Exception("Таблица '$tableName' не найдена для модели " . get_class($model) . ". Что-то не так.");
        }

        //Удаляем старую таблицу

        $cmd = $db->createCommand();
        $cmd->dropTable($tableName);

        //Создаем новую таблицу
        ActiveRecordHelper::executeMigrationForModel(get_class($model), $db);
        $db->createCommand("SET foreign_key_checks = 1")->execute();
        $model->refreshMetaData();
    }

    public function synchronizeTableForModel($modelname, $tableName = null)
    {
        return ActiveRecordHelper::synchronizeTableForModel($modelname, $tableName);
    }

    /**
     * Переименование поля модели
     *
     * @param string $modelname Нащвание модели
     * @param string $oldName Старое имя поля
     * @param string $newName Новое имя поля
     * @return bool True, если были произведены операции с базой данных, False, если в них небыло необходимости
     * @throws CDbException
     */
    public function renameFieldForModel($modelname,$oldName, $newName)
    {
        return ActiveRecordHelper::renameFieldForModel($modelname,$oldName, $newName);
    }

    protected function synchronizeOrCreateTablesForModel($modelname,$tableName = null)
    {
        $model = ActiveRecordHelper::createModelInstance($modelname);
        $table = $this->connection->getSchema()->getTable($model->tableName());
        if($table)
        {
            Yii::log("Has table, syncing");
            $this->synchronizeTableForModel($modelname,$tableName);
        }
        else {
            Yii::log("No table, creating");
            $this->createMigrationForModel($modelname);
        }
    }

}
