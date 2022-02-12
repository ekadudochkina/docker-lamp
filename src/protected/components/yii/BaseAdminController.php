<?php

/**
 * Базовый Контроллер панели управления
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class BaseAdminController extends NeonAdminController
{
    /**
     * Создает главное меню
     * 
     * @return string[]
     */
    public function generateMainMenu()
    {
        $menu = array();
        $menu[] = array('Users','adminUser/index','entypo-users right');
        $menu[] = array('Log Out','logout','entypo-logout right');
        return $menu;
    }
    
    
      public function beforeAction($action) {
        $ret = parent::beforeAction($action);

        $this->redirectToRoute("landing/index");

        return $ret;
        
    }
}
