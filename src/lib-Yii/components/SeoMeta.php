<?php

/**
 * Модель нужна для тегов по умолчанию.
 * Класс SeoMeta наследуется от интерфейса ISeoMeta
 * Конструктор применяет 2 параметра $description и  $keywords
 * Реализация происходит через функции.
 * 
 * @package Hs
 */
class SeoMeta implements ISeoMeta
{

    public $description;
    public $keywords;

    public function __construct($description, $keywords)
    {
        $this->description = $description;
        $this->keywords = $keywords;
    }

    /**
     * Получение содержимого тега Description.
     * Описание страницы, отображаемого в поисковике.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Получение содержимого тега Keywords.
     * Описание ключевых слов и фраз через запятую для поисковика.
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

}
