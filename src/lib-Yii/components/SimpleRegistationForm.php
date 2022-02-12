<?php

/**
 * Простая форма для регистрации, которая подойдет большинству проектов
 * 
 * Для ее работы требуется контроллер и объекты IUserProvider и IUser.
 * Наследники BaseUser реализуют оба интерфейса.
 * 
 * @see IUserProvider
 * @see IUser
 * @see BaseUser
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
class SimpleRegistationForm extends ModelForm implements IActiveRecord
{

    /**
     * Имя пользователя (отображаемое)
     * 
     * @var String 
     */
    public $name;

    /**
     * Логин
     * 
     * @var String
     */
    public $login;

    /**
     * Пароль
     * 
     * @var String
     */
    public $password;

    /**
     * Подтверждение пароля
     * @var String
     */
    public $passwordConfirm;

    /**
     * Адрес эл. почты
     * @var String
     */
    public $email;

    /**
     * Согласие с правилами, при регистрации
     * 
     * @var String
     */
    public $acceptRulesFlag;

    /**
     * Имя роли пользователя
     * @var String
     */
    public $role;

    /**
     * Требовать ли пароль или генерировать автоматически
     * <b>При использовании данной функции нужно обязательно передать пароль в шаблон для отпавки почты
     * или как-то иначе вывести его на экран. </b>
     * 
     * @var Bool 
     */
    public $generatePassword = false;

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
    public $requireAcceptRules = false;

    /**
     * Проверять ли наличие логина, при регистрации.
     * (По-умолчанию: да)
     * @var Bool
     */
    public $requireLogin = true;

    /**
     * Логинить ли пользователя после регистрации
     * @var Bool
     */
    public $loginAfterRegistration = true;

    /**
     * Логинить ли пользователя после проверки его почты
     * @var Bool
     */
    public $loginAfterEmailConfirm = false;

    /**
     * Если true, то пользователь может самостоятельно выбрать роль
     * @var Bool
     */
    public $safeRole = false;

    /**
     * Отсылать ли сообщение пользователю на электронную почту
     * 
     * @var Bool 
     */
    protected $sendConfirmationEmail = false;

    /**
     * Тема письма
     * Теги {{name}} {{login}}  будут превращены в данные пользователя
     * 
     * @var String 
     */
    protected $emailTitle = null;

    /**
     * Тело письма
     * Теги {{name}} {{login}} {{confirm_url}} будут превращены в данные пользователя
     * 
     * @var String 
     */
    protected $emailBody = null;

    /**
     * Путь к контроллеру, который будет обрабатывать подтверждение
     * 
     * @var String 
     */
    protected $emailConfirmRoute = null;

    /**
     * Имя параметра контроллера при подтверждении имейла
     * @var String 
     */
    protected $emailConfirmParamName = "id";

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
    protected $controller = null;

    /**
     * Пользователь
     * @var IUser
     */
    protected $user;

    /**
     * Менеджер авторизации для контроля ролей.
     * @var DbAuthManager
     */
    protected $authManager = null;

    /**
     * Роли, которые необходимо назначить пользователю
     * 
     * @var CAuthItem
     */
    protected $defaultRole = array();

    /**
     * @param IUser $model Модель пользователя
     * @param IUserProvider $provider Объект, который умеет осуществлять поиск пользовтаелей
     * @param BaseController $controller Контроллер
     * @throws Exception
     */
    public function __construct(IUser $model, IUserProvider $provider, BaseController $controller)
    {
        $this->user = $model;
        $this->provider = $provider;
        $this->controller = $controller;
        $this->emailTitle = "Thank you for registering on " . Yii::app()->name;
        $this->emailBody = "Thank you, {{name}} for registering. Here's your url: {{confirm_url}}";

        if (!($model instanceof ActiveRecord))
            throw new Exception("Пользователь должен быть наследником класса ActiveRecord");

        parent::__construct($model, '');
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

        //Назначаем данные пользователю перед валидацией
        //Ведь мы не знаем какие поля там у пользователя
        $this->user->setLogin($this->login);
        $this->user->setName($this->name);
        $this->user->setPassword($this->password);
        $this->user->setEmail($this->email);
        $ret = parent::validate($attributes, $clearErrors);

        return $ret;
    }

    /**
     * Включение добавления ролей, при регистрации
     * 
     * @param DbAuthManager $authManager
     * @param String[] $role Роль, которую необходимо присвоить по умолчанию
     * @throws Exception
     */
    public function enableRoleBasedAccessSystem(DbAuthManager $authManager, $role = null)
    {
        $this->authManager = $authManager;
        $this->authManager->setUserPrefix($this->user);
        if ($role)
        {
            $item = $authManager->getAuthItem($role);
            if ($item === null)
                throw new Exception("Роль '$item' не найдена");
        }

        $this->defaultRole = $role;
    }

    /**
     * Получение данных ролей для <select>
     * 
     * @return String[] Массив ключей и значений
     * @throws Exception
     */
    public function getRoleValues()
    {
        if (!$this->authManager)
        {
            $msg = "Перед тем как использовать метод getRoleValue() необходимо вызвать метод enableRoleBasedAccessSystem()";
            throw new Exception($msg);
        }

        $this->authManager->setUserPrefix($this->user);
        $roles = $this->authManager->getRoles();
        $result = array();
        foreach ($roles as $role)
            $result[$role->getName()] = $role->getName();

        return $result;
    }

    /**
     * Включение отправки имейлов для подтверждения пользователя.
     * В переданных шаблонах теги {{name}} {{login}} {{confirm_url}} будут превращены в данные пользователя 
     * 
     * @param String $route Роут к контроллеру вида controller/action по которому будет обрабатываться подтверждение почты
     * @param String $body Шаблон тела письма
     * @param String $title Шаблон темы письма
     */
    public function enableEmailConfirm($route, $body = null, $title = null)
    {
        $this->sendConfirmationEmail = true;
        $this->emailConfirmRoute = $route;
        if ($body !== null)
            $this->emailBody = $body;
        if ($title !== null)
            $this->emailTitle = $title;
    }

    /**
     * Регистрация пользователя
     * 
     * @return boolean True, если все успешно
     */
    public function register()
    {
        //Создаем пароль, если это необходимо
        if ($this->generatePassword)
            $this->password = $this->generatePassword();

        if (!$this->validate())
            return $this->addActionError($this->getFirstError());

        $this->user->setLogin($this->login);
        $this->user->setPassword($this->password);
        $this->user->setEmail($this->email);

        //Запускаем транзакцию
        $transaction = Yii::app()->getDb()->beginTransaction();

        $result = $this->saveUser();

        if (!$result)
        {
            $err = $this->user->getFirstError();
            return $this->addActionError($err);
        }

        //Добавляем роли
        if ($result && $this->authManager)
        {
            $this->authManager->setUserPrefix($this->user);
            $item = null;
            //Сначала получаем роль, которую указал пользователь, если мы дали ему такую возможность
            if ($this->role)
            {
                $item = $this->authManager->getAuthItem($this->role);
                if (!$item)
                    throw new Exception("Роль '{$this->role}' не найдена");
            }

            //Если такой роли нет, то берем роль по-умолчанию
            if (!$item && $this->defaultRole)
            {
                $item = $this->authManager->getAuthItem($this->defaultRole);
                if (!$item)
                    throw new Exception("Роль '{$this->defaultRole}' не найдена");
            }

            //Если и ее нет, то так дело не пойдет
            if (!$item)
                throw new Exception("Не удалось добавить роль");

            $this->authManager->assign($item->getName(), $this->user->getLogin());
        }

        if ($result)
            $transaction->commit();
        else
        {
            $transaction->rollback();
            return false;
        }

        if ($this->sendConfirmationEmail)
            if (!$this->sendConfirmationEmail())
                return false;

        if ($this->loginAfterRegistration)
        {
            Yii::log("Logging in as {$this->user->getLogin()}");
            $loginForm = new SimpleLoginForm($this->provider, $this->controller);
            $login = $this->requireLogin ? $this->user->getLogin() : $this->user->getEmail();
            $result = $loginForm->loginWithCredentials($login, $this->password);
            $this->mergeActionErrors($loginForm);
        }
        return $result;
    }

    public function rules()
    {
        $arr = parent::rules();
        $safe = array('login, name, email,password,passwordConfirm,acceptRulesFlag', 'safe');

        if ($this->safeRole)
            $safe[0].= ",role";

        $arr[] = $safe;

        $arr[] = array("password", 'required');

        if ($this->requireLogin)
        {
            $arr[] = array("login", 'required');
            $arr[] = array("login", 'uniqueLoginValidator');
        }

        $arr[] = array("email", "email");

        if ($this->sendConfirmationEmail || !$this->requireLogin)
        {
            $arr[] = array("email", "required");
            $arr[] = array("email", "uniqueEmailValidator");
        }
        if ($this->requireAcceptRules)
        {
            $arr[] = array('acceptRulesFlag', 'required');
            $arr[] = array('acceptRulesFlag', 'boolean');
            $arr[] = array('acceptRulesFlag', 'compare', 'compareValue' => true, "message" => "You have to accept the rules");
        }

        if ($this->requirePasswordConfirm)
        {
            $arr[] = array('password', 'comparePasswordsValidator');
        }

        // Debug::drop($arr);
        return $arr;
    }

    /**
     * Валидатор сравнения паролей
     */
    public function comparePasswordsValidator()
    {
        if ($this->password != $this->passwordConfirm)
            $this->addError("passwordConfirm", "Passwords not match");
    }

    /**
     * Проверка имени пользователя на уникальность
     */
    public function uniqueLoginValidator()
    {
        $user = $this->provider->findByLogin($this->login);
        if ($user)
            $this->addError("login", "Someone already registered with this name");
    }

    /**
     * Проверка имени пользователя на уникальность
     */
    public function uniqueEmailValidator()
    {
        $user = $this->provider->findByEmail($this->email);
        if ($user)
            $this->addError("email", "Someone already registered with this email");
    }

    /**
     * Интерфейс сохранения пользователя
     * 
     * @return Bool True, в случае успеха
     */
    protected function saveUser()
    {
        $ret = $this->provider->saveUser($this->user);
        return $ret;
    }

    /**
     * Отсылает сообщение на почту
     */
    protected function sendConfirmationEmail()
    {
        $code = $this->provider->createCode($this->user);
        $url = $this->controller->createAbsoluteUrl($this->emailConfirmRoute, array($this->emailConfirmParamName => $code));

        $subject = $this->templateString($this->emailTitle, $url);
        $body = $this->templateString($this->emailBody, $url);

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
     * @param String $confirmUrl Url для подтверждения адреса электронной почты
     * @return String Строка с подставленными переменными
     */
    protected function templateString($string, $confirmUrl)
    {

        $string = str_replace("{{name}}", $this->user->getName(), $string);
        $string = str_replace("{{login}}", $this->user->getLogin(), $string);
        $string = str_replace("{{email}}", $this->user->getEmail(), $string);
        $string = str_replace("{{password}}", $this->password, $string);
        $string = str_replace("{{confirm_url}}", $confirmUrl, $string);
        return $string;
    }

    /**
     * Подтверждение пользователем адреса электронной почты
     * 
     * @return boolean True, если удалось подтвердить
     */
    public function confirmEmail()
    {
        $code = $this->controller->getRequest()->getParam($this->emailConfirmParamName, null);
        if (!$code)
            return $this->addActionError("Link is invalid");

        //100 лет на подтверждение
        $timeToLive = 60 * 60 * 24 * 30 * 12 * 100;
        $user = $this->provider->findUserByCode($code, $timeToLive);
        if (!$user)
            $this->addActionError("Link is invalid");

        $user->confirmEmail();

        if ($this->loginAfterEmailConfirm)
        {
            $form = new SimpleLoginForm($this->provider, $this->controller);
            $form->rememberMe = true;
            return $form->loginUser($user->getLogin());
        }

        return true;
    }

    /**
     * Генерирует пароль для пользователя
     * 
     * @return String Незашифрованная строка пароля
     */
    protected function generatePassword()
    {
        $str = StringHelper::generateString(10);
        return $str;
    }

    /**
     * Сохраняет пользователя в базе данных.
     * Имплементация интерфейса IActiveRecord
     * Является синонимом метода SimpleRegistrationForm::Register().
     * 
     * @return Bool True, в случае успеха
     */
    public function save()
    {
        return $this->register();
    }

}
