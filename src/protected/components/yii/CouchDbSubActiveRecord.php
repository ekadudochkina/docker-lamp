<?php

/**
 * Description of CouchDbActiveRecord
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class CouchDbSubActiveRecord extends ActiveRecord
{
    protected $data = null;

    abstract public function getAttributeNames();

    public function findByAttributes($attributes, $condition = '', $params = array())
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }

    public function countByAttributes($attributes, $condition = '', $params = array())
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }

    public function findAllByAttributes($attributes, $condition = '', $params = array())
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }

    public function findAll($condition = '', $params = array())
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }

    public function findByPk($pk, $condition = '', $params = array())
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }

    public function map($data)
    {
        //bug::Drop($data);
        // $this->type = $data["type"];
        $this->data = $data;
        $attrs = $this->getAttributeNames();
        foreach ($attrs as $attr)
        {
            if (isset($data[$attr]))
            {
                $this->$attr = $data[$attr];
            }
        }

        $relations = $this->relations();
        foreach ($relations as $key => $arr)
        {
            $type = $arr[0];

            $class = $arr[1];
            $dataKey = $arr[2];
            if(!isset($data[$dataKey]))
            {
                continue;
            }
            
            if ($type == self::HAS_ONE)
            {
                $this->setOne($key, $class, $data[$dataKey]);
            } else if ($type == self::HAS_MANY)
            {
                $this->setMany($key, $class, $data[$dataKey]);
            }
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __call($name, $parameters)
    {
        return call_user_func_array($this->$name, $parameters);
    }

    public function __construct($scenario = 'insert')
    {
        
    }

    public function getPrimaryKey()
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data. It can't have an Id");
    }

    public static function model($className = null)
    {
        $class = get_called_class();
        return new $class(null);
    }

    public function setMany($key, $class, $data)
    {
        $this->$key = [];
        foreach ($data as $subKey => $subData)
        {
            $model = new $class;
            $model->map($subData);
            $arr = &$this->$key;
            $arr[$subKey] = $model;
        }
    }

    public function setOne($key, $class, $data)
    {
        $this->$key = null;
        $model = new $class;
        $model->map($data);
        $this->$key = $model;
    }

    public function getAttributes($names = true)
    {
        $arr = [];
        $names = $this->getAttributeNames();
        foreach ($names as $name)
        {
            $arr[$name] = $this->$name;
        }
        return $arr;
    }

    public function getData()
    {
        return $this->data;
    }

    public function save($runValidation = true, $attributes = null,$saveRelations = true)
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }

    public function recalculateData()
    {
        $data = $this->getData();
        $fields = $this->getAttributes();
        foreach ($fields as $key => $value)
        {
            $data[$key] = $value;
        }
        $many = [];
        foreach ($this->relations() as $key => $value)
        {
            $type = $value[0];
            $class = $value[1];
            $dataKey = $value[2];
            if ($type == self::HAS_ONE)
            {
                $submodel = $this->$key;
                if(!$submodel)
                {
                    continue;
                }
                $newData = $submodel->recalculateData();
                $data[$dataKey] = $newData;
            }
            if ($type == self::HAS_MANY)
            {
                $newData = [];
                foreach ($this->$key as $subKey => $object)
                {
                    $row = $object->recalculateData();
                    $newData[$subKey] = $row;
                }
                $data[$dataKey] = $newData;
            }
        }
        return $data;
    }

    public function getFirstError()
    {
        $errors = $this->getErrors();
        if (!$errors)
        {
            return "Unknown Error";
        }

        $first = ArrayHelper::getFirst($errors);
        return $first;
    }

    public function delete()
    {
        throw new Exception("CouchDbSubActiveRecords is only container for data");
    }
    
    public function isNew()
    {
       return false;
    }

}
