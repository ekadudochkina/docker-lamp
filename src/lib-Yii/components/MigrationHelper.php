<?php
/**
 * Хелпер для работы с миграциями
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class MigrationHelper
{
    
    /**
     * Создает таблицы для системы проверки доступа RBAC.
     * 
     * @param CDbConnection $connection Подключение к БД
     * @param RoleBasedUser $model Класс для которого создаем таблицы
     * @param Bool $ownRolesSet Если True, то набор ролей будет уникальный для указанного класса
     * @throws Exception
     */
    public static function createRbacTables(CDbConnection $connection,  RoleBasedUser $model)
    {
        //Получаем код миграции
        $pathToAuth = Yii::getPathOfAlias("system.web.auth");
        $filename = "schema-mysql.sql";
        $path = StringHelper::joinPaths($pathToAuth, $filename);
        if(!file_exists($path))
            throw new Exception ("Не удалось найти файл '$path'");
        
        //Тут начинается магия
        $sql = file_get_contents($path);
        $prefix = $model->getRbacPrefix();
        
        //Определяем заменять ли часть названия только в первой таблице или во всех
        //И заменяем название таблиц в SQL запросах
        $tableNamePartToReplace = !$model->hasOwnRoleSet() ? "AuthAssignment" : "Auth";
        $replacement = $prefix.$tableNamePartToReplace;
        $newSql = str_replace($tableNamePartToReplace, $replacement, $sql);
  
        //Создаем таблицы
        $cmd = $connection->createCommand($newSql);
        $cmd->execute();
        
        
        //Теперь надо добавить уникальные и внешние ключи, чтобы права не хуячились,
        //когда пользователь меняет логин
        $tableName = $prefix."AuthAssignment";
        
        //Добавляем уникальный ключ в таблицу пользователей
        $cmd->setText("ALTER TABLE `{$model->tableName()}` ADD UNIQUE INDEX (`{$model->getRbacIdName()}`)");
        $cmd->execute();
        
        //Добавляем внешний ключ в таблицу прав на таблицу пользователей
        $cmd->addForeignKey($tableName."user", $tableName,"userId",$model->tableName() ,$model->getRbacIdName(),  ForeignKey::CASCADE,  ForeignKey::CASCADE);
        
    }

    /**
     * Сохраняет модель или кидает исключение с пояснением, почему это не удалось
     * 
     * @param СActiveRecord $model Модель
     * @return Bool True, в случае успеха
     * @throws Exception
     */
    public static function saveModel(ActiveRecord $model)
    {
           $saved  = $model->save();
       
       if(!$saved)
       {
           $errors = $model->getErrors();
           $str = print_r($model->getAttributes(),true)."\n Errors: ".print_r($errors,true);
           $msg = "Не удалось сохранить модель '".get_class($model)."': \n ".$str;         
           throw new Exception($msg);
       }
       return $saved;
    }

}
