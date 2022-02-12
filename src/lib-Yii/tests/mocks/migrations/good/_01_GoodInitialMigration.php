<?php
/**
 * Пример хорошой миграции
 */
class _01_GoodInitialMigration extends DbMigration
{

    public function getNumber()
    {
        return 01;
    }

    public function up()
    {
        //Пример применения автоматической миграции
        Yii::import("root.lib-Yii.tests.mocks.*");
        $this->createMigrationForModel("GenericModel", $this->connection);
    }

    public function down()
    {
        
    }

}
    