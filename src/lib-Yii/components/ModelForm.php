<?php

/**
 * Класс абстрагирующий логику форм от логики моделей. 

 * Суть заключается в том, что данная форма принимает другую форму в конструктор и наследует ее правила валидации.
 * Таким образом, мы получаем возможность не дублировать валидаторы. 

 * Представь форму регистрации: форма регистрации будет дублировать часть валидаторов пользователя.
 * 
 * Используя ModelForm можно вносить в нее только уникальные для формы правила, а правила модели держать в модели.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
class ModelForm extends FormModel
{

    protected $_model = null;

    public function __construct(CActiveRecord $model = null, $scenario = '')
    {
        $this->_model = $model;
        parent::__construct($scenario);
    }

    /**
     * Перегрузка магического метода получения поля.
     * В данном случае мы возвращаем либо поле формы, либо поле вложенной модели.
     * 
     * @param String $name Имя поля
     * @return Mixed Содержимое поля формы или модели
     */
    public function __get($name)
    {

        try
        {
            parent::__get($name);
        } catch (Exception $ex)
        {
            return $this->_model->$name;
        }
    }

    /**
     * Sets the attribute values in a massive way.
     * @param array $values attribute values (name=>value) to be set.
     * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
     * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
     * @see getSafeAttributeNames
     * @see attributeNames
     */
    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        //Чтобы Yii не сыпал варнингами, нужно устанавливать только safe аттрибуты
        $safe = $this->_model->getSafeAttributeNames();
        $newVals = array();
        foreach ($safe as $name)
            if (isset($values[$name]))
                $newVals[$name] = $values[$name];

        $this->_model->setAttributes($newVals, $safeOnly);
    }

    /**
     * Возвращает модель созданную формой.
     * 
     * @return ActiveRecord Модель формы
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Performs the validation.
     *
     * This method executes the validation rules as declared in {@link rules}.
     * Only the rules applicable to the current {@link scenario} will be executed.
     * A rule is considered applicable to a scenario if its 'on' option is not set
     * or contains the scenario.
     *
     * Errors found during the validation can be retrieved via {@link getErrors}.
     *
     * @param array $attributes list of attributes that should be validated. Defaults to null,
     * meaning any attribute listed in the applicable validation rules should be
     * validated. If this parameter is given as a list of attributes, only
     * the listed attributes will be validated.
     * @param boolean $clearErrors whether to call {@link clearErrors} before performing validation
     * @return boolean whether the validation is successful without any error.
     * @see beforeValidate
     * @see afterValidate
     */
    public function validate($attributes = null, $clearErrors = true)
    {
        $result = parent::validate($attributes, $clearErrors);
        $model = $this->_model;
        $model->clearErrors();
        $model->setAttributes($this->getAttributes());
        $modelResult = $model->validate($attributes, $clearErrors);
        $errors = $model->getErrors();
        $this->addErrors($errors);
        return $result && $modelResult;
    }

    /**
     * Сохраняет внутреннюю модель
     * 
     * @return boolean True, в случае успеха
     */
    public function save()
    {
        if ($this->validate())
        {
            $model = $this->_model;
            $model->clearErrors();
            $model->setAttributes($this->getAttributes());
            $ret = $model->save();
            return $ret;
        } else
            return false;
    }

    /**
     * Returns the first error of the specified attribute.
     * @param string $attribute attribute name.
     * @return string the error message. Null is returned if no error.
     */
    public function getError($attribute)
    {
        $error1 = $this->getModel()->getError($attribute);
        if ($error1)
        {
            return $error1;
        }
        $error = parent::getError($attribute);
        return $error;
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     */
    public function getErrors($attribute = null)
    {
        $errors = $this->getModel()->getErrors($attribute);
        $errors2 = parent::getErrors($attribute);
        $result = array_merge($errors, $errors2);
        return $result;
    }

}

?>
