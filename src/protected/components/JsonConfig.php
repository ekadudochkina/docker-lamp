<?php

/**
 * Description of JsonConfig
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class JsonConfig
{
    protected $path;
    protected $data;

    public function loadJson($content)
    {
        $this->data = json_decode($content, true);
    }
 
    public function toJson()
    {
        $content = json_encode($this->data, JSON_PRETTY_PRINT);
        return $content;
    }

    public function getProperties()
    {
        $result = [];
        foreach ($this->data as $key => $value)
        {
            $prop = $this->createPropertyObject($key);
            $result[] = $prop;
        }
        return $result;
    }

    public function getProperty($name)
    {
        if(!isset($this->data[$name]))
        {
            throw new Exception("Property '$name' does not exist");
        }
        
        $prop = $this->createPropertyObject($name);
        return $prop;
    }
    
    public function getValue($name)
    {
        return $this->getProperty($name)->getValue();
    }

    public function getChangesFromRequest(BaseController $ctrl)
    {
        $req = $ctrl->getRequest();
        $props = $this->getProperties();
        foreach ($props as $prop)
        {
            $val = $req->getParam($prop->getName(), null);
            if ($val === null)
            {
                continue;
            }
            if ($prop->getType() == ConfigProperty::TYPE_BOOLEAN)
            {
                $prop->setValue($val == "1" ? true : false);
            } else
            {
                $prop->setValue($val);
            }
            $this->syncProp($prop);
        }
    }

    protected function syncProp(ConfigProperty $prop)
    {
        $row = &$this->data[$prop->getName()];
        $row["value"] = $prop->getValue();
    }

    protected function createPropertyObject($key)
    {
        $value = $this->data[$key];
        $type = isset($value["type"]) ? $value["type"] : ConfigProperty::TYPE_STRING;
        $description = isset($value["description"]) ? $value["description"] : "";
        $prop = new ConfigProperty($key,$value["value"],$type,$description);
        return $prop;
    }

}