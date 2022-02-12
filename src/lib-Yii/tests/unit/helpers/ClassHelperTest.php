<?php

/**
 * Юнит тесты для класс хелпера
 *
 * @see Hs\Helpers\ClassHelper
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ClassHelperTest extends \Hs\Test\NoDbTestCase
{
    /**
     * Тестовое поле с описанием
     * 
     * @see ClassHelper
     * @var String 
     */
    protected $field1 = null;
    
    /**
     *
     * @var String Тестовое поле с иным описанием
     */
    protected $field2 = null;

    protected $field3 = null;

    /**
     *
     * @see ActiveRecord
     * @see ArrayHelper Этот тег
     * делится на две строчки
     * @see CModel
     * @author Alex
     */
    protected $multipleTagsField = null;
    
    public function methodWithoutComment()
    {
        return null;
    }
    
    /**
     * 
     * @return Null
     */
    public function methodWithoutDescription()
    {
        return null;
    }
    
    /**
     * Проверка получение имени класса из файла
     */
    public function testClassName()
    {
       $name = Hs\Helpers\ClassHelper::getClassName("ClassHelperTest.php");
       $this->assertEquals("ClassHelperTest",$name);
       $fromFullPath = Hs\Helpers\ClassHelper::getClassName(__FILE__);
       $this->assertEquals("ClassHelperTest",$fromFullPath);
       
    }
    
    /**
     * Проверка получения комментариев к классу
     */
    public function testClassComments()
    {
        $original = "Юнит тесты для класс хелпера";
        $description =  Hs\Helpers\ClassHelper::getDescriptionForClass("ClassHelperTest");
        $this->assertEquals($original,$description);
        
        $shouldBeNull = Hs\Helpers\ClassHelper::getDescriptionForClass("\Hs\Test\Mocks\Comments\EmptyDescriptionClass");
        $this->assertNull($shouldBeNull);
        $shouldBeNull2 = Hs\Helpers\ClassHelper::getDescriptionForClass("\Hs\Test\Mocks\Comments\EmptyCommentClass");
        $this->assertNull($shouldBeNull2);
    }
    
    /**
     * Тестируем получение описания методов
     * @see Hs\Helpers\ClassHelper
     * @author Alex
     */
    public function testMethodComments()
    {
        $original = "Тестируем получение описания методов";
        $description = Hs\Helpers\ClassHelper::getDescriptionForMethod("ClassHelperTest","testMethodComments");
        $this->assertEquals($original,$description);
        
        $author = Hs\Helpers\ClassHelper::getTagForMethod("ClassHelperTest","testMethodComments","author");
        $see = Hs\Helpers\ClassHelper::getTagForMethod("ClassHelperTest","testMethodComments","see");
        $this->assertEquals("@see Hs\Helpers\ClassHelper",$see);
        $this->assertEquals("@author Alex",$author);
        
        //Все вместе
        $tags = Hs\Helpers\ClassHelper::getTagsForMethod("ClassHelperTest","testMethodComments");
        $this->assertEquals(2,count($tags));
        $this->assertEquals("@see Hs\Helpers\ClassHelper",$tags[0]);
        $this->assertEquals("@author Alex",$tags[1]);
        
        //Пустые
        $shouldBeNull = Hs\Helpers\ClassHelper::getDescriptionForMethod("ClassHelperTest","methodWithoutComment");
        $this->assertNull($shouldBeNull);
        
        $shouldBeNull2 = Hs\Helpers\ClassHelper::getDescriptionForMethod("ClassHelperTest","methodWithoutDescription");
        $this->assertNull($shouldBeNull2);
    }
    
    /**
     * Првоерка получениев комментариев к полям
     */
    public function testFieldComments()
    {
        $original = "Тестовое поле с описанием";
        $description = Hs\Helpers\ClassHelper::getDescriptionForField("ClassHelperTest","field1");
        $this->assertEquals($original,$description);
        
        //Тестируем второе описание
        $original2 = "Тестовое поле с иным описанием";
        $description2 = Hs\Helpers\ClassHelper::getDescriptionForField("ClassHelperTest","field2");
        $this->assertEquals($original2,$description2);
        
        
        //Тестируем пустые
        $shouldBeNull = Hs\Helpers\ClassHelper::getCommentForField("ClassHelperTest","field3");
        $this->assertNull($shouldBeNull);
        $shouldBeNull2 = Hs\Helpers\ClassHelper::getDescriptionForField("ClassHelperTest","field3");
        $this->assertNull($shouldBeNull2);
        
        //Теструем теги
        $var = Hs\Helpers\ClassHelper::getTagForField("ClassHelperTest","field1","var");
        $see = Hs\Helpers\ClassHelper::getTagForField("ClassHelperTest","field1","see");
        $this->assertEquals("@var String",$var);
        $this->assertEquals("@see ClassHelper",$see);
        
        //Все вместе
        $tags = Hs\Helpers\ClassHelper::getTagsForField("ClassHelperTest","field1");
        $this->assertEquals(2,count($tags));
        $this->assertEquals("@see ClassHelper",$tags[0]);
        $this->assertEquals("@var String",$tags[1]);
        
        //Поиск одинаковых тегов
        $tags2 = Hs\Helpers\ClassHelper::getTagsForField("ClassHelperTest","multipleTagsField","see");
        $this->assertEquals(3,count($tags2));
        $this->assertEquals("@see ActiveRecord",$tags2[0]);
        $this->assertEquals("@see ArrayHelper Этот тег\nделится на две строчки",$tags2[1]);
        $this->assertEquals("@see CModel",$tags2[2]);
       
    }
    
    /**
     * Проверка получение тегов классов
     */
    public function testClassTags()
    {
        $author = Hs\Helpers\ClassHelper::getTagForClass("ClassHelperTest","author");
        $see = Hs\Helpers\ClassHelper::getTagForClass("ClassHelperTest","see");
        $this->assertEquals("@author Sarychev Aleksey <freddis336@gmail.com>",$author);
        $this->assertEquals("@see Hs\Helpers\ClassHelper",$see);
        
        //Все вместе
        $tags = Hs\Helpers\ClassHelper::getTagsForClass("ClassHelperTest");
        $this->assertEquals(2,count($tags));
        $this->assertEquals("@see Hs\Helpers\ClassHelper",$tags[0]);
        $this->assertEquals("@author Sarychev Aleksey <freddis336@gmail.com>",$tags[1]);
    }
    
    /**
     * Проверка получение имен и значений тегов
     */
    public function testTagNamesAndValues()
    {
        $tag = \Hs\Helpers\ClassHelper::getTagForField("ClassHelperTest","field1","see");
        
        $name = \Hs\Helpers\ClassHelper::getTagName($tag,true);
        $strippedName = \Hs\Helpers\ClassHelper::getTagName($tag,false);
        $value = \Hs\Helpers\ClassHelper::getTagValue($tag);
        
        $this->assertEquals($name,"@see");
        $this->assertEquals($strippedName,"see");
        $this->assertEquals("ClassHelper",$value);
        
    }
}
