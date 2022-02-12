<?php

require_once(__DIR__.'/adaptiveaccounts-sdk-php/PPBootStrap.php');


use PayPal\Service\AdaptiveAccountsService;
use PayPal\Types\AA\AccountIdentifierType;
use PayPal\Types\AA\GetVerifiedStatusRequest;


/**
 * Класс помощник для работы с платежной системой paypal через adaptiveaccounts-sdk api.
 * У данной библиотеки широкие возможности по созданию и валидации аккаунтов paypal.
 * 
 * 
 * ПРОВЕРКА НА ПРИНАДЛЕЖНОСТЬ АККАУНТА ПО EMAIL
 * 
 * Для проверки на существование аккаунта по email в контроллере надо вызвать метод validClient($email)
 * и передать на вход email, котрый мы хотим проверить.
 * 
 * 
 * ПРОВЕРКА НА ПРИНАДЛЕЖНОСТЬ АККАУНТА ПО EMAIL
 * <b>example:</b>
 *      <pre>//Код контроллера
 * 
 *  public function actionEditProfile()
 * {                    
 *       $user= $this->getCurrentUser();
 *       $manager = new PayPalAdaptiveAccountsManager($payPalUserName, $payPalPassword, $payPalSignature);     
 *       $manager->validClient($user->email);
 *  }
 * 
 */
class PayPalAdaptiveAccountsManager
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
     */
    public function __construct($payPalUserName,$payPalPassword,$payPalSignature) 
    {
        $this->payPalUserName = $payPalUserName;
        $this->payPalPassword = $payPalPassword;
        $this->payPalSignature = $payPalSignature;
    }

    /**
     * Метод создает конфигурацию для adaptiveaccounts-sdk
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
     * Проверка на то что аккаунт зарегистрирован в сисмеме paypal по email
     * Если аккаунт существует, то вернет true, иначе false
     * 
     * @param String $email email котрый мы хотим проверить.
     * @return boolean
     */
    public function validClient($email)
    {
        $getVerifiedStatus = new GetVerifiedStatusRequest();
        $accountIdentifier=new AccountIdentifierType();
        $accountIdentifier->emailAddress = $email;
        $getVerifiedStatus->accountIdentifier=$accountIdentifier;
        $getVerifiedStatus->matchCriteria='NONE';
        $service  = new AdaptiveAccountsService($this->createSdkConfig());
        try 
        {
            // ## Making API call
            // invoke the appropriate method corresponding to API in service
            // wrapper object
            $response = $service->GetVerifiedStatus($getVerifiedStatus);
        } 
        catch(Exception $ex) 
        {
            Yii::log("Response: ". print_r($ex->getMessage(), TRUE), CLogger::LEVEL_ERROR);
            return FALSE;
        }
        $ack = strtoupper($response->responseEnvelope->ack);
        
        if($ack != "SUCCESS")
        {
            return FALSE;
        }
        
        return TRUE;
    }
}

