<?php

/**
 * Менеджер социальной сети Instagramm
 *
 * @package Hs\Social
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class InstagramSocialManager
{

    protected $appId;
    protected $secret;
    protected $token;
    protected $pageId;
    protected $oauthToken = null;
    protected $verifier = null;

    /**
     * Клиент инстаграма
     * @var \MetzWeb\Instagram\Instagram
     */
    protected $client = null;

    /**
     *
     * @var BaseController
     */
    protected $controller;

    /**
     * 
     * @param BaseController $controller Текущий контроллер
     * @param String $id Открытый ключ
     * @param String $secret Секретный ключ
     */
    public function __construct($controller, $id, $secret)
    {
        $this->appId = $id;
        $this->secret = $secret;
        $this->controller = $controller;
    }

    /**
     * Авторизация
     * @return boolean true, В случае успеха
     */
    public function authorize()
    {
        $insta = $this->getClient();

        $code = $this->controller->getRequest()->getParam("code", null);
        if (!$code)
        {
            $url = $insta->getLoginUrl(['basic', 'relationships']);

            $url = str_replace("+relationships", "+relationships+public_content", $url);
            $this->controller->redirect($url);
            //Ну типа логическое завершение. Редирект, конечно не позволит сюда дойти
            return false;
        }

        $data = $insta->getOAuthToken($code);
        $token = $data->access_token;

        $insta->setAccessToken($token);

        return true;
    }

    /**
     * Подписка на пользователя
     * 
     * @param String $name Имя пользователя на которого необходимо подписаться
     * @return boolean
     */
    public function follow($name)
    {
        try
        {
            $insta = $this->getClient();
            $userId = $this->getUserId($name);
            $response = $insta->modifyRelationship("follow", $userId);
            if ($response->meta->code == 200)
                return true;
        } catch (Exception $e)
        {
            
        }
        return false;
    }

    /**
     * Получение клиента Инстаграмма
     * 
     * @return \MetzWeb\Instagram\Instagram
     */
    public function getClient()
    {
        if ($this->client)
            return $this->client;
        $route = $this->controller->getId() . "/" . $this->controller->getAction()->getId();
        $callback = $this->controller->createAbsoluteUrl($route);

        $config = ['apiKey' => $this->appId, 'apiSecret' => $this->secret, "apiCallback" => $callback];
        $insta = new \MetzWeb\Instagram\Instagram($config);
        $this->client = $insta;
        return $insta;
    }

    /**
     * Получение идентификатора пользователя по его имени
     * 
     * @param String $name Имя пользователя
     * @return String идентификтор
     */
    public function getUserId($name)
    {
        $base = "https://api.instagram.com/v1/users/search";
        $params = ["q" => "$name", "access_token" => $this->getClient()->getAccessToken()];
        $url = UrlHelper::createUrl($base, $params);
        $client = new \GuzzleHttp\Client();
        $content = $client->get($url);
        $str = $content->getBody()->getContents();
        $response = CJSON::decode($str);
        if ($response['meta']['code'] !== 200)
            return null;
        $data = $response['data'];
        foreach ($data as $user)
        {
            if ($user['username'] == $name)
                return $user['id'];
        }
        return null;
        //bug::drop($data);
    }

}
