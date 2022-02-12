<?php
/**
 * Класс базового контроллера, который можно инстанциировать в тестах.
 * на данный момент (07/01/17) контсруктор вызывает ошибки
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class BaseControllerMock extends BaseController
{
   public function __construct()
   {
       //parent::__construct($id, $module);
   }
}
