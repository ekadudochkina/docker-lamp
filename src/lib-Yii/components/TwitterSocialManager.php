<?php

/**
 * Менеджер социальной сети Twitter
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Social
 */
class TwitterSocialManager
{

    protected $key;
    protected $secret;
    protected $token;
    protected $pageId;
    protected $oauthToken = null;
    protected $verifier = null;

    /**
     *
     * @var BaseController
     */
    protected $controller;
    private $client;

    /**
     * 
     * @param BaseController $controller Текущий контроллер
     * @param String $id Открытый ключ
     * @param String $secret Секретный ключ
     */
    public function __construct($controller, $key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->controller = $controller;
    }

    /**
     * Авторизация
     * 
     * @return boolean True, в случае успеха
     */
    public function authorize()
    {
        $this->verifier = $this->controller->getRequest()->getParam("oauth_verifier");
        $this->oauthToken = $this->controller->getRequest()->getParam("oauth_token");
        $connection = $this->getClient();

        if ($this->oauthToken && $this->oauthToken == Yii::app()->session['oauth_request_token'])
        {
            try
            {
                //получаем access_token
                //Это что-то типа долгого токена
                $data = $connection->oauth("oauth/access_token", ['oauth_verifier' => $this->verifier]);
                $token = $data['oauth_token'];
                $tokenSecret = $data['oauth_token_secret'];

                $connection->setOauthToken($token, $tokenSecret);

                return true;
            } catch (Exception $e)
            {

                if (YII_DEBUG)
                    throw $e;
                //return false;
            }
            // bug::drop($content, Yii::app()->session['oauth_token']);
        }
        $route = $this->controller->getId() . "/" . $this->controller->getAction()->getId();

        $callbackUrl = $this->controller->createAbsoluteUrl($route);
        //$callbackUrl .= "?msg=" . urlencode($msg);
        $connection = new Abraham\TwitterOAuth\TwitterOAuth($this->key, $this->secret);

        //Для outh_callback нужно указать хоть какой-то callback в настройках приложения твиттера
        //даже если мы 
        $data = $connection->oauth('oauth/request_token', ['oauth_callback' => $callbackUrl]);
        //bug::drop($data);
        $token = $data['oauth_token'];
        $secret = $data['oauth_token_secret'];
        Yii::app()->session['oauth_request_token'] = $token;
        Yii::app()->session['oauth_request_secret'] = $secret;

        $url = $connection->url("oauth/authenticate", ['oauth_token' => $token]);
        $this->controller->redirect($url);
        return false;
    }

    /**
     * Отправка твита
     * 
     * @param String $msg Сообщение
     * @return boolean True, в случае успеха
     */
    public function tweet($msg)
    {

        $connection = $this->getClient();
        $content = $connection->post("statuses/update", ["status" => $msg]);
        return true;
    }

    /**
     * Публикация новостей
     * 
     * @depricated Метод не дописан
     * @param IOpenGraphObject $object Объект, которым можно поделиться
     * @return boolean True, в случае успеха
     * @throws Exception
     */
    public function publishNews(IOpenGraphObject $object)
    {
        $result = true;
        $oauthAccessToken = "617904731-dsYwF1rmnuWuWJsWRBqOxcIFc2RizylVUhqixi5x";
        $oauthAccessTokenSecret = "jfbRnG12VguEkUGE1gqoj52qPmn7tsNUBCgV5uAXHGiFL";
        $consumerKey = "yxfdZ2ntXVJbzKFNNYwCOQLX6";
        $consumerSecret = "AUvH7XdeVGX0gFl3RVtzhNmgIh9FHlyNkS9bKSTRDSKhu5CRsD";

        try
        {
            $connection = new \Abraham\TwitterOAuth\TwitterOAuth($consumerKey, $consumerSecret, $oauthAccessToken, $oauthAccessTokenSecret);
            $msg = $object->getTitle() . "\n " . $object->getUrl($this->controller);
            $content = $connection->post("statuses/update", ["status" => $msg]);
        } catch (Exception $ex)
        {
            $result = false;
            throw $ex;
        }


        return $result;
    }

    /**
     * Получение клиента LinkedIn.
     * 
     * @return LinkedIn
     */
    public function getClient()
    {
        if ($this->client)
            return $this->client;
        $consumerKey = $this->key;
        $consumerSecret = $this->secret;

        $connection = new Abraham\TwitterOAuth\TwitterOAuth($consumerKey, $consumerSecret, Yii::app()->session['oauth_request_token'], Yii::app()->session['oauth_request_secret']);
        $this->client = $connection;
        return $connection;
    }

}
