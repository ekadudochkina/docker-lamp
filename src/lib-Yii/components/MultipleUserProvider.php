<?php

/**
 * Предоставляет возможность получать пользователей из нескольких таблиц
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Auth
 */
class MultipleUserProvider extends SimpleUserProvider
{

    /**
     * Классы пользователей
     * 
     * @var IUser[] 
     */
    protected $classes = array();
    
    /**
     * Список путей, куда пользователи отправляются после логина
     * @var String[]
     */
    protected $routes = array();
    
    /**
     * Путь пользователя по-умолчанию
     * @var type 
     */
    public $defaultRoute = "profile/index";

    /**
     * @param \IUser $model Объект пользователя (можно пустой)
     * @param String $route Роут на который перенаправлять пользователя после логина
     */
    public function __construct(\IUser $model, $route = null)
    {
        $this->addUserClass($model, $route);
        parent::__construct($model);
    }

    /**
     * Добавление обработки входа для класса пользователя
     * 
     * @param \IUser $model Объект пользователя (можно пустой)
     * @param String $route Роут на который перенаправлять пользователя после логина
     */
    public function addUserClass(IUser $model, $route = null)
    {
        $this->classes[] = $model;
        $this->routes[] = $route;
    }

    /**
     * Возвращает пользователя по его логину
     * 
     * @param String $login Логин пользователя
     * @return IUser Пользователь
     */
    public function findByLogin($login)
    {
        foreach ($this->classes as $class)
        {

            $this->userModel = $class;
            $user = parent::findByLogin($login);
            if ($user)
                return $user;
        }
        return null;
    }

    /**
     * Возвращает пользователя по его почте
     * 
     * @param String $email Почта пользователя
     * @return IUser Пользователь
     */
    public function findByEmail($email)
    {
        foreach ($this->classes as $class)
        {
            $this->userModel = $class;
            $user = parent::findByEmail($email);
            if ($user)
                return $user;
        }
        return null;
    }
    
    /**
     * Получает путь, куда пользователь будет переведен после логина
     * 
     * @param IUser $user Пользователь
     * @return String Путь пользователя или путь по-умолчанию
     */
    public function getUserRoute(IUser $user)
    {
        $class = get_class($user);
        foreach($this->classes as $key => $obj)
            if(get_class($obj) === $class)
            {
                $route = $this->routes[$key];
                if($route === null)
                    break;
                return $route;
            }
            
        return $this->defaultRoute;
    }
    
    /**
     * Перенаправляет текущего пользователя домой
     * 
     * @param BaseController $controller
     */
    public function redirectCurrentUser($controller)
    {
        $user = $controller->findCurrentUser();
        $route = $this->getUserRoute($user);
        $controller->redirectToRoute($route);
    }

}
