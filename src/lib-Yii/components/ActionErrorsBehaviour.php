<?php

/* Список магических методов, которые можно скопировать и подставить в модель
 * 
 * @method String[] getActionErrors() Возвращает все ошибки действия (или коды, если сайт мультиязычный).
 * @method String getActionError() Возвращает первую ошибку метода или ошибку по-умолчанию (или код, если сайт мультиязычный).
 * @method String addActionError(String $text) Добавение ошибки в модель (или кода, если сайт мультиязычный).
 * @method String getFirstError() Возвращает первую ошибку валидации или ошибку по-умолчанию.
 * @method Boolean hasActionErrors() Проверка, есть ли ошибки действий
 * @method Boolean mergeErrors(String $model) Забирает ошибки действий у модели и добавляет их себе
 */

/**
 * Поведение для добавления ошибок в модели.
 * Этот примерно тот же механизм, что и валидация, но ошибки относятся к бизнес логике, а не к полям.
 *
 * Мы запускаем какой-либо метод в модели, если он вернул false, значит произошла ошибка. Далее мы получаем эту ошибку при помощи
 * функции getActionError().
 *
 * Внутри модели добавление ошибок осуществляется методом $this->addActionError().
 *
 * Объект устроен так, что если модель не добавила ошибок, то он вернет ошибку по-умолчанию "Unknown error".
 *
 * <b>example</b>
 * <pre>
 * $notifcation = Notification::model()->findByPk($x);
 * $result = $notifcation->send();
 * if($result)
 *     $this->getUser()->setFlash("success","Notification successfuly sended");
 * else
 *     $this->getUser()->setFlash("error",$notification->getActionError());
 * </pre>
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
class ActionErrorsBehaviour extends CBehavior
{
    /**
     * Список ошибок
     * @var String[]
     */
    protected $actionErrors = array();

    /**
     * Ошибка по-умолчанию.
     * @var Sring
     */
    protected $errorPlaceholder = "Unknown error";

    /**
     * Добавение ошибки в модель
     *
     * @param String $error Текст ошибки (или код, если сайт мультиязычный)
     * @return Bool Всегда возвращает false
     */
    public function addActionError($error)
    {
        Yii::log("Action error: $error");
        $this->actionErrors[] = $error;
        return false;
    }

    /**
     * Возвращает все ошибки.
     *
     * @return String[] Список ошибок (или коды, если сайт мультиязычный)
     */
    public function getActionErrors()
    {
        return $this->actionErrors;
    }

    /**
     * Возвращает первую ошибку метода или ошибку по-умолчанию. (или код, если сайт мультиязычный)
     *
     * @return String Текст ошибки
     */
    public function getActionError()
    {
        $count = count($this->actionErrors);
        if ($count == 0)
            return $this->errorPlaceholder;
        return $this->actionErrors[0];
    }

    /**
     * Возвращает первую ошибку валидации или ошибку по-умолчанию.
     *
     * @return String Текст ошибки
     */
    public function getFirstError()
    {
        if ($this->hasActionErrors()) {
            return $this->getActionError();
        }

        $errors = $this->getOwner()->getErrors();
        if (empty($errors)) {
            return $this->errorPlaceholder;
        }

        $firstSet = ArrayHelper::getFirst($errors);
        $firstError = ArrayHelper::getFirst($firstSet);
        return $firstError;
    }

    /**
     * Проверка, есть ли ошибки действий
     *
     * @return Bool True, если есть
     */
    public function hasActionErrors()
    {
        $result = count($this->actionErrors) != 0;
        return $result;
    }

    /**
     * Объединяет ошибки действий у моделей
     *
     * @param Model $model Модель, ошибки которой, необходимо забрать
     */
    public function mergeActionErrors(CModel $model)
    {

        if ($model->hasActionErrors())
            return;

        $errors = $model->getActionErrors();
        foreach ($errors as $error)
            $this->addActionError($error);
    }

}
