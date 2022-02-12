<?php
/**
 * Объект, который представляет собой кусок кода, например поле класса или метод, возможно сам класс.
 * В целом, данный класс является именованным кортежем. Используется для генерации кода.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Models
 */
class CodeEntity
{
    public $code = null;
    public $commentary = null;
    public $autogenerated = false;
    protected $name;
    
    const TYPE_FIELD = "TYPE_FIELD";
    const TYPE_METHOD = "TYPE_METHOD";
    
    public function __construct($name,$type)
    {
        $this->name = $name;
        $this->type = $type;
        $correct = ReflectionHelper::checkConstant($this,$type,"TYPE_");
        if(!$correct)
            throw new Exception("'$type' неверное значение константы");
    }
    
    /**
     * Получает название куска кода (имя метода или поля)
     * 
     * @return String Имя поля / метода
     */
    public function getName()
    {
        return $this->name;
    }
   
}