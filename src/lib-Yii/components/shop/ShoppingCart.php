<?php

namespace Hs\Shop;

/**
 * Корзина для товаров для интернет-магазина
 * 
 * @package Hs\Shop
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ShoppingCart
{

    /**
     * Объект модели товаров
     * 
     * @var CActiveRecord
     */
    protected $model = null;

    /**
     * Контроллер
     * @var BaseController
     */
    protected $controller = null;

    /**
     * Объект платежа (для получения валюты)
     * 
     * @var BasePayment
     */
    protected $paymentObj = null;

    /**
     * Товары
     * @var CActiveRecord[]
     */
    protected $items = [];

    /**
     * Параллельный массив (к товарам) с их количествами
     * @var Number[] 
     */
    protected $quantities = [];

    /**
     * Имя куки в кторой хранится информация о товарах в корзине
     * 
     * @var String
     */
    protected $cartCookieName = "hsCartItems";

    /**
     * Имя куки в кторой хранится информация о товарах магазина
     * @var String
     */
    private $shopCookieName = "hsShopItems";

    public function __construct(\BaseController $controller, \CActiveRecord $emptyModel, \BasePayment $paymentObj)
    {
        $this->model = $emptyModel;
        $this->controller = $controller;
        $this->paymentObj = $paymentObj;
    }

    /**
     * Загружает данные о товарах с клиента
     * 
     * @return boolean True, если действительно что-то было передано с клиента
     */
    public function load()
    {
        $cookieName = $this->cartCookieName;
        $cookie = $this->controller->getRequest()->getCookies()->itemAt($cookieName);
        if (!$cookie)
        {
            return false;
        }

        try
        {
            $items = [];
            $quantities = [];
            //$sizes = [];
            $value = $cookie->value;
            $arr = \CJSON::decode($value);
            foreach ($arr as $row)
            {
                $id = $row['i'];
                $quantity = $row['q'];
                //$size = $row['size'];
                $item = $this->model->findByPk($id);
                if ($item)
                {
                    $items[] = $item;
                    $quantities[] = $quantity;
                    $this->loadItem($item);
                    //$sizes[] = $size;
                }
            }
            $this->quantities = $quantities;
            $this->items = $items;
        } catch (\Exception $ex)
        {
            \Yii::log("Error: couldn't load items from cart. Resetting.");
            $cookies = $this->controller->getRequest()->getCookies();
            unset($cookies[$cookieName]);
        }
        return true;
    }

    /**
     * Добавление вещи в корзину
     * 
     * @todo Избавиться от размера
     * @param String $id Идентификатор вещи
     * @param Number $quantity Количество вещей
     * @param String $size Размер вещи
     * @throws Exception
     */
    public function addItem($id, $quantity, $size)
    {
        $item = $this->model->findByPk($id);
        if (!$item)
        {
            throw new Exception("Товар с id '$id' не найден.");
        }
        $this->quantities[] = $quantity;
        $this->items[] = $item;
    }

    /**
     * Получение количества позиций
     * 
     * @return String
     */
    public function getRowNumber()
    {
        return count($this->items);
    }

    /**
     * Получение товара для позиции
     * 
     * @param Number $number Номер позиции
     * @return IShoppingCartItem Объект товара
     * @throws Exception
     */
    public function getItemForRow($number)
    {
        if (!isset($this->items[$number]))
        {
            throw new Exception("Позиции '$number' нет в корзине");
        }
        return $this->items[$number];
    }

    /**
     * Получение количества товаров для позиции
     * 
     * @param Number $number Номер позиции
     * @return Number Количество товаров
     * @throws Exception
     */
    public function getQuantityForRow($number)
    {
        //Проверка есть ли позиция
        $this->getItemForRow($number);

        $result = $this->quantities[$number];
        return $result;
    }

    /**
     * Получение стоимости позиции в списке покупок (товар х кол-во)
     * 
     * @param Number $number Номер позиции
     * @return Integer Стоимость позиции
     */
    public function getRowPrice($number)
    {
        $item = $this->getItemForRow($number);
        $quantity = $this->getQuantityForRow($number);
        $price = $item->getPrice() * $quantity;
        return $price;
    }

    /**
     * Получение стоимости позиции в виде строки с символом валюты.
     * 
     * @param Number $number Номер позиции
     * @return String Строка цены
     */
    public function getRowPriceString($number)
    {
        $price = $this->getRowPrice($number);
        $result = $this->formPriceString($price);
        return $result;
    }

    /**
     * Получение стоимости товара в виде строки с символом валюты.
     * 
     * @param Number $number Номер позиции
     * @return String Строка цены
     */
    public function getItemPriceString($number)
    {
        $item = $this->getItemForRow($number);
        $result = $this->formPriceString($item->getPrice());
        return $result;
    }

    /**
     * Получение подытога (полной цены корзины)
     * 
     * @return Number
     */
    public function getSubtotal()
    {
        $count = $this->getRowNumber();
        $total = 0;
        for ($i = 0; $i < $count; $i++)
        {
            $total += $this->getRowPrice($i);
        }
        return $total;
    }

    /**
     * Формирует цену товара
     * 
     * @param String $price
     * @return string
     */
    public function formPriceString($price)
    {
        $this->paymentObj->price = $price;
        $str = $this->paymentObj->getPriceString();
        return $str;
    }

    /**
     * Получение символа валюты
     * 
     * @return String
     */
    public function getCurrencySymbol()
    {
        return $this->paymentObj->getCurrencySymbol();
    }

    /**
     * Получение подытога в виде строки для отображения
     * 
     * @return String
     */
    public function getSubtotalString()
    {
        $total = $this->getSubtotal();
        $str = $this->formPriceString($total);
        return $str;
    }

    /**
     * Очищает корзину (на клиенте)
     */
    public function clear()
    {
        /* @var $cookieList  CCookieCollection */
        $cookieList = $this->controller->getRequest()->getCookies();
        $cookieList->remove($this->cartCookieName);
    }

    /**
     * Загружает информацию о вещах на стороную клиента
     * 
     * @param IShoppoingCartItem[] $items Массив вещей
     */
    public function loadItems($items)
    {
        foreach ($items as $item)
        {
            $this->loadItem($item);
        }
    }

    /**
     * Загружает информацию о вещи на стороную клиента
     * @param \Hs\Shop\IShoppingCartItem $item Вещь
     */
    public function loadItem(IShoppingCartItem $item)
    {
        $items = $this->controller->getJavascriptParam($this->shopCookieName);
        $items = $items != null ? $items : [];
        foreach ($items as $ar)
        {
            if ($ar["id"] == $item->getPk())
            {
                return;
            }
        }
        $arr = [];
        $arr["id"] = $item->getPk();
        $arr["info"] = $item->getDescription();
        $arr["image"] = $item->getImageUrl();
        $arr["title"] = $item->getTitle();
        $arr["price"] = $item->getPrice();
        $items[] = $arr;
        $this->controller->addJavascriptParam($this->shopCookieName, $items);
    }

    /**
     * Добавляет класс на кнопку добавления товара в корзину
     * 
     * @param \Hs\Shop\IShoppingCartItem $model Товар
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getAddToCartButtonClass(IShoppingCartItem $model)
    {
        $class = "hs-cart-add item-" . $model->getPk();
        return $class;
    }

    /**
     * Добавляет счетчик товаров к элементу input.
     * Счетчик можно отображать как для вещей, которые нужно добавить в корзину,
     * Так и для вещей, которые уже там (например при отображении содержимого корзины)
     * 
     * @todo Разделить метод на 2, так как иначе он запутанный
     * @param \Hs\Shop\IShoppingCartItem $model Товар
     * @param Boolean $existingItem Флаг того, что 
     * @param Number $quantity Количество вещей по-умолчанию
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getQuantityInputClass(IShoppingCartItem $model, $existingItem = false, $quantity = null)
    {
        $class = "hs-cart-item-quantity-input item-" . $model->getPk();
        if ($quantity)
        {
            $class .= " hs-cart-value-" . $quantity;
        }
        if ($existingItem)
        {
            $class .= " hs-cart-existing";
        }
        return $class;
    }

    /**
     * Класс для увеличения количества товаров для добавления в корзину / в корзине
     * 
     * @param \Hs\Shop\IShoppingCartItem $model Товар
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getQuantityAddClass(IShoppingCartItem $model)
    {
        $class = "hs-cart-item-quantity-add item-" . $model->getPk();
        return $class;
    }

    /**
     * Класс для уменьшения количества товаров для добавления в корзину / в корзине
     * 
     * @param \Hs\Shop\IShoppingCartItem $model Товар
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getQuantityRemoveClass(IShoppingCartItem $model)
    {
        $class = "hs-cart-item-quantity-remove item-" . $model->getPk();
        return $class;
    }

    /**
     * Добавляет перевод для шаблонов корзины на стороне клиента
     * 
     * @param String $key Название переменной
     * @param String $name Значение переменной
     */
    public function setTranslation($key, $name)
    {
        $translationParamName = "hsCartTranslations";
        $translations = $this->controller->getJavascriptParam($translationParamName);
        $translations = $translations ? $translations : [];
        $translations[$key] = $name;
        $this->controller->addJavascriptParam($translationParamName, $translations);
    }

    /**
     * Добавляет класс на кнопку, который удаляет товар из корзины
     * 
     * @param \Hs\Shop\IShoppingCartItem $model Товар
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getRemoveClass(IShoppingCartItem $model)
    {
        $class = "hs-cart-item-remove item-" . $model->getPk();
        return $class;
    }

    /**
     * Добавляет класс на элемент, в котором будет отображаться общая стоимость товаров этого типа
     * 
     * @param \Hs\Shop\IShoppingCartItem $model Товар
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getRowPriceClass(IShoppingCartItem $model)
    {
        $class = "hs-cart-item-total item-" . $model->getPk();
        return $class;
    }

    /**
     * Добавляет класс на элемент, в котором будет отображаться информация о товаре.
     * При удалении товара из корзины данный элемент будет также удален из верстки
     * 
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getCartItemElementClass(IShoppingCartItem $model)
    {
        $class = "hs-cart-item-element item-" . $model->getPk();
        return $class;
    }

    /**
     * Добавляет класс на элемент, в котором будет отображаться общее количество товаров в корзине
     * 
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getItemNumberClass()
    {
        return "hs-cart-item-number";
    }

    /**
     * Добавляет класс на кнопку отображения корзины
     * 
     * @return String Класс, который автоматически подцепится HsShoppingCartController
     */
    public function getCartButtonClass()
    {
        return "hs-cart-button";
    }

    /**
     * Добавляет необходимые Javascript файлы для функционирования корзины на сайте
     */
    public function addJavascriptFiles()
    {
        $controller = $this->controller;
        $controller->addJavascriptFile("jquery.min.js", null, "jquery/dist");
        $controller->addJavascriptFile("jquery.cookie.js", null, 'jquery.cookie');
        $controller->addJavascriptFile("hs.application.js");
        $controller->addJavascriptFile("hs.functions.js");
        $controller->addJavascriptFile("hs.Cart.js");
        $controller->addJavascriptFile("hs.CartItem.js");
        $controller->addJavascriptFile("hs.Counter.js");
        $controller->addJavascriptFile("hs.EventMixture.js");
        $controller->addJavascriptFile("hs.functions.js");
        $controller->addJavascriptFile("hs.CartController.js");
    }

}
