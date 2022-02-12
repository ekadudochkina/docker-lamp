<?php
/**
 * Хелпер для работы с Url
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class UrlHelper
{
    
    /**
     * Создает url с параметрами
     * 
     * @param String $base Имя сайта, например example.com/page
     * @param String[] $params Ассоциативный массив параметров
     */
    public static function createUrl($base,$params)
    {
        $paramString = static::createParams($params);
        $result = $base."?".$paramString;
        
        return $result;
    }
    
    /**
     * Создает список параметров вида "param=value&param2=value2"
     * 
     * @param String[] $data Ассоциативный массив параметров
     * @param Bool $encode Кодировать параметры в соответствии с HTTP протоколом
     * @return String Строка параметров для url
     */
    public static function createParams($data,$encode = true)
    {

        if($encode)
        {
            $newData = [];
            foreach($data as $key => $value)
            {
                $newData[urldecode($key)] = urlencode($value);
            }
            $data = $newData;
        }
        
        $string = CodeGenHelper::magicWrap(0,  array_keys($data),"=",  array_values($data));
        $parts = explode("\n",trim($string));
        $result =  join("&",$parts);
	//Debug::drop($data,$string,$parts);
        return $result;
    }
    
    /**
     * Получает параметры из канонического Url. (site.com/page?param=value&param2=value2)
     * 
     * @param String $url Url с параметрами
     * @return String[] Ассоциативный массив параметров, где ключами являются названия параметров, а значениями - значения
     */
    public static function getParamsFromUrl($url)
    {
        $result = array();
        $parts = explode("?",$url);
        if(count($parts) != 2)
            return $result;
        
        $paramPart = $parts[1];
        $paramValuePairs = explode("&",$paramPart);
        foreach($paramValuePairs as $paramAndValue)
        {
            $splat = explode("=",$paramAndValue);
            if(count($splat) != 2)
                return $result;
            
            $paramName = $splat[0];
            $paramValue = $splat[1];
            $result[$paramName] = $paramValue;
        }
        
        return $result;
    }

    /**
     * Поулчение зоны домена
     * 
     * @param String $url Адрес
     * @return String Зона, например ru или com
     */
    public static function getDomainZone($url)
    {
        $base = static::getBase($url);
        $parts = explode(".", $base);
        $result = $parts[count($parts) - 1];
        return $result;
    }


    /**
     * Получение домена
     * 
     * @param String $url Адрес
     * @return Домен вида, www.yandex.ru или home-studio.pro
     */
    public static function getDomain($url)
    {
        $noparams = static::stripParams($url);
        $protocol = static::getProtocol($noparams);
        $noprotocol = $protocol ? str_replace($protocol."://", "", $noparams) : $noparams;

        $parts = explode("/", $noprotocol);
        $result = $parts[0];

        return $result;
    }

    /**
     * 
     * @param String $url Адрес
     * @return String Url без строки параметров
     */
    public static function stripParams($url)
    {
        $parts = explode("?", $url);
        return $parts[0];
    }
    
    /**
     * Возвращает протокол ссылки
     * 
     * @param String $url Адрес
     * @return string Протокол, например "http" или "https"
     */
    public static function getProtocol($url)
    {
        $parts = explode("://", $url);
        if(count($parts) == 1)
        {
            return null;
        }
        $result = $parts[0];
        return $result;
    }

}
