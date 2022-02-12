<?php
/**
 * Базовый класс Сервисов. 
 * Класс есть прослойка между классом ActiveRecord и Service
 * 
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * @package Hs\Models
 */

abstract class SimpleService extends ActiveRecord implements IService
{

    /**
     * Метод возвращает загаловок плетежа
     * @return String
     */ 
    abstract public function getTitle();
    
    /**
     * Метод возвращает цену за плетежа
     * @return String
     */
    abstract public function getPrice();
    
    /**
     * Метод возвращает потомка SimpleSubscription
     * @return SimpleSubscription
     */
    abstract public function createSubscriptionInstance();
    
     /**
     * Метод возвращает Уникальный идентификатор сервиса
     * @return Integer
     */
    public function getServiceId(){
        return $this->getPk();
    }

    /**
     * Возвращает период за подписку в течении которой она действует, например год, месяц, неделя 
     * @return String 
     */
    public function getPeriod(){
        return 'month';
    }
    
    /**
     * Возвращает период за пробную подписку в течении которой она действует, например год, месяц, неделя 
     * @return String 
     */
    public function getTrialPeriod(){
        return $this->getPeriod();
    }
    
    /**
     * Возвращает число циклов в течении которых происходят регулярные списания за подписку
     * @return Integer 
     */
    public function getCycles(){
        return 12;
    }
    
    /**
     * Возвращает число циклов для пробной подписки в течении которых действует пробная подписка
     * @return Integer 
     */
    public function getTrialCycles(){
        return 0;
    }
    
    /**
     * Количество списаний  за период 
     * @return Integer 
     */
    public function getInterval(){
        return 1;
    }
    
    /**
     * Метод возвращает валюту платежа
     * @return String
     */   
    public function getCurrency(){
        return 'RUB';
    }
}