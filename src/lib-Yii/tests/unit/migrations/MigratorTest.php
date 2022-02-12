<?php

/**
 * Тестируем объект осуществляющий миграции
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MigratorTest extends \Hs\Test\TestCase
{
    
    /**
     * Успешныйтест, что миграции применяются
     */
    public function testMigration()
    {
        
        $alias = "root.lib-Yii.tests.mocks.migrations.good";
        Yii::import($alias.".*");
        $mockPath = Yii::getPathOfAlias($alias);
        
        $migrator = new Migrator($mockPath);
        $initialCount = count($migrator->getAppliedMigrations());
        
        $migrator->removeCache();
        $migrator->applyNewMigrations();
        
        $count =  count($migrator->getAppliedMigrations()) - $initialCount;
       
        $this->assertEquals(2,$count,"Миграции не применились");
    }
    
    /**
     * Тестирует то, что мигратор не позволяет создавать данные и таблицы в одной миграции.
     */
    public function testTableAndDataDivision()
    {
        Yii::import("root.lib-Yii.tests.mocks.migrations.bad.*");
        $mockPath = Yii::getPathOfAlias("root.lib-Yii.tests.mocks.migrations.bad");
        $migrator = new Migrator($mockPath);
        $migrator->removeCache();
        $initialCount = count($migrator->getAppliedMigrations());
        
        try{
            $migrator->applyNewMigrations();
            $this->fail("Небыло исключения на плохой миграции");
        }
        catch(Exception $e)
        {
            $this->log("Успешно поймали исключение");
            $msg = $e->getMessage();
            $expected  = "Нельзя создавать таблицы и данные в одной миграции";
            $found  = StringHelper::hasSubstring($msg,$expected);
            $this->assertTrue($found,"Текст исключения не верен '$msg'");
        }
        
        $class = get_class(Yii::app()->getDb());
        $pdo = Yii::app()->getDb()->pdoClass;
        $this->assertEquals($class,"DbConnection","Класс не БД поменялся на страндартный");
        $this->assertEquals($pdo,"NestedPDO","Класс не PDO поменялся на страндартный");
        
        
        $log = GenericModel::model()->find();
        $this->assertEmpty($log,"Объект лога почему-то добавился в миграции");
        
        $count =  count($migrator->getAppliedMigrations()) - $initialCount;
        $this->assertEquals(0,$count,"Миграция применилась: Допутимо смешивать создание таблиц и данных");
        
        //Провреяем, что все встало на свои места
        try{
            $this->resetDb();
        }
        catch(Exception $e)
        {
            $this->fail("После ошибки в миграциях не удается их заново применять: '{$e->getMessage()}'");
        }
    }
}
