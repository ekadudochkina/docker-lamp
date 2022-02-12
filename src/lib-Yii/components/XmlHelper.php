<?php
/**
 * Хелпер для работы с XML.
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class XmlHelper
{
    /**
     * Получение значения инпута
     * 
     * @param SimpleXMLElement $xml Объект HTML
     * @param String $name Имя инпута
     */
    public static function getInputValue($xml,$name)
    {
        $selector = "//input[@name='$name']";
        $input = static::getFirst($xml, $selector);
        if(!$input)
            throw new Exception ("Can't find input with selector '$selector' ");
        $value = $input->attributes()['value']."";
        return $value;
    }
    
    /**
     * Получение одного XML элемента XPATH запросом
     * 
     * @param SimpleXMLElement $xml Объект HTML
     * @param String $selector Селектор XPATH
     * @return SimpleXMLElement
     */
    public static function getFirst($xml,$selector)
    {
        $elements = $xml->xpath($selector);
        if(!$elements)
            return null;
        $result = $elements[0];
        return $result;
    }
}
