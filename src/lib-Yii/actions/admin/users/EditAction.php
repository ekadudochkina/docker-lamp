<?php

/**
 * Description of ShowAction
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class EditAction extends CViewAction
{
    public function run()
    {
        
//        $path = Yii::getPathOfAlias("application.modules.users.views.default");
//        $this->getController()->setViewPath($path);
        
        $this->getController()->render($this->view);
    }
}
