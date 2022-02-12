<?php
/**
 * Проверят авторизован ли пользователь в системе 
 *
 * Dudochkina Ekaterina <edudochkina@home-studio.pro>
 * @package Hs\Access
 */
class AuthorizedAccessChecker extends AccessChecker
{
    /**
     * Роут на который редиректятся неправильные пользователи
     * @var type 
     */
    public $defaultRoute = 'auth/login';
       
    /**
     * Проверяет пользователя
     * @param BaseController $controller
     * @return Bool True, если пользовтель прошел проверку
     */
    public function ExecuteAccessCheck(BaseController $controller)
    {
        $user = $controller->findCurrentUser();
        //bug::drop($user);
        $result =  $user !== null;
        return $result;
    }
}
