<?php

/**
 * Простая форма для логина.
 * 
 * Для ее работы требуется контроллер и объект IUserProvider. BaseUser его реализует.
 * 
 * @see IUserProvider
 * @see IUser
 * @see BaseUser
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
class SimpleLoginForm extends FormModel
{
   /**
     * Логин
     * @var String
     */
    public $login;

    /**
     * Пароль
     * @var String
     */
    public $password;

    /**
     * Флаг "Запомнить меня".
     * Eсли данный флаг не установлен то пользователь будет залогинен только на время текущей сессии.
     * @var Bool
     */
    public $rememberMe = false;

    /**
     * На сколько оставлять пользователя, если он просит его запомнить.
     * Значение должно быть выраженно в секундах
     * 
     * @var Integer
     */
    public $duration = 2592000; //30 дней
    
    /**
     * Поставщик пользователей
     * 
     * @var  IUserProvider 
     */
    protected $provider = null;
    
    /**
     * Контроллер.
     * 
     * @var BaseController 
     */
    protected $controller= null;
    
    /** 
     * @param IUserProvider $provider провайдер пользователей. Можно обнаружить в контроллере.
     * @param BaseController $controller Ссылка на класс контроллера который необходим для формирования URL.
     * @return type
     */
    public function __construct(IUserProvider $provider, BaseController $controller)
    {
        $this->provider = $provider;
        $this->controller = $controller;
        
        return parent::__construct('');  
    }
    
    /**
     * Осуществляет вход пользователя в систему
     * 
     * @return Bool True в случае успеха
     */
    public function login()
    {
        if(!$this->validate())
            return $this->addActionError($this->getFirstError());
        
        return $this->loginUser($this->login);    
    }
    
    /**
     * Выполняет выход пользователя из системы
     * @return Bool True, в случае успеха
     */
    public function logout()
    {
        $this->controller->getWebUser()->logout();
        return true;
    }
    
    /**
     * Осуществляет вход пользователя в систему.
     * Данный метод альтернатива методу login(), для которого требуется форма.
     * 
     * @param String $login Логин
     * @param String $password Пароль
     * @param Bool $rememberMe Если True, то пользователь будет запомнен системой
     * @return Bool True, в случае успеха
     */
    public function loginWithCredentials($login,$password,$rememberMe = false)
    {
        $this->login = $login;
        $this->password = $password;
        $this->rememberMe = $rememberMe;
       
        return $this->login();
    }
    
    /**
     * Проверка пароля. 
     * Для проверки используется метод пользователя getEncodedPassword();
     */
    public function checkPassword()
    {
        $user = $this->provider->findByLogin($this->login);
        if(!$user)
            return $this->addError('login',Yii::t("lib","Incorrect login"));
        
        $userPassword = $user->getEncodedPassword();
        $encodedPassword = $user->encodePassword($this->password);
       
        
        if($encodedPassword != $userPassword)
            return $this->addError('password',Yii::t("lib","Incorrect password"));
    }
    
    /**
     * Правила валидации
     * 
     * @return Array
     */
    public function rules()
    {
	return array(
	    array('login, password', 'required'),
	    array('rememberMe', 'boolean'),
	    array('password', 'checkPassword'),
	);
    }

    /**
     * Вход в систему за пользователя по логину.
     * <b>Использовать только если есть понимание, что пользователя точно надо логинить</b>
     * 
     * @param String @login  Имя пользвователя
     */
    public function loginUser($login)
    {
        $session = $this->controller->getWebUser();
        $identity = new CUserIdentity($login, "WhyThisIsNeeded?");
        $duration = $this->rememberMe ? 60*60*24*7 : 0;
       
        return $session->login($identity,$duration);
    }
    
    public function attributeLabels()
    {
        $arr = [];
        $arr["login"] = Yii::t("lib","Login");
        $arr["password"] = Yii::t("lib","Password");
        return $arr;
    }

}