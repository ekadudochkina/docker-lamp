<?php
/**
 * Базовый класс Выплат. 
 * Класс есть прослойка между классом ActiveRecord и Payout
 * 
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * @package Hs\Models
 */
abstract class BasePayout extends ActiveRecord
{
    
    /**
     * @var String
     * Поле хранит статус о выплате
     */
    public $status;
    
    /**
     * @var DateTime. 
     * Дата создания платежа
     */
    public $creationDate;
    
   
    /**
     * @var String 
     * Статус что это новая выплата
     */
    const STATUS_NEW = 'new';
       
    /**
     * @var String 
     * Статус что выплата успешная
     */
    const STATUS_COMPLETE = 'complete';
    
    /**
     * @var String 
     * Статус что выплата неуспешная
     */
    const STATUS_FAILED = 'failed';
    
     /**
     * Конструктор
     * @param String $scenario параметр по умолчанию для вставки значений в таблицу
     */
    public function __construct($scenario = 'insert') 
    {
        $ret = parent::__construct($scenario);
        $this->creationDate = DateTimeHelper::timestampToMysqlDateTime();
        return $ret;
    }
    
     /**
      * Абстрактный метод который необходимо реализовать в классе наследнике
      * 
      * @return String Метод возвращает описание за выплату
      */    
    public abstract function getDescription();
    
    /**
     * Абстрактный метод который необходимо реализовать в классе наследнике
     * 
     * @return String Метод возвращает email пользователя для которого была выплата
     */    
    public abstract function getMail();
       
    /**
     * Абстрактный метод который необходимо реализовать в классе наследнике
     * 
     * @return String Метод возвращает валюта выплаты
     */    
    public abstract function getCurrency();
    
    /**
     * Метод который сохраняет данные о выплатe в бд при успешной обработки платежа
     * @return BasePayout $this
     */    
    public function complete()
    {
        if ($this->status === self::STATUS_COMPLETE)
            throw new Exception ('This payout is already completed');
        
        $this->status = self::STATUS_COMPLETE;        
        $this->save();
        
        return $this;
    }
    
    /**
     * Метод который сохраняет данные о выплатe в бд при неуспешной обработки платежа
     * 
     * @return BasePayout $this
     */   
    public function fail()
    {
        $this->status = self::STATUS_FAILED;        
        $this->save();
        
        return $this;
    }
    
    /**
     * Метод который нужно вызывать перед стартом каждого payout.
     * 
     * @return BasePayout $this
     */    
    public function start()
    {
        if($this->status == self::STATUS_COMPLETE) 
            throw new Exception("This payout is already completed");
        
        $this->status = self::STATUS_NEW;
        
        return $this;
    }
    
    /**
     * Метод возвращает Id выплаты
     * @return Integer id
     */    
    public function getId()
    {
        if($this->status == self::STATUS_NEW)
            throw new Exception("You need to start call start() first");    
        
        return $this->getPrimaryKey();
    }
}