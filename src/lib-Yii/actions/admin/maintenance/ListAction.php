<?php

/**
 * Экшен режима обслуживания сайта
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
        $manager = new MaintenanceManager();
        $action = $this->getRequest()->getParam("action");
        switch ($action)
        {
            case 'disable' : $manager->turnOn();
                break;
            case 'enable' : $manager->turnOff();
                break;
            case 'delete' :
                $id = $this->getRequest()->getParam("id");
                $manager->removeIp($id);
                break;
        }
        if ($action)
        {
            $this->redirectToRoute("index");
        }

        //Добавление Ip
        $ip = $this->getRequest()->getParam("ip");
        if ($ip && $manager->addIp($ip))
        {
            $this->showSuccessMessage("Ip has been added");
            $this->redirectToRoute("index");
        }
        $this->addViewData($manager, "manager");
        $this->render();
    }

}
