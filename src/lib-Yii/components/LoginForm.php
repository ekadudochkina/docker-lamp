<?php

/**
 * Форма для логина
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @deprecated 
 * @see SimpleLoginForm
 * @package Hs\Forms
 */
class LoginForm extends CFormModel
{

    /**
     * Логин
     * @var String
     */
    public $name;

    /**
     * Пароль
     * @var String
     */
    public $password;

    /**
     * Флаг "Запомнить меня"
     * @var Bool
     */
    public $rememberMe;

    /**
     * Сущность реквизитов пользователя
     * @var UserIdentity
     */
    private $_identity;

    /**
     * Метод аутентификации пользователя. Проверяет позволяют ли реквизиты пользователя сделать логин
     */
    public function authenticate($attribute, $params)
    {

        if (!$this->hasErrors())
        {
            $this->_identity = new UserIdentity($this->name, $this->password);
            if (!$this->_identity->authenticate())
            {
                //В любом случае пароль плохой, ибо нехуй хакерам подсказки давать
                switch ($this->_identity->errorCode)
                {
                    case UserIdentity::ERROR_USERNAME_INVALID : $this->addError('password', 'Incorrect  password.');
                        break;
                    case UserIdentity::ERROR_PASSWORD_INVALID : $this->addError('password', 'Incorrect  password.');
                        break;
                }
            }
        }
    }

    /**
     * Вход в систему.
     * 
     * @return {Boolean} True, если вход произведен успешно
     */
    public function login()
    {
        if ($this->_identity === null)
        {
            $this->_identity = new UserIdentity($this->name, $this->password);
        }
        $this->_identity->authenticate();

        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE)
        {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        } else
            return false;
    }

    /**
     * Returns the validation rules for attributes.
     *
     * @return array validation rules to be applied when {@link validate()} is called.
     * @see scenario
     */
    public function rules()
    {
        $arr = parent::rules();
        $arr[] = array('name, password', 'required');
        $arr[] = array('rememberMe', 'boolean');
        $arr[] = array('password', 'authenticate');
        return $arr;
    }

}
