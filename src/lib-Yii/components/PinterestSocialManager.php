<?php

/**
 * Менеджер, облегчающий работу с Pinterest
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Social
 */
class PinterestSocialManager extends BaseSocialManager
{

    protected $appId;
    protected $secret;

    /**
     *
     * @var BaseController
     */
    protected $controller;

    /**
     *
     * @var Pinterest
     */
    private $client;

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
     * 
     * @return boolean True, в случае успеха
     */
    public function authorize()
    {
        $pinterest = $this->getClient();
        $route = $this->controller->getId() . "/" . $this->controller->getAction()->getId();
        $callbackUrl = $this->controller->createAbsoluteUrl($route);
        $loginurl = $pinterest->auth->getLoginUrl($callbackUrl, array('read_public', 'write_public'));

        $token = null;
        $code = $this->controller->getRequest()->getParam("code");
        if ($code)
        {
            //bug::drop($this);
            $token = $pinterest->auth->getOAuthToken($code);
            $pinterest->auth->setOAuthToken($token->access_token);
        }


        if (!$token)
        {
            //$loginurl = "https://ssl.home-studio.pro/zenviral/pinterest";
            $this->controller->redirect($loginurl);
            return false;
        }
        return true;
    }

    /**
     * Создает пин
     * 
     * @param String $message Сообщение
     * @param String $image Url изоображения
     * @param String $url Ссылка на материал
     * @return boolean True, Если пин успешно создан
     */
    public function createPin($message, $image, $url = null)
    {
        $pinterest = $this->getClient();

        $response = $pinterest->request->get("me/boards");

        $boards = $response->data;


        if (empty($boards))
        {
            $this->createBoard("board");
            return $this->createPin($message, $image, $url);
        }
        $response = $pinterest->request->get("me", ['fields' => "username"]);

        $me = $response->data;

        $userName = $me['username'];
        $boardName = $boards[0]['name'];

        $options = [];
        $options['note'] = $message . " " . $url;
        $options['board'] = $userName . "/" . $boardName;
        $options['image_url'] = $image;
        if ($url)
            $options['url'] = $url;
        $result = $pinterest->request->post("pins", $options);

        if (!empty($result->data))
            return true;
        return false;
    }

    /**
     * Создает доску для пинов
     * 
     * @param String $name Имя доски
     * @return boolean True, в случае успеха
     */
    public function createBoard($name)
    {
        $pinterest = $this->getClient();
        $result = $pinterest->request->post("boards", ['name' => $name]);
        if (!empty($result->data))
            return true;

        return false;
    }

    /**
     * Получение клиента Pinterest
     * 
     * @return \DirkGroenen\Pinterest\Pinterest
     */
    public function getClient()
    {
        if ($this->client)
            return $this->client;

        $pinterest = new DirkGroenen\Pinterest\Pinterest($this->appId, $this->secret);

        $this->client = $pinterest;
        return $pinterest;
    }

}
