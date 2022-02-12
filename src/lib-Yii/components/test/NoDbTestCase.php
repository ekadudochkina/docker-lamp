<?php

namespace Hs\Test;

/**
 * Базовый класс для тестов, которым не нужно использовать базу данных
 *
 * @package Hs\Test
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class NoDbTestCase extends \Hs\Test\TestCase
{
 
    /**
     * Сброс базы данных. Функция пустая, так как БД в таких тестах отсутствует.
     */
    public function resetDb()
    {
        return;
    }

}
