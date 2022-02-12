<?php

/**
 * Контроллер для обработки режима обслуживания
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MaintenanceMockController extends BaseController
{

    private $processed = true;

    public function beforeAction($action)
    {
        if (!$this->processed)
        {
            return;
        }
        $result = parent::beforeAction($action);

        return $result;
    }

    /**
     * Проверяет является ли вебсайт общедоступным.
     * Если веб-сайт недоступен, то зайти могут только админы
     */
    public function processMaintance()
    {
        Yii::import("root.lib-Yii.tests.mocks.maintenance.*");
        $manager = new MaintenanceManagerMock();
        $result = $manager->process($this);
        $this->processed = $result;
        return $result;
    }

    /**
     * Экшен по-умолчанию
     */
    public function actionIndex()
    {
        echo "Index";
    }

}
