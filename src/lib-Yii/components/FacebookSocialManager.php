<?php

/**
 * Социальный менеджер для фейсбука
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Social
 */
class FacebookSocialManager extends BaseSocialManager
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
     * @var \Facebook\Facebook
     */
    private $client;

    public function __construct($controller, $id, $secret)
    {
        $this->appId = $id;
        $this->secret = $secret;
        //$this->token = $token;
        //$this->pageId = $pageId;
        $this->controller = $controller;

//	$path = Yii::getPathOfAlias("webroot.lib-Yii.components.vendor");
//	$file = StringHelper::joinPaths($path, "autoload.php");
//	
//	spl_autoload_unregister(array('YiiBase','autoload'));        
//        require_once $file; 
//	spl_autoload_register(array('YiiBase','autoload'));        
    }

    /**
     * Публикация новостей в соациальной сети
     * 
     * @param IOpenGraphObject $object Объект OpenGrapgh
     * @return boolean True, если авторизация прошла успешно
     * @throws Exception
     */
    public function publishNews(IOpenGraphObject $object)
    {
        $result = true;

        try
        {

            $options = array();
            $options['app_id'] = $this->appId;
            $options['app_secret'] = $this->secret;
            $options["default_graph_version"] = "v2.6";

            $fb = new Facebook\Facebook($options);
//
//	    $params = array();
//	    $params['client_id'] = $this->appId;
//	    $params['client_secret'] = $this->secret;
//	    $params['grant_type'] = "client_credentials";
//	    
//	   // bug::drop($params);
//	    $query = UrlHelper::createParams($params);
//	    //$token  = $fb->get("oauth/access_token?".$query);
//	    //$response  = $fb->sendRequest("GET","/oauth/access_token",$params,"240780112687960|2VG73p_pFoy0P8QzZf3GHIPjoH0");
//	
//	    $token = $response->getDecodedBody()['access_token'];
//	   // bug::drop($token);
            $token = $this->token;
            // $token = $this->appId."|".$this->secret;
            //получаем более правильный токен
            $response = $fb->get('/me/accounts', $token);
            foreach ($response->getDecodedBody()['data'] as $account)
                if ($account["id"] == $this->pageId)
                    $token = $account['access_token'];


            $params = array(
                "message" => "",
                "published" => true,
                "picture" => $object->getImageUrl($this->controller),
                "link" => $object->getUrl($this->controller),
                "from" => "527510427433813", //$this->pageId,
            );

            //Вот тут можно другие токены получить, если текущий токен короткий
            //$response = $fb->get('/me/accounts',$token);
            //bug::drop($response);

            $response = $fb->post('/' . $this->pageId . '/feed', $params, $token);
            bug::drop($response);
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
     * @return boolean True, если авторизация прошла успешно
     */
    public function authorize()
    {
        $fb = $this->getClient();
        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email']; // Optional permissions
        $route = $this->controller->getId() . "/" . $this->controller->getAction()->getId();
        $callback = $this->controller->createAbsoluteUrl($route);

        $helper = $fb->getRedirectLoginHelper();

        try
        {
            $accessToken = $helper->getAccessToken();
            // echo "success";
            // exit;
        } catch (Facebook\Exceptions\FacebookResponseException $e)
        {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e)
        {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!$accessToken)
        {
            $loginUrl = $helper->getLoginUrl($callback, $permissions);

            $this->controller->redirect($loginUrl);
            return false;
        }
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
       // echo '<h3>Metadata</h3>';
        //var_dump($tokenMetadata);

// Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($this->appId); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived())
        {
            // Exchanges a short-lived access token for a long-lived one
            try
            {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e)
            {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }

           // echo '<h3>Long-lived</h3>';
            //var_dump($accessToken->getValue());
        }

        $fb->setDefaultAccessToken($accessToken);
       return true;
    }

    /**
     * Делится новостями в ленте клиента
     * 
     * @param String $msg Сообщение
     * @param String $url Url страницы
     * @return boolean True, если успешно
     */
    public function share($msg,$url)
    {
        $fb = $this->getClient();
        
        $response = $fb->get('/me',[]);
        $data = $response->getDecodedBody();
        $userId = $data['id'];
        $params = [];
        $params['message'] = $msg." ".$url;
        
        
        $response = $fb->post("$userId/feed",$params);
        $data = $response->getDecodedBody();
        if($data['id'])
            return true;
        return false;
    }
    
    /**
     * Получение клиента для фейсбука
     * 
     * @return \Facebook\Facebook
     */
    public function getClient()
    {
        if ($this->client)
            return $this->client;
        $options = array();
        $options['app_id'] = $this->appId;
        $options['app_secret'] = $this->secret;
        $options["default_graph_version"] = "v2.6";

        $fb = new Facebook\Facebook($options);
        $this->client = $fb;
        return $fb;
    }

}
