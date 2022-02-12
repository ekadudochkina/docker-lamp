<?php

namespace Hs\Access;

/**
 * Запрещает доступ к контроллерам, которые не наследуются от базового.
 * Базовых контроллеров может быть более одного.
 * 
 * Данный класс удобен при разработке, когда нужно скрыть какие-то контроллеры.
 * 
 * @package Hs\Access
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ControllerAccessChecker extends \AccessChecker
{

    /**
     * Массив разрешенных контроллеров
     * 
     * @var string[]
     */
    protected $controllerNames = array();

    /**
     * @param String $controllerName Имя контроллера, экшены которого разрешены
     */
    public function __construct($controllerName)
    {
        \Yii::import("application.controllers.*");
        $this->addController($controllerName);
    }

    /**
     * Метод проверяет нужно ли нам редиректить пользователя на маршрут для неправильныйх пользователей или они уже переброшенны на данный маршрут
     * @param BaseController $controller
     * @return boolean True, если проверка прошла успешно
     */
    public function ExecuteAccessCheck(\BaseController $controller)
    {
        $result = false;
        foreach ($this->controllerNames as $baseController)
        {
            $result = $result || $this->checkControllers($controller, $baseController);
        }
        return $result;
    }

    /**
     * Добавление дополнительного базового контроллера экшены которого разрешены
     * 
     * @param String $stringName Имя класса контроллера
     * @throws Exception
     */
    public function addController($stringName)
    {
        if (!is_string($stringName))
        {
            throw new Exception("Имя контроллера должно быть строкой");
        }

        if (!class_exists($stringName))
        {
            throw new Exception("Класс '$stringName' не существуе");
        }

        $this->controllerNames[] = $stringName;
    }

    /**
     * Проверяет, является ли текущий контроллер наследником одного из добавленных
     * 
     * @param CController $currentController Текущий конроллер
     * @param String $baseControllerName Имя класса базового контроллера
     * @return True, если текущий контроллер является наследником базового
     */
    protected function checkControllers($currentController, $baseControllerName)
    {
        $class = $baseControllerName;
        $isSubclass = is_subclass_of($currentController, $class);
        $isClass = get_class($currentController) == $class;
        $result = $isSubclass || $isClass;

        //bug::drop($result,$class,$baseController);
        return $result;
    }

}
