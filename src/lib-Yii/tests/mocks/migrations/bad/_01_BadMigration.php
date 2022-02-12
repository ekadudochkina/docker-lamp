<?php
/**
 * Пример плохой миграции
 */
class _01_BadMigration extends DbMigration
{

    public function getNumber()
    {
        return 01;
    }

    public function up()
    {
        //Пример применения автоматической миграции
        //echo "---- BAD -----";
        Yii::import("root.lib-Yii.tests.mocks.*");
        ActiveRecordHelper::executeMigrationForModel("GenericModel", $this->connection);

        $model = new GenericModel();
        $model->ip = "127.0.0.1";
        $model->url = "/home";
        
        $model->save();
    }

    public function down()
    {
        
    }

}
    