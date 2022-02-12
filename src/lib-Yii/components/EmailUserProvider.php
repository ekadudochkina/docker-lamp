<?php
/**
 * Провайдер пользователей для авторизации по email
 *
 * @see IUser
 * @see SimpleUserProvider
 * @see IUserProvider
 * 
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 * @package Hs\Auth
 */
class EmailUserProvider extends SimpleUserProvider
{     
    /**
     * Возвращает пользователя по его логину
     * 
     * @param String $login Логин пользователя
     * @return IUser Пользователь
     */
    public function findByLogin($login)
    {
	return parent::findByEmail($login);
    }
    
    /**
     * Возвращает пользователя по его почте
     * 
     * @param String $email Почта пользователя
     * @return IUser Пользователь
     */
    public function findByEmail($email)
    {
        /* @var $user User */
        $user = parent::findByEmail($email);
  
        return $user;
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
        $email = $user->getEmail();
        $hash = $this->createHash($user, $time);
        $str = $time . "_" . $email . "_" . $hash;
        $code = base64_encode($str);
        return $code;
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
        $user = $this->findByEmail($login);
        if (!$user)
            return null;

        //Выкидываем если хеш не соответсвует
        $userHash = $this->createHash($user, $time);
        if ($userHash != $hash)
            return null;

        return $user;
    }
    
}
