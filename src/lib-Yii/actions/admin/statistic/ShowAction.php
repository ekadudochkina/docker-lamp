<?php

/**
 * Просмотр статистики сайта по посещениям пользователей
 */
class ShowAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {
        $data = array();
        $statistics = AccessLog::model()->findAll();

        $filter = new ModelFilter(new AccessLog(), $this->getController());
        $filter->setModelsPerPage(10);
        $filter->setParam("orderByDirection", "desc");

        $this->addViewData($filter, "filter");
        $this->addViewData($statistics, "statistics");
        $this->render();
    }

}
