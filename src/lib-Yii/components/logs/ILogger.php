<?php
namespace Hs\Logs;

/**
 * Интерфейс логирования
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
interface ILogger
{
   /**
     * Logs a message.
     * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
     * and may be recorded in different media, such as file, email, database, using
     * {@link CLogRouter}.
     * @param string $msg message to be logged
     * @param string $level level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
     */
    public function log($msg, $level = CLogger::LEVEL_INFO);
}
