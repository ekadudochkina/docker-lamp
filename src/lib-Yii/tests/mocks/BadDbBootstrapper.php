<?php

/**
 * Бутстраппер с плохой базой данных. С таким бутстраппером невозможно создать подключение.
 * 
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class BadDbBootstrapper extends BootstrapperMock
{

    /**
     * Получение массива конфигурации приложения
     * 
     * @return Mixed[]
     */
    public function getConfig()
    {
        $conf = parent::getConfig();
        $conf['components']['db']['username'] = "dasdasdadsad";
        return $conf;
    }

}
