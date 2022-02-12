<?php
/**
 * Базовый класс для менеджеров социальных сетей
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Social
 */
abstract class BaseSocialManager
{
    /**
     * Проводит авторизацию в социальной сети
     */
    abstract public function authorize();
    
}
