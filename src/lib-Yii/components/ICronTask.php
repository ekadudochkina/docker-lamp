<?php

/**
 * Интерфейс для задач крона. Такая задача должна быть расположена в компонентах проекта.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
interface ICronTask
{
    /**
     * Исполняет задачу.
     * todo решить вопрос с входными параметрами которые возможно нужны при вызове данного метода
     */
    public function execute();
}
