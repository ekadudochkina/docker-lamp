<?php

/**
 * Description of DbSafeActiveRecord
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class DbSafeActiveRecord extends ActiveRecord
{

    /**
     * @param ActiveRecord $model
     * @return CDbCriteria
     * @throws Exception
     */
    public function createCriteriaForRelation(ActiveRecord $model, $relation = null)
    {
        $relationKey = null;
        $relations = $this->relations();
        foreach ($relations as $key => $value)
        {
            if(get_class($model) == $value[1] && ($relation == null || $key == $relation))
            {
                if ($relationKey != null)
                {
                    bug::drop($relations,$model,$relationKey);
                    throw new Exception("Please specify relation, it's ambigious.");
                }
                $relationKey = $value[2];
            }
        }

        if (!$relationKey)
        {
            throw new Exception("Relation for class for class'" . get_class($model) . "' not found");
        }
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([$relationKey => $model->getPk()]);
        return $criteria;
    }

    static function f($value,$alias = null)
    {
        return $alias ? $alias.".".$value : $value;
    }
    static function r($value)
    {
        return $value;
    }

}
