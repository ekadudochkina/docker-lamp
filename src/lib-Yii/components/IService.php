<?php
/**
 * Интерфейс сервиса на который можно сделать платную подписку, котрая снимает средства в переодически.
 * 
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * @package Hs
 */
interface IService{
    
    /**
     * Метод возвращает загаловок плетежа
     * @return String
     */  
    public function getTitle();
    
    /**
     * Метод возвращает цену за плетежа
     * @return String
     */
    public function getPrice();
    
    /**
     * Метод возвращает потомка SimpleSubscription
     * @return SimpleSubscription
     */
    public function createSubscriptionInstance();
    
    /**
     * Метод возвращает Уникальный идентификатор сервиса
     * @return Integer
     */
    public function getServiceId();
    
    /**
     * Возвращает период за подписку в течении которой она действует, например год, месяц, неделя 
     * @return String 
     */
    public function getPeriod();
    
    /**
     * Возвращает период за пробную подписку в течении которой она действует, например год, месяц, неделя 
     * @return String 
     */
    public function getTrialPeriod();
    
    /**
     * Возвращает число циклов в течении которых происходят регулярные списания за подписку
     * @return Integer 
     */
    public function getCycles();
    
    /**
     * Возвращает число циклов для пробной подписки в течении которых действует пробная подписка
     * @return Integer 
     */
    public function getTrialCycles();
    
    /**
     * Количество списаний  за период 
     * @return Integer 
     */
    public function getInterval();
    
    /**
     * Метод возвращает валюту
     * @return String
     */    
    public function getCurrency();
    
}