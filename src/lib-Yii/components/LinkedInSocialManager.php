<?php

use LinkedIn\LinkedIn;

/**
 * Социальный менеджер для сети LinkedIn
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Social
 */
class LinkedInSocialManager extends BaseSocialManager
{

    protected $appId;
    protected $secret;
    protected $token;
    protected $pageId;

    /**
     *
     * @var BaseController
     */
    protected $controller;

    /**
     *
     * @var LinkedIn
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


        $li = new LinkedIn(
                array(
            'api_key' => $this->appId,
            'api_secret' => $this->secret,
            'callback_url' => "http://qaiwa.home-studio.pro/admin/publishLinkedIn/4"
                )
        );
        $url = $li->getLoginUrl(
                array(
                    LinkedIn::SCOPE_BASIC_PROFILE,
                    LinkedIn::SCOPE_EMAIL_ADDRESS,
                    "rw_company_admin"
                )
        );


        $token = null;
        $code = $this->controller->getRequest()->getParam("code");
        if ($code)
        {
            $token = $li->getAccessToken($code);
        }
        //bug::drop($_REQUEST);

        if (!$token || !$li->getAccessTokenExpiration())
        {
            $this->controller->redirect($url);
        }

        $params = array();
        $params["comment"] = "Hello api world";
        $params["visibility"]["code"] = "anyone";
        $info = $li->post('/companies/10693172/shares', $params);
        bug::drop($info);

//
//	$linkedIn=new Happyr\LinkedIn\LinkedIn($this->appId,$this->secret,"json");
//	$linkedIn->setHttpClient(new \Http\Adapter\Guzzle6\Client());
//	$linkedIn->setHttpMessageFactory(new Http\Message\MessageFactory\GuzzleMessageFactory());
//
////	 if ($linkedIn->getAccessToken()) {
////            bug::drop($linkedIn->getAccessToken());
////        }
//	$x = new \Happyr\LinkedIn\AccessToken();
//	if ($linkedIn->hasError()) {
//	     echo "User canceled the login.";
//	    exit();
//	}
//	else if ($linkedIn->getAccessToken()) {
//	    //we know that the user is authenticated now. Start query the API
//	    $user=$linkedIn->get('v1/people/~:(firstName,lastName)');
//	    //print_r($user);
//	    echo "Welcome ".$user['firstName'];
//
//	    exit();
//	} 
//$url = $linkedIn->getLoginUrl();
//echo $url;
//	$this->controller->redirect($url);
//	die("11");
        try
        {
            
        } catch (Exception $ex)
        {
            $result = false;
            throw $ex;
        }


        return $result;
    }

    /**
     * Авторизация
     * 
     * @return boolean True, в случае успеха
     */
    public function authorize()
    {
        $li = $this->getClient();

        $url = $li->getLoginUrl(
                array(
                    LinkedIn::SCOPE_BASIC_PROFILE,
                    LinkedIn::SCOPE_EMAIL_ADDRESS,
                    "w_share"
                )
        );


        $token = null;
        $code = $this->controller->getRequest()->getParam("code");
        if ($code)
        {
            $token = $li->getAccessToken($code);
        }
        //bug::drop($_REQUEST);

        if (!$token || !$li->getAccessTokenExpiration())
        {
            $this->controller->redirect($url);
            return false;
        }
        //bug::drop($token);
        return true;
    }

    /**
     * Поделиться сообщением
     * 
     * @param String $comment Комментарий
     * @param String $url Url, к сообщению
     * 
     * @return Boolean True, в случае успеха
     * @throws Exception
     */
    public function share($comment, $url = null)
    {

        if ($url)
            $comment .=" " . $url;

        $li = $this->getClient();
        $params = array();
        $params["comment"] = $comment;
        $params["visibility"]["code"] = "anyone";


        try
        {
            $info = $li->post('/people/~/shares', $params);
        } catch (Exception $e)
        {
            if (YII_DEBUG)
                throw $e;

            return false;
        }
        if ($info['updateUrl'])
            return true;

        return false;
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

        $route = $this->controller->getId() . "/" . $this->controller->getAction()->getId();
        $callbackUrl = $this->controller->createAbsoluteUrl($route);
        $params = [
            'api_key' => $this->appId,
            'api_secret' => $this->secret,
            'callback_url' => $callbackUrl
        ];
        //bug::drop($params);
        $li = new LinkedIn($params);
        $this->client = $li;
        return $li;
    }

}
