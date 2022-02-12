<?php
/**
 * Модель, поля которой не созданны по правилам конвенции Home Studio.
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ExternalModel extends ActiveRecord
{
    /**
     * Идентификтатор
     * 
     * @var Integer
     */
    public $id;
    
    /**
     * Поле 1
     * @var String
     */
    public $full_name;
    
    /**
     * Поле 2
     * @var Integer
     */
    public $parent_id;
    
    public function tableName()
    {
        return "externalmodels";
    }
}
