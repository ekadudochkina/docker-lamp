<?php

namespace Hs\Redmine;

/**
 * Адрес электронной почты
 *
 * @package Hs\Redmine
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class Email extends \RemappedActiveRecord
{

    public $userId;
    public $value;

    /**
     * Получение массива конвертации имен полей
     * @return string[]
     */
    function getMappings()
    {
        $arr = [];
        $arr["user_id"] = "userId";
        $arr['address'] = "value";
        return $arr;
    }

    public function tableName()
    {
        return "email_addresses";
    }

}
