<?php
/**
 * Начальная миграция для таблиц. Сюда можно вкладывать все изменения.
 * Создание данных нельзя совмещать с изменением таблиц.
 */
class _01_InitMigration extends DbMigration
{

    public function getNumber()
    {
        return 01;
    }

    public function up()
    {
        //Пример применения автоматической миграции

        $this->createMigrationForModel("User");

        
    }

    public function down()
    {
        
    }

}
