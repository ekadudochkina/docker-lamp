<?php

/**
 * Помошник для работы с SQL кодом.
 * 
 * Именно кодом как строками, а не как с запросами. 
 * Например может мигрировать дамп для БД с другим синтаксисом.
 *
 * @todo Помошник не реализован полностью. Парсеры очень простые и не тестировались. Файл был написан, когда я конвертировал sqlite в mysql - чего добру пропадать.
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class SqlHelper
{

    /**
     * Парсит объявление таблицы и возвращает массив имен колонок
     * 
     * @param String $text Текст CREATE TABLE
     * @param Bool $skipKeys Пропускает определения ключей (иногда ключи могут быть определены вместе с полем, а иногда после)
     * @return String [] Массив имен колонок
     */
    public function parseCols($text, $skipKeys = true)
    {
	$matches = array();
	preg_match_all("/TABLE `.*?` \((.*)\)/", $text, $matches);
	$fieldsText = $matches[1][0];
	$parts = explode(", ", trim($fieldsText));
	$names = array();
	foreach ($parts as $part)
	{
	    if ($skipKeys && StringHelper::hasSubstring($part, " key", true))
		continue;
	    $subparts = explode(" ", trim($part));
	    $namestr = $subparts[0];
	    $name = trim($namestr, "`");
	    $names[] = $name;
	}
	//Debug::drop($matches,$text,$names);
	return $names;
    }

    /**
     * Читает строку с запросами создания таблиц и возвращает структуру полей
     * 
     * @param String $newStr Cтрока содержащее запросы на создание таблиц.
     * @param Bool $sqlite Флаг базы данных sqlite (Применяется если запросы для базы данных sqlite)
     * @return String[] Ассоциативный массив ключами которого являются названия баз данных, а значениями названия таблиц.
     */
    public function parseFile($newStr, $sqlite)
    {
	$newStr = str_replace("\n", "", $newStr);
	$matches = array();
	$x = preg_match_all("/TABLE.*?;/", $newStr, $matches);
	$fields = array();
	foreach ($matches[0] as $match)
	    $fields[$this->parseTableName($match)] = $this->parseCols($match, $sqlite);

    }

    /**
     * Узнает название таблицы из запроса на создание таблицы
     * 
     * @todo Функция идиотская. Необходимо убрать второй параметр и просто написать нормальное регулярное выражение
     * @param String $text Текст запроса
     * @param Bool $insert Флаг является ли запрос INSERT (идиотский параметр)
     * @return String Имя таблицы
     */
    public function parseTableName($text, $insert = false)
    {
	$matches = array();
	if ($insert)
	    preg_match_all("/INTO `(.*?)` V/", $text, $matches);
	else
	    preg_match_all("/TABLE `(.*?)` \(/", $text, $matches);
	$table = $matches[1][0];

	return $table;
    }
}
