<?php
namespace Hs\Shop;
/**
 * Интерфейс товара
 *
 * @package Hs\Shop
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
interface IShoppingCartItem
{
    /**
     * Стоимость товара
     * @return Number
     */
    public function getPrice();
    
    /**
     * Название товара
     * @return String
     */
    public function getTitle();
    
    /**
     * Описание товара
     * @return String
     */
    public function getDescription();
    
    /**
     * Ссылка на изоображение товара
     * @return String
     */
    public function getImageUrl();
    
    /**
     * Получение идектификатора товара
     * @retun Number
     */
    public function getPk();
}
