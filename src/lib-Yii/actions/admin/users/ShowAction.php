<?php

/**
 * Description of ShowAction
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ShowAction extends CViewAction
{
    public function run()
    {
        $filter = new ModelFilter(new User(),$this->controller);
        $this->getController()->addViewData($filter,"filter");
        $filter->setModelsPerPage(30);
        $this->getController()->render($this->view);
    }
}
