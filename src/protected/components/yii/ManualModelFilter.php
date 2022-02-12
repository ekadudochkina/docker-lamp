<?php

/**
 * Description of ManualModelFilter
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ManualModelFilter extends ModelFilter
{
    public function __construct($models, $controller,$modelCount = null,$modelsPerPage=1000000)
    {
        parent::__construct($models[0],$controller);
        $this->models = $models;
        $this->isExecuted = true;
        $this->modelCount = $modelCount ? $modelCount : count($models);
        $this->modelsPerPage = $modelsPerPage;
        if($modelCount !== null)
        {
            $this->pageCount = ceil($this->modelCount / $this->modelsPerPage);
        }
    }
}
