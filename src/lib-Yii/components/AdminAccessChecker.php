<?php

/**
 * Проверят пользователя по статусу функции isAdmin
 *
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 * @package Hs\Access
 */
class AdminAccessChecker extends AccessChecker
{
    /**
     * Роут на который редиректятся неправильные пользователи
     * @var type 
     */
    public $defaultRoute;
    
    /**
     * @param Mixed $obj Объект класса пользователя
     * @param String $defaultRoute Роут на который редиректятся неправильные пользователи
     */
    public function __construct($obj,$defaultRoute=null)
    {
        parent::__construct($obj);
        $this->defaultRoute = $defaultRoute;
    }
    
    /**
     * Проверяет пользователя
     * @param BaseController $controller
     * @return Bool True, если пользовaтель прошел проверку
     */
    public function ExecuteAccessCheck(BaseController $controller)
    {              
         $user = $controller->getCurrentUser();
        if($user->isAdmin() == true)
            return true;
        
        return false;
               
    }
}
