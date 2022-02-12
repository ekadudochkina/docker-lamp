<?php

require_once(__DIR__.'/adaptivepayments-sdk-php-master/PPBootStrap.php');


use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PayRequest;
use PayPal\Types\AP\Receiver;
use PayPal\Types\AP\ReceiverList;
use PayPal\Types\AP\ExecutePaymentRequest;
use PayPal\Types\Common\RequestEnvelope;


/**
 * Класс помощник для работы с платежной системой paypal через adaptivepayments-sdk api.
 * 
 * ПЛАТЕЖИ
 * 
 * Первый и единственный  этап для данной библиотеки.
 * На первом этапе происходит формирование платежа и url по которому мы редериктим клиента на 
 * сайт paypal.com
 * Для этого нужно в контроллере вызвать метод startPayment($payment,$route)-сформируем платеж.
 * И вызвать метод который начинается на getRedirectUrl() - сформирeм url.
 * 
 * ВЫПЛАТЫ
 * 
 * Для формирование выплат в контроллере нужно вызывать метод executePayout($payout) и передать на вход payout
 * Сам payout формирует пользователь
 * 
 * ПЛАТЕЖИ
 * <b>example:</b>
 *      <pre>//Код контроллера
 * Первый этап создание платежа и перенаправление пользователя на paypal
 * 
 *  public function actionStart($id)
 * {                    
 *       $payment= Payment::model()->findByPk($id);                
 *       $manager = new PayPalAdaptivePaymentsManager($payPalUserName, $payPalPassword, $payPalSignature);     
 *       $manager->startPayment($payment,"payment/complete");
 *       $link=$manager->getRedirectUrl();
 *       $this->redirect($link);
 *  }
 * 
 * ВЫПЛАТЫ
 * <b>example:</b>
 *      <pre>//Код контроллера
 *  public function actionMakePayout() 
 * {
 *      $payout=$user->createPayout();
 *      $manager = new PayPalAdaptivePaymentsManager($payPalUserName, $payPalPassword, $payPalSignature);     
 *      $manager->executePayout($payout);
 *  }
 * 
 */
class PayPalAdaptivePaymentsManager implements IPaymentManager, IPayoutManager
{
    /**
     * Поле хранит уникальное имя клиента в системе paypal для классического sdk api
     * Сам payPalUserName клиент получает из paypal при создании приложения 
     * 
     * @var String 
     */
    protected $payPalUserName;
    
    /**
     * Поле хранит уникальный пароль клиента в системе paypal для классического sdk api
     * Сам payPalPassword клиент получает из paypal при создании приложения 
     * 
     * @var String 
     */
    protected $payPalPassword;
    
    /**
     * Поле хранит сигнатуру клиента в системе paypal для классического sdk api
     * Сам payPalSignature клиент получает из paypal при создании приложения 
     * 
     * @var String 
     */
    protected $payPalSignature;
    
    /**
     * Поле хранит email клиента, который привязан к счету paypal.
     * Здесь имеется ввиду, что это email основного получателя платежей.
     * 
     * @var String 
     */
    protected $payPalEmail;
    
    /**
     * Поле хранит ссылку на объект PayResponse.
     * Объект нужен для получение значения payKey
     * 
     * @var PayPal\Types\AP\PayResponse 
     */
    protected $payResponse;

    /**
     * Адрес для редирикта пользователей на оплату на paypal.com
     * 
     * @var String 
     */
    const REDIRECT_URL_AP_PAYMENT = 'https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=';
    
    /**
     * 
     * @param String $payPalUserName Уникальное имя клиента в системе paypal для классического sdk api.
     * Значение необходимо получить на сайте paypal, добавить в конфиг и забирать его в контроллере.
     * 
     * @param String $payPalPassword Уникальный пароль клиента в системе paypal для классического sdk api.
     * Значение необходимо получить на сайте paypal, добавить в конфиг и забирать его в контроллере.
     * 
     * @param String $payPalSignature Уникальная сигнатура клиента в системе paypal для классического sdk api.
     * Значение необходимо получить на сайте paypal, добавить в конфиг и забирать его в контроллере.
     * 
     * @param String $payPalEmail Email клиента, который привязан к счету paypal.
     * Значение необходимо получить от клиента, в пользу которого должны поступать платежи, добавить в конфиг и забирать его в контроллере.
     */
    public function __construct($payPalUserName,$payPalPassword,$payPalSignature,$payPalEmail=null) 
    {
        $this->payPalUserName = $payPalUserName;
        $this->payPalPassword = $payPalPassword;
        $this->payPalSignature = $payPalSignature;
        
        if (!is_null($payPalEmail))
            $this->payPalEmail = $payPalEmail;
    }

    /**
     * Метод создает конфигурацию для adaptivepayments-sdk
     * 
     * @return sdkConfig[] массив настроек
     */
    protected function createSdkConfig()
    {
        $sdkConfig = array();
        
        $sdkConfig['mode'] = "sandbox";
        $sdkConfig['acct1.UserName'] = $this->payPalUserName;
        $sdkConfig['acct1.Password'] = $this->payPalPassword;
        $sdkConfig['acct1.Signature'] = $this->payPalSignature;
        $sdkConfig['acct1.AppId']="APP-80W284485P519543T";
        
        return $sdkConfig;
    }
    
    /**
     * Первый этап оплаты
     * На данном этапе происходит формирование полей для платежа и url для обработки на стороне нашего сайта
     * 
     * @param BasePayment $payment объект платежа из которого получаем данные для оплаты 
     * @param String $route в формате Controller/action на action контроллер который будет завершать наш платеж
     */
    public function startPayment(BasePayment $payment,$route)
    {
        $summa = $payment->getPrice();
        $amountForPrimaryReceiver = $summa;
        $amountForTeacherReceiver = ($summa*90)/100;
        $currency = $payment->getCurrency();
        $paymentId = $payment->getPk();
        $teacherId = $payment->booking->teacherId;
        $teacherEmail = User::model()->findByPk($teacherId)->email;
        $payRequest = new PayRequest();

        $receiver = array();
        
        $receiver[0] = new Receiver();
        $receiver[0]->amount = "$amountForPrimaryReceiver";
        $receiver[0]->email = "$this->payPalEmail";
        $receiver[0]->primary = TRUE;
        
        $receiver[1] = new Receiver();
        $receiver[1]->amount = "$amountForTeacherReceiver";
        $receiver[1]->email = "$teacherEmail";
        $receiver[1]->primary = FALSE;
        
        $receiverList = new ReceiverList($receiver);
        $payRequest->receiverList = $receiverList;

        $requestEnvelope = new RequestEnvelope("en_US");
        
        $payRequest->requestEnvelope = $requestEnvelope; 
        $payRequest->actionType = "PAY_PRIMARY";
        $payRequest->returnUrl = "$route?hpaypal_success=true&hpaypal_id=$paymentId";
        $payRequest->cancelUrl = "$route?hpaypal_success=false&hpaypal_id=$paymentId";
        $payRequest->currencyCode = "$currency";
        
        $adaptivePaymentsService = new AdaptivePaymentsService($this->createSdkConfig());
        try
        {
            $payResponse = $adaptivePaymentsService->Pay($payRequest);    
            
            if ($payResponse->responseEnvelope->ack == 'Failure')
            {
                Yii::log("Request: ".print_r($payRequest,true), CLogger::LEVEL_ERROR);
                Yii::log("Response: ".print_r($payResponse,true), CLogger::LEVEL_ERROR);
                return FALSE;
            }
            
            $payKey = $payResponse->payKey;
            $payment->setPayPalKey($payKey);
            $this->payResponse = $payResponse;
            //Debug::drop($payResponse);
        }
        catch (Exception $ex)
        {
            Yii::log("Error: ".print_r($ex->getMessage(),true), CLogger::LEVEL_ERROR);
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Так как в данной библиотеке, процесс оплаты происходит в один этап, то возврщаем true.
     * 
     * @param BasePayment $payment
     * @return boolean
     */
    public function completePayment(BasePayment $payment) 
    {
        return TRUE;
    }

    /**
     * Метод формирует выплаты от продовца покупателям.
     * 
     * @param BasePayout $payout выплата которую нужно провести
     * @return boolean вернет true если хоты бы один платеж был проведен.
     */
    public function executePayout(BasePayout $payout)
    {
        $count = 0;
        $payments = $payout->payments;
        
        foreach ($payments as $payment)
        {
            $res = $this->executePayment($payment);
            if ($res === TRUE)
                $count++;
        }
        
        if ($count > 0)
        {
            $payout->complete();
            return TRUE;
        }
            
        else
        {
            $payout->fail();
            return FALSE;
        }
            
    }

    /**
     * Метод формирует отложенные платежи.
     * Можно сказать что именно здесь происходят выплаты от главного получателя, вторичным.
     * 
     * @param BasePayment $payment
     * @return boolean
     */
    protected function executePayment(BasePayment $payment)
    {
        $requestEnvelope = new RequestEnvelope("en_US");
        $paykey = $payment->getPayPalKey();
        $executePaymentRequest = new ExecutePaymentRequest($requestEnvelope,$paykey);
        $executePaymentRequest->actionType = "PAY";
        $adaptivePaymentsService = new AdaptivePaymentsService($this->createSdkConfig());
        
        $res = $adaptivePaymentsService->ExecutePayment($executePaymentRequest);
        $responseEnvelope = $res->responseEnvelope;
        $error = $res->error;
        
        if ($responseEnvelope->ack === 'Success')
        {
            $payment->updatePaymentAfterSuccessPayout();
            return TRUE;
        }
        
        else 
        {
            Yii::log("Error: ".print_r($error,true), CLogger::LEVEL_ERROR);
            return FALSE;
            //die ($error[0]->message);
        }
    }

    /**
     * Возвращает ссылку для редирикта пользователя на сайт paypal.com для начала оплаты
     * 
     * @return string
     */
    public function getRedirectUrl()
    {
        $payKey = $this->payResponse->payKey;
        $url = self::REDIRECT_URL_AP_PAYMENT.$payKey;
        return $url;
    }
}

