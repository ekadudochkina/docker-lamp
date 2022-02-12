<?php
/**
 * Форма регистрации нового пользователя.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @deprecated
 * @see SimpleRegistrationForm
 * @package Hs\Forms
 */
class RegistrationForm extends ModelForm
{
	/**
	 * Флаг того, что пользователь согласен с правилами
	 * @var Bool
	 */
	public $acceptRulesFlag;
	/**
	 * Имя пользователя
	 * @var String
	 */
        public $fullname;
	/**
	 * Имейл
	 * @var String
	 */
        public $email;
	/**
	 * Логин (имя пользователя)
	 * @var String
	 */
        public $name;
	/**
	 * Пароль
	 * @var String
	 */
        public $password;
	/**
	 * Подтверждение пароля
	 * @var String
	 */
	public $passwordConfirm;
	
	/**
	 * Делать проверку с подтверждение пароля. 
	 * Если True, то пользователь должен ввести пароль повторно в поле passwordConfirm.
	 * 
	 * @var Bool 
	 */
	public $requirePasswordConfirm = false;
	
	/**
	 * Проверять согласие с правилами. Если true, то в верстке должна быть галочка acceptRulesFlag.
	 * @var Bool 
	 */
	public $requireAcceptRules = true;
           
	public function rules()
	{
            $arr = array();
            $arr[] = array('fullname, name,email,password,passwordConfirm,acceptRulesFlag', 'safe');
	    if($this->requireAcceptRules){
		$arr[] = array('acceptRulesFlag', 'required');
		$arr[] = array('acceptRulesFlag', 'compare','compareValue'=>1);
	    }
         
	    if($this->requirePasswordConfirm){
		 $arr[] = array('password', 'comparePasswordsValidator');
	    }
            $ret = array_merge($arr,parent::rules());
            return $ret;
	}    
	
	/**
	 * Валидатор сравнения паролей
	 */
	public function comparePasswordsValidator(){
	    if($this->password != $this->passwordConfirm)
		$this->addError("passwordConfirm", "Passwords not match");
	}
}
        