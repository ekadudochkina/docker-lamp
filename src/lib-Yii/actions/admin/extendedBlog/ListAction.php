<?php

/**
 * Список статей блога
 * 
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ListAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {
        $data = array();
        $filter = new ModelFilter(new BlogArticle(), $this->getController());
        $filter->setModelsPerPage(30);
        $filter->setParam("orderByDirection", "desc");

        $this->addViewData($filter, "filter");
        $this->render();
    }

}
