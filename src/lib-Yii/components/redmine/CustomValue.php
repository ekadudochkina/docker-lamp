<?php

namespace Hs\Redmine;

/**
 * Значения дополнительных полей для сущностей
 *
 * @package Hs\Redmine
 * @property Hs\Redmine\CustomField $field Дополнительное поле
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class CustomValue extends \RemappedActiveRecord
{

    public $value;
    public $objectId;
    public $fieldId;

    /**
     * Получение массива конвертации имен полей
     * @return string[]
     */
    public function getMappings()
    {
        $arr = [];
        $arr["custom_field_id"] = "fieldId";
        $arr["customized_id"] = "objectId";
        return $arr;
    }

    public function relations()
    {
        $arr = parent::relations();
        $arr["field"] = [self::BELONGS_TO, "Hs\Redmine\CustomField", "custom_field_id"];
        return $arr;
    }

    public function tableName()
    {
        return "custom_values";
    }

}
