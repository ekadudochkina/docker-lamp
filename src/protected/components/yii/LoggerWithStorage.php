<?php


class LoggerWithStorage extends CLogger
{

    protected $storage = [];
    protected $storageLimit = 50000;
    public $autoFlush = 1;
    public $autoDump = true;


    public function log($message, $level = 'info', $category = 'application')
    {
        if(count($this->storage) > $this->storageLimit)
        {
            $this->storage = [];
        }
        return parent::log($message, $level, $category);
    }

    public function flush($dumpLogs = false)
    {
       $this->storage[] = ArrayHelper::getLast($this->getLogs());
       return parent::flush($dumpLogs);
    }

    public function getStoredLogs()
    {
        $storage = $this->storage;
        return $storage;
    }

    public function clearStorage()
    {
        $this->storage = [];
    }

}