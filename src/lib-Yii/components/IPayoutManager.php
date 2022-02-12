<?php

/**
 * Интерфейс для классов менеджеров платежных систем
 * 
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * @package Hs
 */
interface IPayoutManager
{
    /**
     * Метод формирует выплаты от продовца покупателям.
     * 
     * @param BasePayout $payout выплата которую нужно провести
     * @return boolean 
     */
    public function executePayout(BasePayout $payout);
}

