<?php

/**
 * Данный менеджер авторизации обладает свойствами получения ролей пользователей.
 * CDbAuthManager менеджер предполагает, что таблицы будут названы всегда одинаково,
 * а у нас есть разные классы пользователей и им нужны разные таблицы
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
class DbAuthManager extends CDbAuthManager
{

    /**
     * Initializes the application component.
     * This method overrides the parent implementation by establishing the database connection.
     */
    public function init()
    {
        //Ну это фикс бага Yii, суть в том, что менеджер не подцепляет название таблиц
        $this->setDefaultTableNames();
    }

    /**
     * Установка префикса для пользователей.
     * Так как назначение ролей для каждого класса пользователей должно происходить в отдельной таблице
     * 
     * @param RoleBasedUser $user Модель пользователя, можно пустую
     */
    public function setUserPrefix(RoleBasedUser $user)
    {
        $this->unsetUserPrefix();
        $this->assignmentTable = $this->getDbConnection()->tablePrefix . $user->getRbacPrefix() . $this->assignmentTable;
        if ($user->hasOwnRoleSet())
        {
            $this->itemTable = $this->getDbConnection()->tablePrefix . $user->getRbacPrefix() . $this->itemTable;
            $this->itemChildTable = $this->getDbConnection()->tablePrefix . $user->getRbacPrefix() . $this->itemChildTable;
        }
    }

    /**
     * Убирает префиксы пользователя с названий таблиц
     */
    public function unsetUserPrefix()
    {
        $this->setDefaultTableNames();
    }

    /**
     * Назначение стандартных имен таблицам
     */
    protected function setDefaultTableNames()
    {
        $this->itemTable = $this->getDbConnection()->tablePrefix . "AuthItem";
        $this->itemChildTable = $this->getDbConnection()->tablePrefix . "AuthItemChild";
        $this->assignmentTable = $this->getDbConnection()->tablePrefix . "AuthAssignment";
    }

}
