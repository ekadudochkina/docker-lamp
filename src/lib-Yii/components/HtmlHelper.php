<?php

/**
 * Хелпер для работы с HTML кодом
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class HtmlHelper
{
    /**
     * Конвертирует строку HTMl в XML. 
     * Необходимо для поиска элементов или изменения DOM.
     * 
     * @param String $html Строка HTML кода
     * @return SimpleXMLElement
     */
    public static function toXml($html)
    {
       libxml_use_internal_errors(true);
       $doc = new DOMDocument();
       $doc->loadHTML($html);
       $sxml = simplexml_import_dom($doc);
       return $sxml;
    }
}
