<?php

/**
 * Форма для изменения пароля пользователя
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
class PasswordChangeForm extends FormModel
{

    /**
     * Старый пароль
     * 
     * @var String 
     */
    public $oldPassword;

    /**
     * Новый пароль
     * 
     * @var String 
     */
    public $newPassword;

    /**
     * Подтверждение нового пароля
     * 
     * @var String 
     */
    public $passwordConfirm;

    /**
     * Пользователь
     * @var IUser
     */
    protected $user = null;

    /**
     * @param IUser $user Пользователь.
     */
    public function __construct(IUser $user)
    {
        $this->user = $user;
        return parent::__construct('');
    }

    public function rules()
    {
        $ret = parent::rules();
        $ret[] = array("oldPassword,newPassword, passwordConfirm", "safe");
        $ret[] = array('oldPassword,newPassword, passwordConfirm', 'required');
        $ret[] = array('newPassword', 'match', 'pattern' => '/[a-zA-Z1-9]{5,}/', "message" => "Password should be atleast 5 letters long");
        $ret[] = array('oldPassword', 'validateOldPassword');
        $ret[] = array('paswordConfirm', 'validatePasswordConfirm');

        return $ret;
    }

    /**
     * Валидатор, который сравнивает введенные пароли
     */
    public function validatePasswordConfirm()
    {
        if ($this->passwordConfirm != $this->newPassword)
        {
            $this->addError("passwordConfirm", "Passwords do not match");
        }
    }

    /**
     * Валидатор, который сравнивает введенные пароли
     */
    public function validateOldPassword()
    {

        $pass = $this->user->encodePassword($this->oldPassword);

        if ($this->user->password != $pass)
        {
            $this->addError("oldPassword", "Current password is not valid");
        }
    }

    /**
     * Функция смены пароля
     * 
     * @return Bool True, в случае успеха
     */
    public function changePassword()
    {
        if (!$this->validate())
            return $this->addActionError($this->getFirstError());
        $this->user->setPassword($this->newPassword);
        $result = $this->user->save();
        return $result;
    }

    /**
     * Псевдоним для функции changePassword()
     * @deprecated
     */
    public function save()
    {
        return $this->changePassword();
    }

}
