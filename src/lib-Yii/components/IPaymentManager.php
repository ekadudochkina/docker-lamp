<?php

/**
 * Интерфейс для классов менеджеров платежных систем
 * 
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * @package Hs
 */
interface IPaymentManager
{
    /**
     * Первый этап оплаты.
     * В данном методе происходит формирования оплаты на сайте платежной системы.
     * 
     * @param BasePayment $payment Платеж который собственно и будет оплачен пользователем
     * @param String $route Маршрут в формате Controller/action на action контроллер который будет завершать платеж
     */
    public function startPayment(BasePayment $payment,$route);
    
    /**
     * Второй этап оплаты
     * На данном этапе происходит обработка платежа на стороне нашего сайта
     * 
     * @param BasePayment $payment Платеж который оплатил или не оплатил пользователь
     * @return Boolean B случае успешной оплаты вернет true, в случае неуспешной false
     */
    public function completePayment(BasePayment $payment);
}

