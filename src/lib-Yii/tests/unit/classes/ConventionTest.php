<?php

/**
 * Тесты для классов в библиотеке
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ConventionTest extends \Hs\Test\NoDbTestCase
{

    /**
     * Строки, которые являются строками по-умолчанию.
     * При нахождении такой строки в комментарии можно полагать, что разработчик его не писал
     * 
     * @var String
     */
    protected $defaultStrings = ["КОММЕНТАРИЙ", "Description"];

    /**
     * Список методов, для которых нет необходимости делать проверки
     * Как правило это магические методы или методы, которые являются типичными для фреймворка
     * 
     * @var String
     */
    protected $noDescriptionMethods = [
        "__construct",
        "rules",
        "attributeLabels",
        "beforeAction",
        "tableName",
        "relations",
        "methodWithoutDescription", //Для теста ClassHelperTest
        "methodWithoutComment", //Для теста ClassHelperTest
    ];

    /**
     * Список классов, которые не участвуют в проверках
     * Как правило - это чужие классы, не написанные командой Home-Studio
     * @var String[]
     */
    protected $noCheckClasses = [
        "ECKEditor",
        "NestedPDO",
        "PDO",
        "PHPUnit_Extensions_Story_TestCase",
        "PHPUnit_Framework_TestCase",
        "Hs\Test\Mocks\Comments\EmptyCommentClass", //У данного класса намеренно нет комментариев
        "Hs\Test\Mocks\Comments\EmptyDescriptionClass", //У данного класса намеренно нет комментариев
        "Facebook\WebDriver\Remote\RemoteWebDriver",
        "bootstrap", //это вовсе не класс, а файл запуска PHPUnit
        "_01_GoodInitialMigration",
        "_02_GoodDataMigration",
        "_01_BadMigration",
        "_01_BadMigration2"
    ];

    /**
     * Тестирует наличие корректных имен классов
     */
    public function testClassNames()
    {
        EnvHelper::enableComposer();
        Yii::import("root.lib-Yii.tests.mocks.*");
        
        $files = Hs\Helpers\HomeStudioHelper::getAllClassesInProject();
        $this->log("Проверяем " . count($files) . " классов");
        foreach ($files as $filePath)
        {
            include_once $filePath;
            $classname = Hs\Helpers\HomeStudioHelper::getClassName($filePath);
            //Пропускаем исключения
            if (in_array($classname, $this->noCheckClasses))
            {
                continue;
            }
            $this->log("Checking class '$classname'");
            $reflector = new ReflectionClass($classname);
            if($reflector == null)
            {
                $this->fail("Класс '$classname' не найден.");
            }
            $this->assertEquals($classname,$reflector->getName(),"Имя файла '$filePath' не соответствует классу");
        }
    }
    
    /**
     * Тестирует наличие корректных комментариев классам
     */
    public function testCommentsForClasses()
    {
        EnvHelper::enableComposer();
        Yii::import("root.lib-Yii.tests.mocks.*");
        
        $files = Hs\Helpers\HomeStudioHelper::getAllClassesInProject();
        $this->log("Проверяем " . count($files) . " классов");
        foreach ($files as $filePath)
        {
            include_once $filePath;
            $classname = Hs\Helpers\HomeStudioHelper::getClassName($filePath);
            //Пропускаем исключения
            if (in_array($classname, $this->noCheckClasses))
            {
                continue;
            }
            $this->log("Checking class '$classname'");
            $this->checkCommentsForClass($classname);
            $reflector = new ReflectionClass($classname);
            $methods = $reflector->getMethods();
            foreach ($methods as $method)
            {
                $this->checkCommentsForMethod($method);
            }
        }
    }

    /**
     * Тестирует наличие тегов @package в классах библиотеки. 
     * Если этих тегов не будет, то apigen будет генерировать неправильную документацию.
     */
    public function testLibPackages()
    {
        EnvHelper::enableComposer();
        $files = Hs\Helpers\HomeStudioHelper::getLibClasses();
        $this->log("Проверяем " . count($files) . " классов");
        foreach ($files as $filePath)
        {
            include_once $filePath;
            $classname = Hs\Helpers\HomeStudioHelper::getClassName($filePath);
            //Пропускаем исключения
            if (in_array($classname, $this->noCheckClasses))
            {
                continue;
            }
            $tag = \Hs\Helpers\ClassHelper::getTagForClass($classname, "package");
            $this->assertTrue(StringHelper::hasSubstring($tag, "Hs"), "Класс '$classname' не содержит тэг @package");
            $this->log("Checking class '$classname'");
        }
    }

    /**
     * Проверка комментариев для класса
     * 
     * @param String $classname Имя класса
     */
    public function checkCommentsForClass($classname)
    {
        $description = Hs\Helpers\ClassHelper::getDescriptionForClass($classname);

        $this->assertFalse(empty($description), "Пустой комментарий у класса '$classname'");
        $isDefaultComment = \StringHelper::hasSubstrings($description, $this->defaultStrings);
        $this->assertFalse($isDefaultComment, "Класс '$classname' содержит комментарий по-умолчанию");
    }

    /**
     * Проврка комментариев для методов
     * @param ReflectionMethod $method Метод
     */
    public function checkCommentsForMethod(ReflectionMethod $method)
    {
        //Не првоеряем классы Yii
        if (\FileHelper::isYiiFile($method->getFileName()))
        {
            return;
        }
        //Не проверяем исключения
        if (in_array($method->class, $this->noCheckClasses))
        {
            return;
        }

        $this->log("Checking method '{$method->name}' of '{$method->class}'");
        $description = Hs\Helpers\ClassHelper::getDescriptionForMethod($method->class, $method->name);

        $camelCase = $this->isCamelCased($method->name);
        //$this->assertTrue($camelCase,"Метод '{$method->name}' класа '{$method->class}' не в Camel Case.");
        //Для некоторых методов мы не проверям описания
        if (!in_array($method->name, $this->noDescriptionMethods))
        {
            $this->assertFalse(empty($description), "Пустой комментарий у метода '{$method->name}' класа '{$method->class}'");
        }

        $isDefaultComment = in_array($description, $this->defaultStrings, false);
        $this->assertFalse($isDefaultComment, "У метода '{$method->name}' класа '{$method->class}' комментарий по-умолчанию");
        $comment = Hs\Helpers\ClassHelper::getCommentForMethod($method->class, $method->name);
    }

    /**
     * Проверка, наприсано ли имя в CamelCase конвенции
     * 
     * @param String $string Имя метода или переменной
     * @return boolean
     */
    public function isCamelCased($string)
    {
        if (lcfirst($string) != $string)
        {
            return false;
        }

        return true;
    }

}
