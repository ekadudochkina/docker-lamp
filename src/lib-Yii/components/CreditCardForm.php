<?php

/**
 * Модель кредитной карты
 *
 * @author  Kustarov Dmitriy
 * @package Hs\Forms
 * @todo Модель является незаконченной. Доделать правила валидации.
 */
class CreditCardForm extends FormModel
{

    /**
     * Имя на карте
     * @var String 
     */
    public $nameCard;

    /**
     * Номер карты
     * @var String 
     */
    public $cardNumber;

    /**
     * Месяц завершения срока действия
     * @var String 
     */
    public $month;

    /**
     * Год завершения срока действия
     * @var String 
     */
    public $year;

    /**
     * Трехзначный код, который написан с обратной стороны
     * @var String 
     */
    public $cvv;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array validation rules to be applied when {@link validate()} is called.
     * @see scenario
     */
    public function rules()
    {
        $arr = parent::rules();
        $arr[] = array('nameCard, cardNumber, month, year, cvv', 'required');
        return $arr;
    }

}
