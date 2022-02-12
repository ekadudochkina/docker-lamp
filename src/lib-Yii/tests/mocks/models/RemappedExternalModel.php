<?php

/**
 * Обеъект модели, которая имеет отличающиеся названия полей от базы данных. Изспользуется в тестах.
 *  
 * @property RemappedExternalModel $parent Родитель
 * 
 * @see RemappedActiveRecordTest
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class RemappedExternalModel extends RemappedActiveRecord
{

    public $title;
    public $parentId;

    /**
     * Возвращает карту полей
     * Данную функцию необходимо переопределять в классах наследниках
     * 
     * @return String[] Массив где ключами являются старые названия полей, а значениями - новые
     */
    public function getMappings()
    {
        $arr = [];
        $arr["full_name"] = "title";
        $arr["parent_id"] = "parentId";
        return $arr;
    }

    public function tableName()
    {
        return "externalmodels";
    }

    public function relations()
    {
        $arr = parent::relations();
        $arr['parent'] = [self::BELONGS_TO, "RemappedExternalModel", "parentId"];
        return $arr;
    }

}
