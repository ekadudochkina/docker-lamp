<?php

/**
 * Тесты для моделей
 * 
 * @see ControllerAccessChecker
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ActiveRecordTest extends \Hs\Test\NoDbTestCase
{
    /**
     * Тестируем имена моделей в проекте, они должны быть в нижнем регистре.
     * Иначе будет проблема со скачиванием базы данных и восстановлением под  Windows.
     */
    public function testTableNames()
    {
        $models = $this->getAllModels();
        foreach($models as $instance)
        {
            $tableName = $instance->tableName();
            $lower = strtolower($tableName);
            $className = get_class($instance);
            $this->assertEquals($lower,$tableName,"Имя таблицы не в нижнем регистре '$tableName' для модели '$className'");
        }
    }
    
    
    /**
     * Проверка комментариев для связей
     */
    public function testRelations()
    {
        $models = $this->getAllModels();
        foreach($models as $model)
        {
            $relations = $model->relations();
            if(empty($relations))
            {
                continue;
            } 
            
            $reflect = new ReflectionClass(get_class($model));
            $className = $reflect->getFileName();
            foreach($relations as $field => $info)
            {
                $class = $info[1];
                $type = $info[0];
                
                $tags = \Hs\Helpers\ClassHelper::getTagsForClass(get_class($model),"property");
                $this->assertNotEmpty($tags,"У модели '$className' есть связи, но нет комментариев свойств.");
                
                $expectedField = "$".$field;
                $foundField = false;
                foreach($tags as $tag)
                {
                    $propClass = \Hs\Helpers\ClassHelper::getTagValue($tag,1);
                    $propField = \Hs\Helpers\ClassHelper::getTagValue($tag,2);
                    $propComment = \Hs\Helpers\ClassHelper::getTagValue($tag,3);
                    //$this->log($propField." ".$propClass." ".$propComment);
                   // $this->log($propField." ".$expectedField);
                    if($expectedField != $propField)
                    {
                        continue;
                    }
                    
                    $expectedClass = $type == CActiveRecord::HAS_MANY ? $class."[]" : $class;
                    $this->assertEquals($expectedClass,$propClass,"Класс свойства не соответсвует классу связи в модели '$className'");
                    
                    $this->assertNotEquals(CodeGenHelper::getDefaultComment(), $propComment,"Обнаружен комментарий по-умолчанию у модели '$className' для связи '$field'");
                    
                    $foundField = true;
                    break;
                }
                
                
               $this->assertTrue($foundField,"Не найдено свойство {$expectedField} для связи модели '$className'");
            }
        }
    }
    
    /**
     * Возвращает список всех моделей в проекте и библиотеке
     * 
     * @return CActiveRecord[];
     */
    protected function getAllModels()
    {
        $result = [];
        ReflectionHelper::includeAll();
        $classNames = ReflectionHelper::getSubclassesOf("CActiveRecord");
        foreach($classNames as $name)
        {
            $reflection = new ReflectionClass($name);
            if($reflection->isAbstract())
            {
                continue;
            }
            $instance = ActiveRecordHelper::createModelInstance($name);
            $result[] = $instance;
        }
        return $result;
    }
}
