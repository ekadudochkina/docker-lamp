<?php

class ConsoleLogRouteWithSql extends ConsoleLogRoute
{

    /**
     * @var WebLogRoute
     */
    private $processor;
    protected $temp = null;

    public function __construct()
    {
        $this->processor = new WebLogRoute();
    }

    protected function processLogs($logs)
    {
        //Чтобы WebLogRoute работал правильно, нужно чтобы профилированные команды шли парами
        if(StringHelper::hasSubstring($logs[0][0],"begin:system.db.CDbCommand"))
        {
            $this->temp = $logs[0];
            return;
        }
        else if($this->temp)
        {
            $logs = [$this->temp,$logs[0]];
            $this->temp = null;
            $filtered = $this->processor->prepareLogs($logs);
            $filtered[0][0] .= "\n";
        }
        else {
            $filtered = $logs;
        }

        return parent::processLogs($filtered);
    }

    protected function colorize($text, $level = null)
    {
        if($level == "profile")
        {
            $out = "[36m"; //Blue background
            return "\033$out" . "$text\033[0m";
        }
        return parent::colorize($text, $level);
    }
}