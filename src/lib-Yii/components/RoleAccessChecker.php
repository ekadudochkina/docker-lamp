<?php
/**
 * Проверят пользователя по его роли
 *
 * Dudochkina Ekaterina <edudochkina@home-studio.pro>
 * @package Hs\Access
 */
class RoleAccessChecker extends AccessChecker
{
    /**
     * Роут на который редиректятся неправильные пользователи
     * 
     * @var String 
     */
    public $defaultRoute = 'client/index';
    
    /**
     * Роль пользователя
     * 
     * @var String 
     */
    public $role;

    /**
     * @param Mixed $obj Объект класса пользователя
     * @param String $role
     */
    public function __construct($obj,$role)
    {
        parent::__construct($obj);
        $this->role = $role;
    }
   
    /**
     * Проверяет пользователя
     * @param BaseController $controller
     * @return Bool True, если пользовaтель прошел проверку
     */
    public function ExecuteAccessCheck(BaseController $controller)
    {
        $role = $controller->getCurrentUser()->getRole()->getName();
        
        if ($role == $this->role)
            return TRUE;
        
        return FALSE;
    }
    
    /**
     * Проверяет есть ли роль текущего пользователя в списке запрещенных ролей
     * 
     * @param String $arr Массив имен ролей
     * @param BaseController $controller
     * @return boolean True, если роль отключена
     */
    public function disable($arr,$controller)
    {
        $currentRole = $controller->getCurrentUser()->getRole()->getName();
        
        foreach ($arr as $role)
        {
            if ($role == $currentRole)
            {
                return TRUE;
                break;
            }
        }
        
        return FALSE;
    }
}
