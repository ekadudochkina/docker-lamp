<?php

/**
 * Простой поставщик пользователей
 * 
 * @see IUser
 * @see IUserProvider
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Auth
 */
class SimpleUserProvider implements IUserProvider
{

    /**
     * Модель пользователя
     * 
     * @var BaseUser 
     */
    protected $userModel = null;

    /**
     * Соль для того, чтобы хеш получался более запутанным
     * 
     * @var String
     */
    protected $salt = "saltysalt";
    
        
    /**
     *  Маршрут, куда пользователи отправляются после логина
     * @var String
     */
    protected $route;

    /**
     * Модель пользователя отнаследованная от BaseUser
     * @param IUser $model
     */
    public function __construct(IUser $model)
    {
        $this->userModel = $model;
        $this->checkUser($this->userModel);
    }

    /**
     * Возвращает пользователя по его логину
     * 
     * @param String $login Логин пользователя
     * @return IUser Пользователь
     */
    public function findByLogin($login)
    {
        $attrs = array("name" => $login);
        $result = $this->userModel->findByAttributes($attrs);
        return $result;
    }

    /**
     * Возвращает пользователя по его почте
     * 
     * @param String $email Почта пользователя
     * @return IUser Пользователь
     */
    public function findByEmail($email)
    {
        $attrs = array("email" => $email);
        $result = $this->userModel->findByAttributes($attrs);
        return $result;
    }

    /**
     * Поиск пользователя по коду, созданному функцией createCode
     * 
     * @param String $code Код пользователя
     * @param Int $timeToLive Срок дейсвтвия кода в секундах (2 часа по-умолчанию)
     * @return IUser Пользователь или null в случае ошибки
     */
    public function findUserByCode($code, $timeToLive = 7200)
    {
        $decoded = base64_decode($code);
        $parts = explode("_", $decoded);
        if (count($parts) != 3)
            return null;
        $time = $parts[0];
        $login = $parts[1];
        $hash = $parts[2];

        //Выкидываем если код устарел
        $currentTime = time();
        $timePassed = $time - $currentTime;
        if ($timePassed > $timeToLive)
            return null;

        //Выкидываем если пользовтаель не найден
        $user = $this->findByLogin($login);
        if (!$user)
            return null;

        //Выкидываем если хеш не соответсвует
        $userHash = $this->createHash($user, $time);
        if ($userHash != $hash)
            return null;

        return $user;
    }

    /**
     * Сохранение пользователя. 
     * Этот метод является частью интерфейса IUserProvider.
     * 
     * @param IUser $user Пользователь
     */
    public function saveUser(IUser $user)
    {
        $model = $this->iUserToBaseUSer($user);
        return $model->save();
    }

    /**
     * Генерация кода, по которому можно однозначно найти пользователя
     * 
     * @param IUser $user Пользователь
     * @return String Код по которому можно его найти
     */
    public function createCode(IUser $user)
    {
        $time = time();
        $login = $user->getLogin();
        $hash = $this->createHash($user, $time);
        $str = $time . "_" . $login . "_" . $hash;
        $code = base64_encode($str);
        return $code;
    }
    
    /**
     * Получает путь, куда пользователь будет переведен после логина
     * 
     * @param IUser $user Пользователь
     * @return String Путь пользователя 
     */
    public function getUserRoute(IUser $user)
    {
        return $this->route;
    }

    /**
     * Создает такой хеш, с помощью которого можно наверняка проверить на подлинность
     * код, сгенерированный для поиска пользователя
     * 
     * @param IUser $user Пользователь
     * @param Int $time Unix timestamp
     * @return String Хэш
     */
    protected function createHash(IUser $user, $time)
    {
        $hash = md5($time . "_" . $user->getLogin() . "_" . $user->getEncodedPassword() . "_" . $this->salt);
        return $hash;
    }

    /**
     * Проверяет, что пользователь является наследником класса BaseUser.
     * В случае несоответствия кидает исключение
     * 
     * @param Mixed $user Пользователь
     * @throws Exception
     */
    protected function checkUser($user)
    {
        if (!is_subclass_of($user, "BaseUser"))
            throw new Exception("Модель должна быть наследником класса 'СActiveRecord'");
    }

    /**
     * Приводит пользователя к классу BaseUser или кидает исключение
     * 
     * @param IUser $user Объект обладающий инерфейсом пользователя
     * @return Baseuser Модель пользователя
     */
    protected function iUserToBaseUSer($user)
    {
        $this->checkUser($user);
        return $user;
    }
    
     /**
     * Получение класса пользователя
     * 
     * @return Класс пользователя
     */
    public function getUserClass() {
        return $this->userModel;
    }

}
