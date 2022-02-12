<?php

/**
 * Базовый класс для проверки доступа пользователей
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Access
 */
abstract class AccessChecker
{

    /**
     * Ссылка на класс
     * 
     * @var Mixed 
     */
    protected $obj;

    /**
     * @param Mixed $obj Объект класса пользователя
     */
    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    /**
     * Роут на который редиректятся неправильные пользователи
     * @var type 
     */
    protected $noAccessRoute = "auth/login";

    /**
     * Проверяет нужно ли рендирект пользователя и отправляет его на другой url если это необходимо
     * 
     * @param BaseController $controller Текущий контроллер
     * @param String $noAccessRoute Роут на который редиректить пользователя
     */
    public function checkAccessAndRedirect(BaseController $controller, $noAccessRoute = null)
    {
        if (!$noAccessRoute)
            $noAccessUrl = $controller->createAbsoluteUrl($this->noAccessRoute);
        else
            $noAccessUrl = $controller->createAbsoluteUrl($noAccessRoute);

        $currentUrl = $controller->createAbsoluteUrl($controller->getRoute());
        if ($currentUrl == $noAccessUrl)
        {
            return true;
        }
        // bug::drop($currentUrl,$noAccessUrl);


        if (!$this->checkAccess($controller))
            $controller->redirect($noAccessUrl);
    }

    /**
     * Проверяет пользователя (эта функция прослойка,между вариантами проверки пользователя)
     * 
     * @param BaseController $controller Текущий контроллер
     * @return Bool True, если пользовтель прошел проверку
     */
    public function checkAccess(BaseController $controller)
    {
        $executeAccessCheck = $this->ExecuteAccessCheck($controller);

        return $executeAccessCheck;
    }

    /**
     * Метод проверяет нужно ли нам редиректить пользователя на маршрут для неправильныйх пользователей или они уже переброшенны на данный маршрут
     * @param BaseController $controller
     * @return boolean True, если проверка прошла успешно
     */
    abstract public function ExecuteAccessCheck(BaseController $controller);
}
