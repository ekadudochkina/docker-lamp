<?php
/**
 * Пустой менеджер для обработки платежей. Удобен для тестирования.
 *
 * @package Hs\Shop
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 */
class EmptyPaymentManager implements IPaymentManager
{

    /**
     * Текущий контроллер
     * @var BaseController
     */
    protected $controller;

    /**
     * @param BaseController $controller Текущий контроллер
     */
    public function __construct(BaseController $controller)
    {
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

        if ($payment->getStatus() == BasePayment::STATUS_COMPLETE)
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

        $controller = $this->controller;

        $payment->start();
        $amount = $this->getAmount($payment);
        $params = array(
            "amount" => $amount,
            "currency" => $payment->getCurrency(),
            "description" => $payment->getTitle()
        );

        $url = $controller->createAbsoluteUrl($route, ['id' => $payment->getPk()]);
        $controller->redirect($url);
        return true;
    }

    /**
     * Конвертация в минимальные валютные единицы, которую требует Stripe.
     * 
     * @param Paymnet $payment Объект платежа
     * @return Стоимость в минимальных единицах валюты (копейки, центы)
     */
    public function getAmount(Payment $payment)
    {
        //Ну пока мы все принимаем в рублях, фунтах, евро или долларах
        //так что получение копеек, это просто умножение на 100
        $number = $payment->getPrice();
        return $number * 100;
    }

}
