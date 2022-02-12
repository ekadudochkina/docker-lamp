<?php

/**
 * Менеджер платежной системы Stripe
 *
 * @package Hs\Shop
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class StripePaymentManager implements IPaymentManager
{

    /**
     * Объект контроллера
     * 
     * @var BaseController
     */
    protected $controller;

    /**
     * Секретный ключ API
     * 
     * @var String
     */
    protected $apiKeySecret;

    /**
     * Публичный ключ API
     * @var String
     */
    protected $apiKey;
    
    
     /**
     * Action для формы
     * @var String
     */
    protected $actionUrl;

    /**
     * Имейл пользователя
     * 
     * @var String
     */
    protected $customerEmail;

    public function __construct($key, $secret, BaseController $controller)
    {
        $this->apiKeySecret = $secret;
        $this->apiKey = $key;
        $this->controller = $controller;
    }

    /**
     * Второй этап оплаты
     * На данном этапе происходит обработка платежа на стороне нашего сайта
     * 
     * @param BasePayment $payment Платеж который оплатил или не оплатил пользователь
     * @return Boolean B случае успешной оплаты вернет true, в случае неуспешной false
     */
    public function completePayment(\BasePayment $model)
    {
        $controller = $this->controller;

        $id = $controller->getRequest()->getParam("id");
        if (!$id)
            throw new Exception("Can't find the payment id");

        /* @var $payment BasePayment */
        $payment = $model->findByPk($id);
        if (!$payment)
            throw new Exception("Can't find the payment");

        if ($payment->status == BasePayment::STATUS_COMPLETE)
            return true;
        return false;
    }

    /**
     * Первый этап оплаты.
     * В данном методе происходит формирования оплаты на сайте платежной системы.
     * 
     * @param BasePayment $payment Платеж который собственно и будет оплачен пользователем
     * @param String $route Маршрут в формате Controller/action на action контроллер который будет завершать платеж
     */
    public function startPayment(\BasePayment $payment, $route)
    {
        $payment->start($this);
        $controller = $this->controller;
        $url = $controller->createAbsoluteUrl($route, ['id' => $payment->getPk()]);

        $token = $controller->getRequest()->getParam("stripeToken");
        if (!$token)
        {
            throw new Exception("От страйпа не получен токен");
            return false;
        }


        $amount = $this->getAmount($payment);
        $params = array(
            "amount" => $amount,
            "currency" => $payment->getCurrency(),
            "source" => $token, // obtained with Stripe.js
            "description" => $payment->getTitle()
        );

        \Stripe\Stripe::setApiKey($this->apiKeySecret);

        //Если есть адрес электронной почты, то создаем покупателя
        //Ему будет выслан чек на имейл
        if ($this->customerEmail)
        {
            //Create a Customer:
            $customer = \Stripe\Customer::create(array(
                        "email" => $this->customerEmail,
                        "source" => $token,
            ));
            unset($params['source']);
            $params['customer'] = $customer->id;
        }


        //bug::drop($params);
        try
        {
            $charge = \Stripe\Charge::create($params);
            $payment->setPaymentId($charge->id);
            $payment->complete($this);
        } catch (Exception $ex)
        {
            $payment->fail($this);
            $controller->redirect($url);
            return false;
        }

        $controller->redirect($url);
        return true;
    }

    /**
     * Конвертация в минимальные валютные единицы, которую требует Stripe.
     * 
     * @param Paymnet $payment Объект платежа
     * @return Стоимость в минимальных единицах валюты (копейки, центы)
     */
    public function getAmount(BasePayment $payment)
    {
        //Ну пока мы все принимаем в рублях, фунтах, евро или долларах
        //так что получение копеек, это просто умножение на 100
        $number = $payment->getPrice();
        return $number * 100;
    }

    /**
     * Назначение имейла пользователя.
     * Если задан, то туда может быть отправлено уведомление.
     * 
     * @param String $email
     */
    public function setCustomerEmail($email)
    {
        $this->customerEmail = $email;
    }

    /**
     * Создает HTML для кнопки платежа Stripe
     * 
     * @param BasePayment $payment Платеж
     * @param type $companyName Название компании
     * @param type $imageUrl Изображние для формы
     * @return string
     */
    public function getFormHTML(BasePayment $payment, $companyName, $imageUrl = null)
    {
        $params = [];
        $params['data-key'] = $this->apiKey;
        $params['data-name'] = $companyName;
        $params['data-amount'] = $this->getAmount($payment);
        $params['data-currency'] = $payment->getCurrency();
        $params['data-description'] = $payment->getTitle();

        if ($this->customerEmail)
        {
            $params['data-email'] = $this->customerEmail;
        }
        if ($imageUrl)
        {
            $params['data-image'] = $imageUrl;
        }

        $actionUrl = $this->getActionUrl();
        $attrsArr = StringHelper::wrapArrays(array_keys($params), "='", array_values($params), "'");
        $attrs = join(" ", $attrsArr);
        $html = ' <form action="'.$actionUrl.'" method="POST">
                <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" ' . $attrs . ' >
                </script>
            </form>';
        return $html;
    }

    /**
     * Получение объекта отсылающего письма
     * 
     * @return PHPMailer
     */
    public function getMailer()
    {
        $mailer = $this->controller->getMailer();
        return $mailer;
    }
    
    /**
     * Назначает action у формы
     * 
     * @return String В формате "controller/action"
     */
    public function setActionUrl($url)
    {
        $this->actionUrl = $url;
    }
    
     /**
     * Возвращает action для формы
     * 
     * @return String В формате "controller/action"
     */
    public function getActionUrl()
    {
        $url = "";
    
        if($this->actionUrl !== null){
            $url = "/".$this->actionUrl;
        }
        
        return $url;
    }

}
