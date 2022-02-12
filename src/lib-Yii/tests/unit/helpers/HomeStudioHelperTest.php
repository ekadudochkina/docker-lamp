<?php
use Hs\Helpers;
/**
 * Юнит тесты для HomeStudioHelper
 *
 * @see Hs\Helpers\HomeStudioHelper
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class HomeStudioHelperTest extends \Hs\Test\NoDbTestCase
{

    /**
     * Проверка полчения имени файла по пути к нему
     */
    public function testGettingNamespacedClassName()
    {
        $path = Yii::getPathOfAlias("root.lib-Yii.components.controllers")."/EmailTestController.php";
        if(!file_exists($path))
        {
            $this->fail("Не найден класс по пути '$path'");
        }
        $classname = Hs\Helpers\HomeStudioHelper::getClassName($path);
        $this->assertEquals("Hs\Controllers\EmailTestController",$classname,"Не удалось корректно определить имя класса");
        
    }
}