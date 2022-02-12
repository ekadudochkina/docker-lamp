<?php

/**
 * Менеджер для социальной сети Google.
 *
 * Используется Oauth2, реквизиты легко создаются в консоле google.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Social
 */
class GoogleSocialManager
{

    /**
     *
     * @var BaseController
     */
    protected $controller;
    protected $clientId;
    protected $clientSecret;
    
    /**
     *
     * @var Google_Client
     */
    protected $client;

    /**
     * 
     * @param BaseController $controller Текущий контроллер
     * @param String $id Открытый ключ
     * @param String $secret Секретный ключ
     */
    public function __construct($controller, $id = null, $secret = null)
    {
        $this->clientId = $id;
        $this->clientSecret = $secret;
        $this->controller = $controller;
    }


    /**
     * Авторизация
     * 
     * @return boolean True, если авторизация прошла успешно
     */
    public function authorize()
    {
        $scopes = [];
        $scopes[] = "https://www.googleapis.com/auth/youtube";

        

        $client = $this->getClient();

        $oldToken = Yii::app()->session['access_token'];
        if ($oldToken)
        {
            $client->setAccessToken($oldToken);
            return true;
        }
        $code = $this->controller->getRequest()->getParam("code");
        if (!$code)
        {
            $url = $client->createAuthUrl($scopes);
            $this->controller->redirect($url);
            //строка ниже - это больше символизм. Не дойдет сюда.
            return false;
        }
        $data = $client->authenticate($code);
        $token = $data['access_token'];
        Yii::app()->session['access_token'] = $token;
        //bug::drop($code,$token);
        $client->setAccessToken($token);
    }

    /**
     * Подписка на канал  Youtube
     * 
     * @param String $channelId Идентификатор канала
     * @return boolean True, в случае успеха
     */
    public function subscribe($channelId)
    {
        $client = $this->getClient();
        $token = $client->getAccessToken();
       // Debug::drop($channelId);
        $conf = [];
        $conf['base_uri'] = "https://www.googleapis.com/";
        $conf['headers'] = ['Authorization' => 'Bearer '.$token['access_token']];
        $conf['headers']['Content-Type'] = 'application/json';
        
        $guzzle = new GuzzleHttp\Client($conf);
        //$response = $guzzle->get("/youtube/v3/subscriptions?part=id&mySubscribers=true",$params);
        //bug::show($response->getBody()->getContents(),$token);
        
        
        $resource = ['snippet'=>["resourceId"=>['kind'=>'youtube#channel', 'channelId' => $channelId]]];
        
        
        $response = $guzzle->post("/youtube/v3/subscriptions?part=snippet",['json'=>  $resource]);
        //bug::show($response->getBody()->getContents(),$token);
        return true;
        //die();
       
        
    }

    /**
     * Получение клинета Google.
     * 
     * @return \Google_Client
     */
    public function getClient()
    {
        if($this->client)
            return $this->client;
        
        $route = $this->controller->getId() . "/" . $this->controller->getAction()->getId();
        $uri = $this->controller->createAbsoluteUrl($route);
        $client = new Google_Client();
        $client->setRedirectUri($uri);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $this->client = $client;
        return $client;
    }

}
