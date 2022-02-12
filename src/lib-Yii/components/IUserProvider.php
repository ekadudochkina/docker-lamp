<?php
/**
 * Интерфейс поставщика пользователей.
 * Данный интерфейс нужен, чтобы абстагироваться от способа поиска пользователей и способа шифрования пароля.
 * 
 * @see IUser
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
interface IUserProvider
{
  
     /**
    * Получает класс пользователя
    * 
    * @return Класс пользователя
    */
    public function getUserClass();
    
   /**
    * Возвращает пользователя по его логину
    * 
    * @param String $login Логин пользователя
    * @return IUser Пользователь
    */
   public function findByLogin($login);
   
   /**
    * Возвращает пользователя по его почте
    * 
    * @param String $email Почта пользователя
    * @return IUser Пользователь
    */
   public function findByEmail($email);

   /**
    * Сохранение пользователя
    * 
    * @param IUser $user Пользователь
    */
   public function saveUser(IUser $user);
   
   /**
    * Генерация кода, по которому можно однозначно найти пользователя
    * 
    * @param IUser $user Пользователь
    * @return String Код по которому можно его найти
    */
   public function createCode(IUser $user);
    
    /**
     * Поиск пользователя по коду, созданному функцией createCode
     * 
     * @param String $code Код пользователя
     * @param Int $timeToLive Срок дейсвтвия кода в секундах (2 часа по-умолчанию)
     * @return IUser Пользователь или null в случае ошибки
     */
    public function findUserByCode($code, $timeToLive = 7200);
}
