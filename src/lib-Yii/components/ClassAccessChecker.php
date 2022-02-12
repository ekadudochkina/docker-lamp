<?php

/**
 * Проверят пользователя по его классу
 *
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 * @package Hs\Access
 */
class ClassAccessChecker extends AccessChecker
{
    
    /**
     * Проверяет пользователя
     * @param BaseController $controller
     * @return Bool True, если пользовaтель прошел проверку
     */
    public function ExecuteAccessCheck(BaseController $controller)
    {  
        
        $user = $controller->findCurrentUser();
//        bug::drop($user,$controller->getUserProvider());
        if(!$user || get_class($this->obj) !== get_class($user))
            return false;
        
        return true;
               
    }
}
