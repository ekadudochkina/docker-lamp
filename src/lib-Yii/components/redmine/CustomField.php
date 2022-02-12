<?php

namespace Hs\Redmine;

/**
 * Дополнительные поля для сущностей
 *
 * @package Hs\Redmine
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class CustomField extends \RemappedActiveRecord
{

    public $type;
    public $title;

    const TYPE_PROJECT = "ProjectCustomField";
    const TYPE_ISSUE = "IssueCustomField";
    const TYPE_USER = "UserCustomField";

    /**
     * Получение массива конвертации имен полей
     * @return string[]
     */
    public function getMappings()
    {
        $arr = [];
        $arr["name"] = "title";

        return $arr;
    }

    public function tableName()
    {
        return "custom_fields";
    }

}
