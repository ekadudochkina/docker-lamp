<?php
namespace Hs\Logs;

/**
 * Пустой лог
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class EmptyLogger implements ILogger
{
    public function log($msg, $level = \CLogger::LEVEL_INFO)
    {
        
    }
}
