<?php

/**
 * DataActiveRecord
 */
class DataActiveRecord extends ActiveRecord
{

    /**
     * Данные о запуске команды, которая создала данную модель
     * <b>Внешний ключ.</b>
     * 
     * @update RESTRICT
     * @delete RESTRICT
     * @var Integer
     * @autogenerated 07-02-2019
     */
    public $dataId;
    
}