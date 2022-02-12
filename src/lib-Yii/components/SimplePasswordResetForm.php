<?php

/**
 * Модель формы для восстановления пароля
 * Задача данной формы отправить пользователю ссылку на имейл и получить пользователя,
 * когда он пройдет по этой ссылке.
 * 
 * Есть 3 основных метода:
 * <b>sendCodeToEmail()</b> - Высылает пользователю на почту ссылку
 * 
 * <b>getPasswordChangeForm()</b> - Получение формы для изменения пароля пользователю 
 * (вызывается после перехода пользователя по ссылке на почте)
 * 
 * <b>getUserFromLink()</b> - Получает пользователя, после его перехода по ссылке
 * (это запасной вариант, если не хочется использовать форму)
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
class SimplePasswordResetForm extends FormModel
{
    /**
     * Логин
     * @var String 
     */
    public $login;
    
    /**
     * Адрес электронной почты
     * @var String 
     */
    public $email;
    
    
    /**
     * Если True, то не логин необходимо предоставить также и логин
     * 
     * @var Bool
     */
    public $enableLoginCheck = false;

    /**
     * Шаблон темы письма
     * @var String
     */
    protected $emailTitle = null;
    
    /**
     * Шаблон тела письма
     * @var String
     */
    protected $emailBody  = null;
    
    /**
     * Поставщик пользователей
     * @var IUserProvider 
     */
    protected $userProvider = null;
    
    /**
     * Контроллер 
     * 
     * @var BaseController 
     */
    protected $controller = null;
    
    /**
     * Имя параметра, который будет использован для получения кода из URL
     * @var String 
     */
    protected $emailResetParamName = "id";

    public function __construct(BaseController $controller, IUserProvider $provider)
    {
        $this->userProvider = $provider;
        $this->controller = $controller;
        $this->emailTitle = "Password reset for ".Yii::app()->name;
        $this->emailBody = "Greetings, {{name}}. Click here to reset password: {{reset_url}}";
        
        parent::__construct('');
    }

    /**
     * Отправка на почту пользовтателю ссылки, для восстановления пароля
     * В переданных шаблонах теги {{name}} {{login}} {{reset_url}} будут превращены в данные пользователя 
     * 
     * @param String $route Роут к контроллеру вида controller/action по которому будет обрабатываться изменения пароля
     * @param String $emailBody Шаблон тела письма
     * @param String $emailTitle Шаблон темы письма
     * @return Bool True, в случае успеха
     */
    public function sendCodeToEmail($route,$emailBody = null, $emailTitle = null)
    {
        if(!$this->validate())
            return $this->addActionError ($this->getFirstError ());

        if($emailTitle)
            $this->emailTitle = $emailTitle;
        if($emailBody)
            $this->emailBody = $emailBody;
        
        $user = $this->userProvider->findByEmail($this->email);
        if (!$user)
            return $this->addActionError("User not found or email is wrong");

        if ($user->getEmail() === null || $user->getEmail() !== $this->email)
            return $this->addActionError("User not found or email is wrong");
        
        return $this->sendEmail($user, $route);
    }
    
    
    /**
     * Получение пользователя из кода, который был передан по ссылке
     * 
     * @return IUser Пользователь или null в случае ошибки
     */
    public function getUserFromLink()
    {
        $code = $this->controller->getRequest()->getParam($this->emailResetParamName,null);
        $user = $this->userProvider->findUserByCode($code);
        
        if($user)
            return $user;
        return $this->addActionError("Link is invalid");
    }
    
    /**
     * Получение формы для изменения пароля
     * 
     * @return AfterResetPasswordChangeForm Модель формы изменения пароля
     */
    public function getPasswordChangeForm()
    {
        $user = $this->getUserFromLink();
       
        if(!$user)
            return null;
        $form  = new AfterResetPasswordChangeForm($user);
        return $form;
    }
   


    /**
     * Declares the validation rules.
     * The rules state that name and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        $safe = join(',', array_keys($this->getAttributes()));
        $arr = array();
        
        //Все аттрибуты хорошие
        $arr[] = array($safe, 'safe');
        $arr[] = array('email', 'required');
        if($this->enableLoginCheck)
        {
            $arr[] = array('login', 'checkLogin');
            $arr[] = array('login', 'required');
        }
        $arr[] = array('email', 'email');
        return $arr;
    }

    /**
     * Проверяет есть ли реальный пользователь с таким логином
     */
    public function checkLogin()
    {
        $user = $this->userProvider->findByLogin($this->login);
        if ($user == null)
            $this->addError('login', 'User not found or email is wrong');
    }

    /**
     * Отсылает сообщение с кодом восстановления пароля пользователю на почту
     */
    protected function sendEmail($user,$route)
    {
        $code = $this->userProvider->createCode($user);
        $url = $this->controller->createAbsoluteUrl($route, array($this->emailResetParamName => $code));

        $subject = $this->templateString($this->emailTitle,$user, $url);
        $body = $this->templateString($this->emailBody,$user,$url);

        $mailer = $this->controller->getMailer();
        $mailer->AddAddress($this->email);
        $mailer->Subject = $subject;
        $mailer->Body = $body;

        $mailer->Send();
        return true;
    }
    
    /**
     * Подставляет переменные в строку. Эти переменные это данные пользователя и код
     * 
     * @param String $string Исходная строка
     * @param IUser $user Пользователь
     * @param String $resetUrl Url для восстановления пароля
     * @return String Строка с подставленными переменными
     */
    protected function templateString($string,$user,$resetUrl)
    {
        
        $string = str_replace("{{name}}",$user->getName(), $string);
        $string = str_replace("{{login}}",$user->getLogin(), $string);
        $string = str_replace("{{email}}",$user->getEmail(), $string);
        $string = str_replace("{{reset_url}}",$resetUrl, $string);
        return $string;
    }
}
