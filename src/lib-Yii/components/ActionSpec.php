<?php
/**
 * Описание кнопки во вьюшке админки
 *
 * @package Hs
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ActionSpec
{

    protected $title;
    protected $url;
    protected $class;
    protected $icon;
    protected $openOnNewPage;
    protected $shouldPutNewRowAfterAction = false;

    public function __construct($title, $url, $class, $icon = null,$openOnNewPage = false)
    {
        $this->title = $title;
        $this->url = $url;
        $this->class = $class;
        $this->icon = $icon;
        $this->openOnNewPage = $openOnNewPage;
    }

    /**
     * Есть ли у кнопки иконка
     * 
     * @return Bool
     */
    public function hasIcon()
    {
        return $this->getIcon() != null;
    }
    
    /**
     * Получает класс иконки
     * 
     * @return String
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Получение имени класса для кнопки
     * 
     * @return String
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Получение Url на который будет перенесен пользователь при нажатии на кнопку
     * 
     * @return String
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Получение надписи на кнопке
     * 
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function opensOnNewPage()
    {
        return $this->openOnNewPage;
    }

    public function isEndOfRow()
    {
        return $this->shouldPutNewRowAfterAction;
    }

    public function setEndOfRow()
    {
        $this->shouldPutNewRowAfterAction = true;
    }
}
