<?php
/**
 * Интерфейс подписки на платный сервис
 * 
 * @see IService
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * @package Hs
 */
interface ISubscription{
    
    /**
     * Метод вернет идетнификатор подписчика
     * @return  Integer
     */
    public function getPayerId();
    
    /**
     * Метод вернет модель сервиса на который мы делаем подписку
     * @return  IService
     */
    public function getService();
    
    /**
     * Метод который сохраняет данные о подписке. Вызывается на старте формирования подписки
     * @return  SimpleSubscription
     */  
    public function start();
    
    /**
     * Метод активирует подписку. 
     * @param String $agreementId  Уникальный идентификатор подписки на сайте paypal
     * @return  SimpleSubscription
     */
    public function turnOn($agreementId);
    
      /**
     * Метод отменяет подписку. 
     * @return  SimpleSubscription
     */
    public function turnOff();
    
    /**
     * Метод отменяет подписку когда пользователь ее еще не активировал, то есть в момент подтверждения подписки на сайте paypal 
     * @return  SimpleSubscription
     */
    public function fail();

}