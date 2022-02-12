<?php
/**
 * Интерфейс пользователя для классов авторизации.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
interface IUser
{
   /**
    * Получение имени пользователя
    * 
    * @return String Имя пользователя
    */
   public function getName();
    
   /**
    * Установка имени пользвоателя
    */
   public function setName($name);
   
   /**
    * Получение логина пользователя
    * 
    * @return String Логин пользователя
    */
   public function getLogin();
   
   /**
    * Устанавливает логина пользователя
    * 
    * @param String $login Логин пользователя
    */
   public function setLogin($login);
   
   /**
    * Установка пароля пользователю. Как правило данный метод также шифрует пароль.
    * 
    * @param String $password Пароль в незашифрованном виде
    */
   public function setPassword($password);
   
   /**
    * Получение пароля пользователя
    * 
    * return String Зашифрованный пароль
    */
   public function getEncodedPassword();
     
   /**
    * Шифрует пароль для пользователя
    * 
    * @param String $string Исходная строка пароля
    * @return String Зашифрованная строка пароля
    */
   public function encodePassword($string);
   
   /**
    * Установка адреса электронной почты
    * 
    * @param String $email Адрес почты
    */
   public function setEmail($email);
   
   /**
    * Получение адреса электронной почты
    * 
    * @return String Адрес почты
    */
   public function getEmail();
   
   /**
    * Подтверждение адреса электронной почты
    */
   public function confirmEmail();
   
   /**
    * Валидация пользователя
    * @return True, в случае успешной валидации
    */
   public function validate();
   
   /**
    * Указывает пользователей администраторов
    * @return Boolean, true - если админ
    */
   public function isAdmin();
}
