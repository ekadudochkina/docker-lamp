<?php

use PayPal\Api\Amount;
//use PayPal\Api\Address;
//use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Payout;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\PayoutItem;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;

/**
 * Класс helper для работы с системой paypal.
 * 
 * По умолчанию работаем в боевом режиме, то есть в live
 * Если надо переключиться на тестовый, то нужно передать в конструктор значения для работы в sandbox режиме.
 * PayPalManager::METHOD_SANDBOX - устанавливаем тестовый режим работы
 * PayPalManager::LEVEL_SANDOX устанавливаем уровень логирования для тестового режима работы
 * <b>example:</b>
 *      <pre>//Пример создания объекта 
 * $manager = new PayPalManager($this,$clientId,$clientSecret,$currencyConversion,PayPalManager::METHOD_SANDBOX,PayPalManager::LEVEL_SANDOX)
 * 
 * ПЛАТЕЖИ
 * Проведение платежа происходит в два этапа.
 * Первый этап
 * На первом этапе происходит формирование платежа и url по которому мы редериктим клиента на 
 * сайт paypal.com
 * Для этого нужно в контроллере вызвать метод startPayment($payment,$route)-сформируем платеж.
 * И вызвать метод который начинается на getLink-сформирeм url.
 * Второй этап
 * На втором этапе нужно в контроллере вызвать метод completePayment() и в зависимости от 
 * параметров которые вернет нам paypal завершить платеж успехом(если клиент подтвердил оплату), 
 * либо завершить неуспехом(если клиент отменил оплату).
 * 
 * ВЫПЛАТЫ
 * Для формирование выплат в контроллере нужно вызывать метод executePayout($payout) и передать на вход payout
 * Сам payout формирует пользователь
 * 
 * ПОДПИСКИ
 * Подписка происходит в два этапа.
 * Первый этап 
 * Создаем подписку на сервис на который нужно подписать подписчика. 
 * Затем  редиректим пользователя на сайт paypal.
 * Второй этап
 * Здесь происходит обработка ответа от paypal на нашей стороне
 * В зависимости от ответа, мы подписываем подписчика на сервис или наооборот не подписываем.
 * 
 * ПЛАТЕЖИ
 * <b>example:</b>
 *      <pre>//Код контроллера
 * Первый этап создание платежа и перенаправление его на paypal
 * 
 *  public function actionStart($id){                    
 *       $payment= Payment::model()->findByPk($id);                
 *       $manager = new PayPalManager($this,$clientId,$clientSecret);     
 *       $manager->startPayment($payment,"payments/complete");
 *       $link=$manager->getLinkWithLogin();
 *       $this->redirect($link);
 *  }
 * 
 * Второй этап завершение оплаты на строне нашего сайта   
 * 
 *  public function actionComplete() {       
 *       $manager = new PayPalManager($this, new Payment(),$clientId,$clientSecret);
 *       $manager->completePayment();
 *       $payPalSuccess = $this->getRequest()->getParam('hpaypal_success',null);
 *         if($payPalSuccess === 'true'){
 *               $this->getUser()->setFlash("success", "Success payment");
 *         }
 *       
 *       elseif($payPalSuccess === 'false') {
 *           $this->getUser()->setFlash("danger", "Fail payment");
 *       }
 *  }
 * 
 * ВЫПЛАТЫ
 * Код контроллера для формирование выплат
 *  public function actionMakePayout() {
 *      $payout=$user->createPayout();
 *      $manager = new PayPalManager($this,$clientId,$clientSecret);     
 *      $manager->executePayout($payout);
 *  }
 *</pre>
 * @see BasePayment
 * 
 * ПОДПИСКИ
 * 
 * Методу startBilling передаем объект подписки 
 * и маршрут в формате controllerId/actionId на который должен вернуться подписчик 
 * <b>example:</b>
 *      <pre>//Код контроллера
 * Первый этап
 * Вызываем метод на старте формирования подписки
 * public function actionStartSubscribe($id)
 *   {
 *      $manager = new PayPalManager($this,$clientId,$clientSecret);
 *      $subscription = new Subscription()
 *      $manager->startBilling($subscription,$route);
 *      $link=$manager->getRederictLink();
 *      $this->redirect($link);
 * }
 * 
 * Второй этап
 * Вызываем данный метод для обработки ответа от paypal
 * 
 * public function actionCompleteSubscription()
 *   {
 *       $subscriptionId=$this->getRequest()->getParam('subscriptionId',null);
 *       $subscription = Subscription::model()->findByPk($subscriptionId);
 *       $manager = new PayPalManager($this,$clientId,$clientSecret);
 *       $result = $manager->executeBilling($subscription);
 *   }
 * 
 * @author Kustarov Dmitriy <dkustarov@home-studio.pro>
 * 
 * @todo реализовать возможность оплаты с кредитной карты сейчас не стали это делать из за настройки https
 */
class PayPalManager implements IPaymentManager
{
    
     /**
     * @var String
     * Поле хранит уникальный id клиента в системе paypal
     * Сам clientId клиент получает из paypal при создании приложения 
     */
     protected $clientId;
    
    /**
     * @var String
     * Поле хранит секрктный ключ клиента в системе paypal
     * Сам clientSecret клиент получает из paypal при создании приложения
     */
    protected $clientSecret;
    
    /**
     * @var PaymentsController Объект
     * Поле хранит ссылку на класс контроллера
     */
    protected $controller;
    
    /**
     * @var BasePayment Объект
     * Поле хранит ссылку на класс модели 
     */
    protected $model;
    
    /**
     * @var PayPal\Api\Payment  Объект
     * поле хранит ссылку на объект PayPal\Api\Payment
     */
    protected $payment;
    
    /**
     * @var PayPal\Api\Plan  Объект
     * поле хранит ссылку на объект PayPal\Api\Plan
     */
    protected $plan;
    
    /**
     * @var PayPal\Api\Agreement Объект
     * поле хранит ссылку на объект PayPal\Api\Agreement
     */
    protected $agreement;
    
    /**
     * Смысл данного поля в том, что надо ли нам конвертировать валюту
     * По умолчанию не конвертируем, то есть задаем значение false в  __construct 
     * @var Boolean 
     */
    protected $currencyConversion;
    
    /**
     * Поле хранит режим работы с paypal.
     * По умолчанию назначаем боевой режим, то есть live
     * 
     * @var String 
     */
    protected $method = self::METHOD_LIVE;
            
    /**
     * Режим логирования paypal
     * Здесь важно следующее:
     * Если запущены в режиме sandbox, то ставим DEBUG, если в live FINE
     * 
     * Так как по умолчанию работаем в боевом режиме, то назначаем FINE
     * @var String 
     */
    protected $logLevel = self::LEVEL_LIVE;
    
    /**
     * @var String Режим работы в тестовом режиме  
     */
    const METHOD_SANDBOX = 'sandbox';
    
    /**
     * @var String Режим работы в боевом режиме
     */
    const METHOD_LIVE = 'live';
    
    /**
     * @var String Режим логирования для тестового режима
     */
    const LEVEL_SANDOX = 'DEBUG';
    
    /**
     * @var String Режим логирования для боевого режима
     */
    const LEVEL_LIVE = 'FINE';
    
    /**     
     * @param Сontroller $controller Контроллер, желательно текущий
     * @param String $clientId Уникальный идентификатор клиента в системе paypal.
     * Значение необходимо получить на сайте paypal, добавить в конфиг и забирать его в контроллере.
     * @param String $clientSecret Секрктный ключ клиента в системе paypal.     
     * Значение необходимо получить на сайте paypal, добавить в конфиг и забирать его в контроллере.
     * @param Boolean $currencyConversion @see currencyConversion
     * @param String $method По умолчанию режим работы боевой - live. Если требуется переключить на тестовый, то передайте конструктуру PayPalManager::METHOD_SANDBOX
     * @param String $logLevel @see Уровень логирования. По умолчанию значение равно self::LEVEL_LIVE, так как работает в боевом режиме. Если требуется переключить на тестовый, то передайте конструктуру PayPalManager::LEVEL_SANDBOX
     */
    public function __construct(CController $controller,$clientId,$clientSecret,$currencyConversion = FALSE, $method= null, $logLevel= null)
    {        
        require_once(Yii::app()->basePath . "/../lib-Yii/components/paypal/PayPal-PHP-SDK/autoload.php");
        require_once (__DIR__.'/Rbc.php');
        
        $this->controller=$controller;
        $this->clientId=$clientId;
        $this->clientSecret=$clientSecret;
        $this->currencyConversion = $currencyConversion;
        
        if (!is_null($method))
            $this->method = $method;
        
        if (!is_null($logLevel))
            $this->logLevel= $logLevel;
    }
    
    /**
     * 
     * Метод сохранит ссылку на объект BasePayment
     * @return String      
     * 
     * @deprecated Исходя из текущей архитектуры данный метод более не требуется
     */
    public function start(BasePayment $model)
    {
        $this->model=$model;
    }

    /**    
     * Метод вернет уникальный id клиента
     * @return String      
     */
    protected function getClientId() 
    {
        return $this->clientId;
    }
    
    /**    
     * Метод вернет Секрктный ключ клиента 
     * @return String      
     */
    protected function getClientSecret() 
    {
        return $this->clientSecret;
    }
    
     /**    
     * Метод создает конфигурацию для PayPal-PHP-SDK
     * @return PayPal\Rest\ApiContext $apiContext     
     */
    public function createApiContext() 
    {
        $apiContext = new ApiContext(
                new OAuthTokenCredential($this->getClientId(), $this->getClientSecret())
        );
                
        $conf = array(
            'mode' => $this->method,
            //'APIUsername' => 'freddis336-seller_api1.gmail.com',
            //'APIPassword' => 'PCPWCPGAG2LB98FR',
            //'APISignature' => 'Aw4rBIeCVJMM0rz0W5ON7l.80OlpA1J865nZSyKe.ywqnuUZmVLxQ437',
            'log.LogEnabled' => true,
            'log.FileName' => Yii::app()->basePath . '/runtime/PayPal.log',
            'log.LogLevel' => $this->logLevel, // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS AND DEBUG IN SANDBOX
            'cache.enabled' => false,
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
        );
        $apiContext->setConfig($conf);
        
        return $apiContext;
    }
    
    /**
     * Первый этап оплаты
     * На данном этапе происходит формирование полей для платежа и url для обработки на стороне нашего сайта     
     * @param BasePayment $obj объект платежа из которого получаем название сервиса и цену
     * @param String $route в формате idController/actionId на action контроллер который будет завершать наш платеж       
     */
    public function startPayment(BasePayment $obj,$route) {
                
        $obj->start();        
        $apiContext = $this->createApiContext();
        
        $payer = new Payer();
        //A resource representing a Payer's funding instrument. For direct credit card payments, set the CreditCard field on this object.
        $payer->setPaymentMethod("paypal");
        
        $item1 = new Item();
        $item1->setName($obj->getTitle())
                ->setCurrency($obj->getCurrency())
                ->setQuantity(1);
        
        if ($this->currencyConversion)
        {
            //Объект для получения курса валют
            $rbc = new Rbc();
        
            //Курс $ на сегодня, в скобках официальный код валюты
            $usd = $rbc->curs(840);
            $item1->setPrice($obj->getPrice()*$usd);
        }
        
        else 
        {
            $item1->setPrice($obj->getPrice());    
        }
        
                
        $itemList = new ItemList();
        $itemList->setItems(array($item1));
        
        //amount
        $amount = new Amount();
        $amount->setCurrency($item1->getCurrency())
               ->setTotal($item1->getPrice());
       
        $id = uniqid();
        
        //transaction
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("Payment description")
                ->setInvoiceNumber($id);        
        //url
        $redirectUrls = new RedirectUrls();
        $paymentId=$obj->getPk();
        $redirectUrls->setReturnUrl("$route?hpaypal_success=true&hpaypal_id=$paymentId")
                ->setCancelUrl("$route?hpaypal_success=false&hpaypal_id=$paymentId");
        //payment
        $payment = new Payment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));
        $payment->create($apiContext);
        
        $this->payment=$payment;
    }
    
    /**
     * Второй этап оплаты
     * На данном этапе происходит обработка платежа на стороне нашего сайта
     * Вернет TRUE в случае успешной обработки платежа и false в случае если платеж отменен.
     * @param BasePayment $obj пустой объект платежа
     * @return Boolean true|false
     */
    public function completePayment(BasePayment $obj) 
    {
        
        $payPalSuccess = $this->controller->getRequest()->getParam('hpaypal_success',null);
        
        $paymentIdSelf=$this->controller->getRequest()->getParam('hpaypal_id',null);     
        $paymentId=$this->controller->getRequest()->getParam('paymentId',null);
        $PayerId=$this->controller->getRequest()->getParam('PayerID',null);           
        $payment = $obj->findByPk($paymentIdSelf);
         
        if ($payPalSuccess === 'true') 
        {
            $this->completePaymentSuccess($paymentId,$PayerId,$payment);
            return TRUE;
        }
        
        elseif($payPalSuccess === 'false') 
        {
            $this->completePaymentFail($payment);
            return false;
        }       
    }
    
    /**
     * Метод вызывается если клиент подтвердил оплату на paypal.com                
     * @param Integer $paymentId id платежа который вернул нам paypal
     * @param Integer $PayerId id плательщика который вернул нам paypal
     * @param BasePayment $payment платеж по которому нужно завершить оплату     
     */
    protected function completePaymentSuccess($paymentId,$PayerId,BasePayment $payment) 
    {
        $apiContext = $this->createApiContext();
        $paymentPayPal = Payment::get($paymentId, $apiContext);            
        $execution = new PaymentExecution();
        $execution->setPayerId($PayerId);
        
        $amount = new Amount();
        $amount->setCurrency($payment->getCurrency());
        
        if ($this->currencyConversion)
        {
            //Объект для получения курса валют
            $rbc = new Rbc();
        
            //Курс $ на сегодня, в скобках официальный код валюты
            $usd = $rbc->curs(840);
            $amount->setTotal($payment->getPrice()*$usd);
        }
        
        else 
        {
            $amount->setTotal($payment->getPrice());
        }
               
        $transaction = new Transaction();       
        $transaction->setAmount($amount);
        $execution->addTransaction($transaction);    
        $paymentPayPal->execute($execution, $apiContext);

        $payment->complete();        
    }
    
    /**
     * Метод вызывается если клиент не подтвердил оплату на paypal.com                     
     * @param ActiveRecord $payment платеж по которому нужно завершить оплату неуспехом     
     */
    protected function completePaymentFail($payment) 
    {
        $payment->fail();        
    }
    
    /**
     * Метод формирует выплаты от продовца покупателям
     * Вернет true в случае успешной выплаты; false в случае неуспешной
     * @param ActiveRecord $payout выплата которую нужно провести
     * @return Boolean true|false
     */
    public function executePayout($payout) {        
        $payout->start();
        $payout->savePayoutAndPayments();
        $apiContext = $this->createApiContext();
        $payouts = new Payout();

        $senderBatchHeader = new PayoutSenderBatchHeader();

        $senderBatchHeader->setSenderBatchId(uniqid())
            ->setEmailSubject("You have a Payout!");

        $senderItem = new PayoutItem();
        $senderItem->setRecipientType('Email')
            ->setNote('Thanks for your patronage!')
            ->setReceiver($payout->mail)
            ->setSenderItemId("2014031400023")
            ->setAmount(new Currency('{
                                "value":'.$payout->amount.','.
                                '"currency":"'.$payout->getCurrency().'"
                            }'));

        $payouts->setSenderBatchHeader($senderBatchHeader)
            ->addItem($senderItem);
        
        $payoutBatch=$payouts->createSynchronous($apiContext);        
        $transactionStatus=$this->getTransactionStatus($payoutBatch);
        $payoutStatus=  $this->getPayoutStatus($transactionStatus);
        
        if ($payoutStatus == TRUE){
            $payout->complete();
            return TRUE;
        }
        elseif($payoutStatus == FALSE){
            $payout->fail();
            return FALSE;  
        }
    }
    
    /**
     * Метод возвращает TRUE если выплата успешна; FALSE если неуспешна
     * @param String $transactionStatus
     * @return Boolean true|false
     */
    protected function getPayoutStatus($transactionStatus){
        if ($transactionStatus != 'SUCCESS')
            return FALSE;
        else return TRUE;
    }
    
    /**
     * Метод возвращает статус транзакции по выплате                     
     * @param PayPal\Api\PayoutBatch $payoutBatch 
     * @return String $transactionStatus
     */
    protected function getTransactionStatus($payoutBatch){
        
        $apiContext = $this->createApiContext();        
        $payoutBatchId = $payoutBatch->getBatchHeader()->getPayoutBatchId();
        $output = Payout::get($payoutBatchId, $apiContext);
        foreach ($output->items as $obj){
            $transactionStatus=$obj->getTransactionStatus();
        }
        return $transactionStatus; 
    }

    /**
     * Метод формирует базовый url для редерикта клиента на paypal.com     
     * @return String $baseLink
     */
    protected function getBaseUrl() {
        $payment=$this->payment;
        if ( !is_null($payment) ){
            
            $links = $payment->getLinks();
            foreach ($links as $key=>$value){
                if ($links[$key]->getMethod()=='REDIRECT')
                    $baseLink = $links[$key]->getHref();
            }
            return $baseLink;
        }
        else throw new Exception ('You must call method start() before proccessing payment');
    
    }

    /**
     * Метод формирует url только с логином и паролем для входа на paypal.com
     * По данноу url необходимо перекинуть клиента на paypal.com
     * @return String $linkWithLogin
     */
    public function getLinkWithLogin() {

        $linkWithLogin= $this->getBaseUrl().'#/checkout/login';
        return $linkWithLogin;
    }

    /**
     * Метод формирует url  с логином и паролем для входа на paypal.com и данными для оплаты через карту
     * По данноу url необходимо перекинуть клиента на paypal.com
     * @return String $linkWithMultistepsignup
     */
    public function getLinkWithMultistepSignup() {
    
        $replace='webapps/xoonboarding?abTestThrottle=xoon&';
        $search='cgi-bin/webscr?cmd=_express-checkout&';
        $linkWithMultistepsignup= str_replace($search, $replace, $this->getBaseUrl()).'#/checkout/multistepsignup/multistepsignupaddcard';
        return $linkWithMultistepsignup;
    }
    
    /**
     * Метод формирует url с данными для оплаты через карту на paypal.com
     * По данноу url необходимо перекинуть клиента на paypal.com
     * @return String $linkWithOnlyCard
     */
    public function getLinkWithOnlyCard() {
        $replace='webapps/xoonboarding?';
        $search='cgi-bin/webscr?cmd=_express-checkout&';
        $linkWithOnlyCard=str_replace($search, $replace, $this->getBaseUrl()).'#/checkout/guest';
        return $linkWithOnlyCard;
    }
    
    
    /**
     * Метод создает первоначальную подписку на сервис
     * @param SimpleSubscription $subscription ссылка на объект подписки
     * @param String $route в формате idController/actionId на action контроллера который будет завершать процесс оформления подписки
     */
    public function startBilling(SimpleSubscription $subscription,$route){
        
        $subscription->start();
        
        $apiContext = $this->createApiContext();
        // Create a new instance of Plan object
        $plan = new Plan();

        // # Basic Information
        // Fill up the basic information that is required for the plan
        $plan->setName($subscription->getService()->getTitle())
            ->setDescription('Template creation.')
            ->setType('fixed');

        // # Payment definitions for this billing plan.
        $paymentDefinitionRegular = new PaymentDefinition();

        // The possible values for such setters are mentioned in the setter method documentation.
        // Just open the class file. e.g. lib/PayPal/Api/PaymentDefinition.php and look for setFrequency method.
        // You should be able to see the acceptable values in the comments.
        $paymentDefinitionRegular->setName('REGULAR Payments')
            ->setType('REGULAR')
            ->setFrequency($subscription->getService()->getPeriod())
            ->setFrequencyInterval($subscription->getService()->getInterval())
            ->setCycles($subscription->getService()->getCycles());
        
        if ($this->currencyConversion)
        {
            //Объект для получения курса валют
            $rbc = new Rbc();
        
            //Курс $ на сегодня, в скобках официальный код валюты
            $usd = $rbc->curs(840);
            $paymentDefinitionRegular->setAmount(new Currency(array('value' => $subscription->getService()->getPrice()*$usd, 'currency' => $subscription->getService()->getCurrency())));
        }
        
        else 
        {
            $paymentDefinitionRegular->setAmount(new Currency(array('value' => $subscription->getService()->getPrice(), 'currency' => $subscription->getService()->getCurrency())));
        }
            

        // Charge Models
        /*$chargeModel = new ChargeModel();
        $chargeModel->setType('SHIPPING')
            ->setAmount(new Currency(array('value' => 10, 'currency' => $service->getCurrency())));

        $paymentDefinition->setChargeModels(array($chargeModel));*/
        
        //Если число циклов для пробной подписки не равно 0, занчит подписка с пробным периода
        if ($subscription->getService()->getTrialCycles() != 0)
        {
            $paymentDefinitionTrial = new PaymentDefinition();
            $paymentDefinitionTrial->setName('TRIAL Payments')
                ->setType('TRIAL')
                ->setFrequency($subscription->getService()->getTrialPeriod())
                ->setFrequencyInterval($subscription->getService()->getInterval())
                ->setCycles($subscription->getService()->getTrialCycles())
                ->setAmount(new Currency(array('value' => 0, 'currency' => $subscription->getService()->getCurrency())));
            
                $plan->setPaymentDefinitions(array($paymentDefinitionRegular,$paymentDefinitionTrial));
        }
        
        else
        {
            $plan->setPaymentDefinitions(array($paymentDefinitionRegular));    
        }
        
        $merchantPreferences = new MerchantPreferences();
        // ReturnURL and CancelURL are not required and used when creating billing agreement with payment_method as "credit_card".
        // However, it is generally a good idea to set these values, in case you plan to create billing agreements which accepts "paypal" as payment_method.
        // This will keep your plan compatible with both the possible scenarios on how it is being used in agreement.
        $merchantPreferences
            ->setReturnUrl("$route?hpaypal_success=true&subscriptionId=".$subscription->getPrimaryKey())
            ->setCancelUrl("$route?hpaypal_success=false&subscriptionId=".$subscription->getPrimaryKey())
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new Currency(array('value' => 1, 'currency' => $subscription->getService()->getCurrency())));
        
        $plan->setMerchantPreferences($merchantPreferences);
        
        try 
        {
            $plan->create($apiContext);
        } 
        catch (PayPal\Exception\PayPalConnectionException $e) 
        {
            echo '<pre>';
            echo $e->getData();
            exit(1);
        }
        
        $billing=$this->updateBilling($plan);
        $this->createBillingWithAgreement($billing,$subscription);
        return TRUE;
    }
    
    /**
     * Метод обновляет статус первоначальной подписке с created on active
     * @param PayPal\Api\Plan $billing 
     * @return PayPal\Api\Plan $billing
     */
    protected function updateBilling($billing){
        
        $patch = new Patch();

        $value = new PayPalModel('{
                   "state":"ACTIVE"
                 }');

        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);
    
        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);
        
        $billing->update($patchRequest, $this->createApiContext());
        $billing = Plan::get($billing->getId(), $this->createApiContext());
        
        return $billing;
    }

    /**
     * Метод формирует подписку с пользовательским соглашением
     * @param PayPal\Api\Plan $billing
     * @param SimpleSubscription $subscription ссылка на объект подписки
     */
    protected function createBillingWithAgreement($billing,SimpleSubscription $subscription){
        
        $startDate=DateTimeHelper::timestampToIco(time()+60*60);
        
        $agreement = new Agreement();
        $titleForDescription='Subscription on '.$subscription->getService()->getTitle();
        
        if ($this->currencyConversion)
        {
            //Объект для получения курса валют
            $rbc = new Rbc();
            die($this->currencyConversion);
            //Курс $ на сегодня, в скобках официальный код валюты
            $usd = $rbc->curs(840);
            $pricetitleForDescription = ' Price: '.$subscription->getService()->getPrice()*$usd;
        }
        else
        {
            $pricetitleForDescription = ' Price: $'.$subscription->getService()->getPrice();    
        }
        
        $description = $titleForDescription.$pricetitleForDescription;
        $agreement->setName($subscription->getService()->getTitle())
            ->setDescription($description)
            ->setStartDate($startDate);
        // Add Plan ID
        // Please note that the plan Id should be only set in this case.
        $plan = new Plan();
        $plan->setId($billing->getId());
        
        $agreement->setPlan($plan);

        // Add Payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        
        $agreement->setPayer($payer);
       
        // Add Shipping Address
        /*$shippingAddress = new ShippingAddress();
        $shippingAddress->setLine1('Ленина 1')
            ->setCity('Москва')
            //->setState('CA')
            ->setPostalCode('115657')
            ->setCountryCode('RU');
        $agreement->setShippingAddress($shippingAddress);*/
        
        try 
        {
            $agreement->create($this->createApiContext());
            $this->agreement=$agreement;
        } 
        catch (PayPal\Exception\PayPalConnectionException $e) 
        {
            echo '<pre>';
            echo $e->getData();
            exit(1);
        }
    }
    
    /**
     * Обработка подписки на стороне нашего сайта
     * Вернет TRUE в случае если пользователь подписался на подписку и false в случае если пользователь отказался
     * @param SimpleSubscription $subscription ссылка на объект подписки
     * @return Boolean true|false
     */
    public function executeBilling(SimpleSubscription $subscription)
    {   
        $payPalSuccess = $this->controller->getRequest()->getParam('hpaypal_success',null);
        $token=$this->controller->getRequest()->getParam('token',null);
        
        $agreement = new Agreement();
        
        if ($payPalSuccess === 'true') 
        {
            try
            {
                $agreement->execute($token, $this->createApiContext());
                $agreementId=$agreement->getId();
                $subscription->turnOn($agreementId);
            }
            catch (PayPal\Exception\PayPalConnectionException $e) 
            {
                echo $e->getData();
                exit(1);
            }
            return TRUE;
        }
        
        elseif($payPalSuccess === 'false') 
        {
            $subscription->fail();
            $subscription->addActionError('You have unsubscribed from');
            return FALSE;
        }  
    }

    /**
     * Метод возвращает url по которому необходимо перекинуть клиента на paypal.com для оформления подписки
     * @return String 
     */
    public function getRederictLink(){
        return $this->agreement->getApprovalLink();
    }
    
    /**
     * Метод возвращает текущую подписку пользователя
     * @param String $id Description уникальный идентификатор подписки на сайте paypal
     * @return PayPal\Api\Agreement
     */
    public function getBillingAgreement($id){
        
        try{
            $agreement = Agreement::get($id,$this->createApiContext());  
        }
        catch (PayPal\Exception\PayPalConnectionException $e) {
                echo $e->getData();
                exit(1);
            }
        return $agreement;
    }
    
    /**
     * Метод отменяет текущую подписку пользователя на стороне paypal и на нашей стороне
     * @param SimpleSubscription $model объект подписки которую необходимо отменить
     * @param String $isSuspend если данный параметр TRUE то значит отменяем подписку как на стороне нашего сайта так и на стороне paypal
     * @return Boolean true|false
     */
    public function suspendSubscription(SimpleSubscription $model,$isSuspend)
    {
        $createdAgreement = $this->getBillingAgreement($model->getUniqueIdentificatorSubscription());
        
        if ($createdAgreement->getState() != 'Active')
        {
            throw new Exception('Current subscription has been canceled');
        }
        
        //Create an Agreement State Descriptor, explaining the reason to suspend.
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote("Suspending the agreement");
        
        try 
        {
            $createdAgreement->suspend($agreementStateDescriptor, $this->createApiContext());
            // Lets get the updated Agreement Object
            $agreement = Agreement::get($createdAgreement->getId(), $this->createApiContext());
        } 
        catch (PayPal\Exception\PayPalConnectionException $e) 
        {
            echo $e->getData();
            exit(1);
        }

        if ($agreement->getState() == 'Suspended')
        {
            if ($isSuspend == TRUE)
                $model->turnOff();
            return TRUE;;
        }
        return FALSE;
    }
    
    /**
     * Метод восстанавливает подписку которая была отменена
     * @param SimpleSubscription $model
     * @return boolean
     */
    public function reactivateSubscription(SimpleSubscription $model)
    {
        $suspendedAgreement = $this->getBillingAgreement($model->getUniqueIdentificatorSubscription());
        
        //Create an Agreement State Descriptor, explaining the reason to suspend.
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote("Reactivating the agreement");
        
        try 
        {
            $suspendedAgreement->reActivate($agreementStateDescriptor, $this->createApiContext());
            // Lets get the updated Agreement Object
            $agreement = Agreement::get($suspendedAgreement->getId(), $this->createApiContext());
        } 
        catch (PayPal\Exception\PayPalConnectionException $e) 
        {
            echo $e->getData();
            exit(1);
        }

        if ($agreement->getState() == 'Active')
        {
            $model->reactive();
            return TRUE;;
        }
        return FALSE;
    }

    public function isSubscriptionActive(SimpleSubscription $currentSubscription)
    {
        $agreement = $this->getBillingAgreement($currentSubscription->getUniqueIdentificatorSubscription());
        if ($agreement->getState() == 'Active')
            return true;
        return false;
    }

}