<?php

/**
 * Объект, который обладает свойствами для публикации его в социальных сетях и скайпе.
 * Когда страница с таким объектом публикуется, отображается картинка и заголовки красивым образом.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
interface IOpenGraphObject
{
    /**
     * Получение заголовка для публикации
     */
    public function getTitle();
    
    /**
     * Получение короткого описания публикации. 
     * Социальные сети обычно сокращают публикации.
     */
    public function getShortDescription();
    
    /**
     * Получение изоображения для публикации
     * @param CController $controller Контроллер необходим для создания Url
     */
    public function getImageUrl(CController $controller);
    
    /**
     * Получение Url публикации
     * @param CController $controller Контроллер необходим для создания Url
     */
    public function getUrl(CController $controller);
}
