<?php
namespace Hs\Actions;

/**
 * Базовый класс для экшенов блога в админке
 *
 * @package Hs\Actions
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class BlogAction extends \ViewAction
{
   public $hiddenFields = [];
   
   
   /**
    * Проверяет нужно ли отображать поле модели
    * 
    * @param String $field
    * @return boolean True, если поле не должно отображаться
    */
   public function isHidden($field)
   {
       if(in_array($field,$this->hiddenFields))
       {
           return true;
       }
       return false;
   }

}
