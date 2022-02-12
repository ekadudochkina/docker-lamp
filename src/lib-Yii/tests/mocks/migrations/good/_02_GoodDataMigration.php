<?php
/**
 * Пример плохой миграции
 */
class _02_GoodDataMigration extends DbMigration
{

    public function getNumber()
    {
        return 02;
    }

    public function up()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");
        $model = new GenericModel();
        $model->ip = "127.0.0.1";
        $model->url = "/home";
        $this->saveModel($model);
    }

    public function down()
    {
        
    }

}
    