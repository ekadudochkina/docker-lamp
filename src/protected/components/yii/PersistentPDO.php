<?php


class PersistentPDO extends NestedPDO
{

    public function __construct($dsn, $username = null, $passwd = null, $options = null)
    {
//        $options[PDO::ATTR_PERSISTENT] = true;
        parent::__construct($dsn, $username, $passwd, $options);
    }
}