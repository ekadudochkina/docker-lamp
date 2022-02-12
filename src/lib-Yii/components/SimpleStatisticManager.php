<?php

/**
 * Простая статистика сайта
 * 
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 * @package Hs
 */
class SimpleStatisticManager {

    /**
     * Метод который собирает статистику и сохраняет в БД
     * 
     * @return Bool True 
     */
    public static function collectInfo() {
        $ip = EnvHelper::getClientIp();
        $url = $_SERVER["REQUEST_URI"];
        if($url == "/"){
            $url = "/site/index";
        }
        else{
          $url = $_SERVER["REQUEST_URI"];
        }        
        $date = DateTimeHelper::timestampToMysqlDateTime();
        $accessLog = new AccessLog();
        $accessLog->ip = $ip;
        $accessLog->url = $url;
        $accessLog->visitDate = $date;

        $accessLog->save();
        return true;
    }

    /**
     * Метод который подсчитывает количество пользователей находящихся на сайте
     * 
     * @return Integer Количество пользователей на сайте в данный момент
     */
    public static function userNumberOnSite() {
        $time = time();
        $differenceTime = $time - (5 * 60);
        $differenceDateTime = DateTimeHelper::timestampToMysqlDateTime($differenceTime);
        $criteria = new CDbCriteria;
        $criteria->group = 'ip';
        $criteria->addCondition('visitDate >= :visitDate');
        $criteria->params = array(':visitDate' => $differenceDateTime);
        $visits = AccessLog::model()->count($criteria);
        $userNumber = $visits;
        return $userNumber;
    }

    /**
     * Метод который подсчитывает количество пользователей посетивших сайт за день
     * 
     * @return Integer Количество пользователей на сайте за день
     */
    public static function userNumberOnSitePerDay() {
        $time = time();
        $differenceTime = $time - (60 * 60 * 24);
        $differenceDateTime = DateTimeHelper::timestampToMysqlDateTime($differenceTime);

        $criteria = new CDbCriteria;
        $criteria->group = 'ip';
        $criteria->addCondition('visitDate >= :visitDate');
        $criteria->params = array(':visitDate' => $differenceDateTime);
        $visits = AccessLog::model()->count($criteria);

        $userNumber = $visits;
        return $userNumber;
    }

    /**
     * Метод который подсчитывает количество пользователей посетивших сайт за неделю
     * 
     * @return Integer Количество пользователей на сайте за неделю
     */
    public static function userNumberOnSitePerWeek() {
        $time = time();
        $differenceTime = $time - (60 * 60 * 24 * 7);
        $differenceDateTime = DateTimeHelper::timestampToMysqlDateTime($differenceTime);
        $criteria = new CDbCriteria;
        $criteria->group = 'ip';
        $criteria->addCondition('visitDate >= :visitDate');
        $criteria->params = array(':visitDate' => $differenceDateTime);
        $visits = AccessLog::model()->count($criteria);
        $userNumber = $visits;
        return $userNumber;
    }
    
    /**
     * Метод который подсчитывает количество пользователей посетивших сайт за неделю
     * 
     * @return Integer Количество пользователей на сайте за неделю
     */
    public static function userNumberOnSitePerMonth() {
        $time = time();
        $differenceTime = $time - (60 * 60 * 24 * 30);
        $differenceDateTime = DateTimeHelper::timestampToMysqlDateTime($differenceTime);
        $criteria = new CDbCriteria;
        $criteria->group = 'ip';
        $criteria->addCondition('visitDate >= :visitDate');
        $criteria->params = array(':visitDate' => $differenceDateTime);
        $visits = AccessLog::model()->count($criteria);
        $userNumber = $visits;
        return $userNumber;
    }

}
