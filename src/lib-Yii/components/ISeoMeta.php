<?php

/** 
 * Интерфейс для тегов SEO оптимизации на странице.
 * @package Hs
 */
interface ISeoMeta
{
    /**
     * Получение содержимого тега Description.
     * Описание страницы, отображаемого в поисковике.
     */
    public function getDescription();

    /**
     * Получение содержимого тега Keywords.
     * Описание ключевых слов и фраз через запятую для поисковика.
     */
    public function getKeywords();
}
