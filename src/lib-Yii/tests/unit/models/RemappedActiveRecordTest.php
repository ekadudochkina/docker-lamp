<?php

/**
 * Тесты для моделей из других проктов, созданных не по конвенции
 * 
 * @see ControllerAccessChecker
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class RemappedActiveRecordTest extends Hs\Test\TestCase
{
    
    /**
     * Подгототавливаем тест
     */
    protected function setUp()
    {
        parent::setUp();
        
        Yii::import("root.lib-Yii.tests.mocks.models.ExternalModel");
        Yii::import("root.lib-Yii.tests.mocks.models.RemappedExternalModel");
        //bug::drop(new ExternalModel());
        ActiveRecordHelper::executeMigrationForModel("ExternalModel",Yii::app()->getDb());
    }
    
    /**
     * Тестируем, что поля теперь доступны по новым именам
     */
    public function testFieldChange()
    {
       //Создаем кривую модель
       $model = new ExternalModel();
       $model->full_name = "Мое имя";
       $model->save();
       
       
       //Tecтируем модель с красивыми полями
       $remapped = RemappedExternalModel::model()->find();
       
       $this->assertTrue(is_subclass_of($remapped,"RemappedActiveRecord"),"Объект не является наследником RemappedActiveRecord, тест не действителен");
       $this->assertEquals("Мое имя",$remapped->title,"Поля не изменились");
    }
    
    /**
     * Тестируем, что модель можно найти в поиске
     */
    public function testFinding()
    {
       //Создаем кривую модель
       $model = new ExternalModel();
       $model->full_name = "Мое имя";
       $model->save();
       
        //Создаем кривую модель
       $model = new ExternalModel();
       $model->full_name = "Другое имя";
       $model->save();
       
       //Tecтируем модель с красивыми полями
       $attrs = ["title" => "Мое имя"];
       $arr = RemappedExternalModel::model()->findAllByAttributes($attrs);
       
       $this->assertEquals(count($arr),1,"Неверное количество найденных моделей");
       $this->assertEquals("Мое имя",$arr[0]->title,"Поля не изменились после поиска");
    }
    
    /**
     * Тестируем сохранение моделей в бд
     */
    public function testUpdating()
    {
        //Создаем кривую модель
       $model = new ExternalModel();
       $model->full_name = "Мое имя";
       $model->save();
       
       
       //Изменяем поля, при помощи красивой модели
       $remapped = RemappedExternalModel::model()->find();
       $remapped->title = "Новое имя";
       $remapped->save();
       
       
       //Тестируем, что все сохранилось
       $model->refresh();
       $this->assertEquals("Новое имя",$model->full_name,"Поля не изменились");
       
    }
    
    /**
     * Проверяет, что связи также работают хорошо
     */
    public function testRelations()
    {
        //Создаем кривую модель
       $parent = new ExternalModel();
       $parent->full_name = "Родитель";
       $parent->save();
       
        //Создаем кривую модель
       $model = new ExternalModel();
       $model->full_name = "Дочка";
       $model->parent_id = $parent->getPk();
       $model->save();
       
       $child = RemappedExternalModel::model()->findByPk($model->getPk());
       
       $this->assertNotNull($child->parent);
       $this->assertEquals($child->parent->title, "Родитель");
    }
    
}
