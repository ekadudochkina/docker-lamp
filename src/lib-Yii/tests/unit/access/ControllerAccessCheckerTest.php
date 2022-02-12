<?php

/**
 * Тестируем работу ControllerAccessChecker
 * 
 * @see ControllerAccessChecker
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ControllerAccessCheckerTest extends \Hs\Test\NoDbTestCase
{
    
    /**
     * Тестирование работы класса
     */
    public function testInterface()
    {
        Yii::import("root.lib-Yii.tests.mocks.*");
        
        $baseController = new BaseControllerMock();
        $checker = new \Hs\Access\ControllerAccessChecker(get_class($baseController));
        
        //Проверяем, что наследникам класса доступ разрешен
        $good = new BaseControllerMockChildClass();
        $goodResult = $checker->checkAccess($good);
        $this->assertTrue($goodResult,"Контроллер является наследником, но доступ запрещен.");
        
        //Теперь проверяем контроллер, который не является наследником
        $bad = new NotBaseControllerMockChildClass();
        $badResult = $checker->checkAccess($bad);
        $this->assertFalse($badResult,"Контроллер не является наследником, но доступ разрешен.");
    }
    
}
