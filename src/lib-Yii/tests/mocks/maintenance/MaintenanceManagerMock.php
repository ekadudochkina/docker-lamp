<?php

/**
 * Менеджер технических работ, который не завершает работу скрипта
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MaintenanceManagerMock extends MaintenanceManager
{
    /**
     * Остановка работы скрипта
     */
    protected function end()
    {
        //не выключаемся
    }
}
