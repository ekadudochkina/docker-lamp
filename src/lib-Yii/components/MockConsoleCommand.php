<?php

/**
 * Пустая консольная команда. Для хаков.
 * Просто в консольных командах есть вкусные методы для работы с консолью.
 * 
 * @todo Подумать как доставать вкусные методы по-другому
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Test\Mocks
 */
class MockConsoleCommand extends CConsoleCommand
{
    public function __construct()
    {
        parent::__construct("",null);
    }
}
