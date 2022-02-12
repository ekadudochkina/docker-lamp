<?php

require_once __DIR__ . "/../../../lib-Yii/Bootstraper.php";

/**
 * Бутстраппер специально для амазона
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class HTMLBootstrapper extends Bootstraper
{
    const MODE_PRODUCTION_LOCAL = "production_local";

    public function isProduction()
    {
//        if ($this->currentMode)
//        {
//            return $this->currentMode == self::MODE_PRODUCTION || $this->currentMode == self::MODE_PRODUCTION_LOCAL;
//        }
//
//        $queueServer = "4.9.0-2-amd64 #1 SMP Debian 4.9.18-1 (2017-03-30)";
//        $websiteServer = "4.9.0-2-amd64 #1 SMP Debian 4.9.18-1 (2017-03-30)";
//        if (strpos($this->machine,$queueServer) !== false || (strpos($this->machine,$websiteServer) !== false ))
//        {
//            return true;
//        }

        return parent::isProduction();
    }

    protected function generateConsoleLogRouteConfig()
    {
        $conf =  parent::generateConsoleLogRouteConfig();
        $conf["class"] =  "ConsoleLogRouteWithSql";
        return $conf;
    }

    public function getProductionModes()
    {
        $ret = parent::getProductionModes();
        $ret[] = self::MODE_PRODUCTION_LOCAL;
        return $ret;
    }

}
