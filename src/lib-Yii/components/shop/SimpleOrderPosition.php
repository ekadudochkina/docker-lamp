<?php
namespace Hs\Shop;
/**
 * Позиция заказа (товар + количество)
 * 
 * @package Hs\Shop
 * @property Order $order Заказ
 * @property Item $item Товар
 * @autogenerated 09-01-2017
 */
class SimpleOrderPosition extends \ActiveRecord
{

    /**
     * Первичный ключ
     * 
     * @var Integer
     * @autogenerated 09-01-2017
     */
    public $id;

    /**
     * Заказ
     * <b>Внешний ключ.</b>
     * 
     * @update CASCADE
     * @delete CASCADE
     * @var Integer
     * @autogenerated 09-01-2017
     */
    public $orderId;

    /**
     * Товар
     * <b>Внешний ключ.</b>
     * 
     * @update CASCADE
     * @delete RESTRICT
     * @var Integer
     * @autogenerated 09-01-2017
     */
    public $itemId;

    /**
     * Количество товаров
     * 
     * @var Integer
     * @autogenerated 09-01-2017
     */
    public $quantity = 1;

    /**
     * Стоимость товара. (Информационное поле, не должно участвовать в вычислениях)
     * 
     * @sqltype decimal(11,2)
     * @var Float
     * @autogenerated 09-01-2017
     */
    public $price;

    /**
     * Дата создания
     * 
     * @sqltype DATETIME
     * @var String
     * @autogenerated 09-01-2017
     */
    public $creationDate;

    public function __construct($scenario = 'insert')
    {
        $this->creationDate = \DateTimeHelper::timestampToMysqlDateTime();
        parent::__construct($scenario);
    }

    /**
     *  Возвращает правила валидации.
     * <b>Внимание: для полей у которых в БД тип VARCHAR необходимо создать валидатор "length".</b>
     * 
     * @autogenerated 09-01-2017
     * @return Array[] Массив правил валидации
     */
    public function rules()
    {
        $arr = parent::rules();
        $arr[] = array('orderId, itemId, quantity, price, creationDate', 'required');
        $arr[] = array('orderId, itemId, quantity', 'numerical', 'integerOnly' => true);
        $arr[] = array('creationDate', 'type', 'type' => 'datetime', 'datetimeFormat' => 'yyyy-MM-dd hh:mm:ss');
        return $arr;
    }

    /**
     * Возвращает массив связей моделей.
     * <b>Внимание: связи BELONGS_TO являются внешними ключами.</b> Для них можно указать поведение при удалений родительской сущности.
     * 
     * @autogenerated 09-01-2017
     * @return Array[] Массив связей
     */
    public function relations()
    {
        $arr = parent::relations();
        $arr["order"] = array(self::BELONGS_TO, 'Order', 'orderId');
        $arr["item"] = array(self::BELONGS_TO, 'Item', 'itemId');
        return $arr;
    }

    /**
     * Возвращает информацию о том, как называются поля на человеческом языке.
     * 
     * @autogenerated 09-01-2017
     * @return String[] Массив лейблов для полей (name=>label)
     */
    public function attributeLabels()
    {
        $arr = parent::attributeLabels();
        $arr["id"] = "Id";
        $arr["orderId"] = "Order";
        $arr["itemId"] = "Item";
        $arr["quantity"] = "Quantity";
        $arr["price"] = "Price";
        $arr["creationDate"] = "Creation date";
        return $arr;
    }

    /**
     * Возвращает новую модель данного класса. 
     * Этот метод необязателен, но улучшает работу подсказок.
     * 
     * @autogenerated 09-01-2017
     * @param String $className Имя класса модели
     * @return OrderItems пустой объект модели
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Получение имени таблицы в базе данных
     *
     * @autogenerated 09-01-2017
     * @return String Название таблицы
     */
    public function tableName()
    {
        return 'orderpositions';
    }

}
