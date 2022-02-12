<?php
/**
 * Description of ConfigProperty
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ConfigProperty extends CModel
{
    protected $name;
    protected $type;
    protected $value;
    protected $description;
    
    const TYPE_BOOLEAN = "bool";
    const TYPE_STRING = "string";
    
    public function __construct($name, $value,$type = ConfigProperty::TYPE_STRING, $description = "")
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->description = $description;
    }
    function getName()
    {
        return $this->name;
    }
    function getType()
    {
        return $this->type;
    }
    function getValue()
    {
        return $this->value;
    }
    function getDescription()
    {
        return $this->description;
    }
    public function attributeNames()
    {
        return ["name","value","description"];
    }
    public function setValue($val)
    {
        $this->value = $val;
    }
}
