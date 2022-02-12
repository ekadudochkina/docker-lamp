<?php

return array(
    'basePath' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."../protected"),
    'aliases' => array(
        'root' => realpath(__DIR__."/../../.."),
        'views' => realpath(__DIR__."/../../views")
        ),
    'name' => 'Yii-template',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
	'application.models.*',
	'application.components.*',
	'application.migrations.*',
        'root.lib-Yii.components.*',
	'ext.yii-mail.YiiMailMessage'
    ),
    'modules' => array(
	// uncomment the following to enable the Gii tool

	'gii' => array(
	    'class' => 'system.gii.GiiModule',
	    'password' => '1q2w3e4rDD',
            'generatorPaths'=>array(
			    'root.lib-Yii.templates.gii'
			),
	    // If removed, Gii defaults to localhost only. Edit carefully to taste.
	    'ipFilters' => array('77.37.206.158','192.168.0.*','127.0.0.1', '::1'),
	),
    ),
    // application components
    'components' => array(
        "messages" => array(
            "class" => "PhpMessageSource",
            "forceTranslation" => false,
         ),
        'assetManager'=> array(
	    'class' => "AssetManager",
	),
        'session'=> array(
	    'class' => "HttpSession",
	),
	'mailer' => array(
	    'class' => 'root.lib-Yii.extensions.mailer.EMailer',
	    'pathViews' => 'application.views.email',
	    'pathLayouts' => 'application.views.email.layouts'
	),
	'user' => array(
	    // enable cookie-based authentication
	    'allowAutoLogin' => true,
	    'class' => 'WebUser'
	),
	'urlManager' => array(
	    'class' => 'BilingualUrlManager',
	    'urlFormat' => 'path',
	    'showScriptName' => false,
	    'rules' => array(
                'gii' => 'gii',
                'gii/<controller:\w+>' => 'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
                
		'<language:\w{2}>/<controller:\w+>/<action:\w+>/<id>' => '<controller>/<action>',
		'<language:\w{2}>/<controller:\w+>/<action:\w+>' => '<controller>/<action>',
		'<language:\w{2}>/<controller:\w+>/' => '<controller>',
		'<language:\w{2}>' => 'site/index',
		'/<language:\w{2}>' => "/",
                
		//'<module:\w+>/<controller:\w+>/<action:\w+>/<id>' => '<module>/<controller>/<action>',
		'<controller:\w+>/<action:\w+>/<id>' => '<controller>/<action>',
		'<controller:\w+>/<action:\w+>' => '<controller>/<action>',             
	    ),
	),
	'authManager' => array(
	    'class' => 'DbAuthManager',
	    'connectionID' => 'db',
	),
	'db' => array(
            'class' => "DbConnection",
            'pdoClass' => "NestedPDO",
            'type' => 'mysql',
            'emulatePrepare' => true,
            'charset' => 'utf8',
            //Теперь вместо connectionString раздельные параметры
            //'connectionString' => 'mysql:host=localhost;dbname=template',
            'host' => '127.0.0.1',
            'dbname' => "template",
	    'username' => 'root',
	    'password' => '',
	    
	),
	'errorHandler' => array(
	    'errorAction' => 'site/error',
	),
	'log' => array(
	    'class' => 'CLogRouter',
	    'routes' => array(
		array(
		    'class' => 'CFileLogRoute',
		    'levels' => 'error, warning, info',
		),
                array(
                   'class'=>'EmailLogRoute',
                    'levels'=>'error, warning',
                    'emails'=>'system@home-studio.pro',
                )
	    ),
	),
    ),
    'params' => array(
	'adminEmail' => 'business@home-studio.pro',
//        'mailHost' => 'p3plcpnl0583.prod.phx3.secureserver.net',
//	'mailSMTPAuth' => true,
//	'mailSMTPSecure' => "ssl",
//	'mailPort' => 465,
//	'mailUsername' => "system@gympluscoffee.com",
//	'mailPassword' => "J+M9UxKr^;C_",
//	'mailFrom' => "system@gympluscofee.com",
//	'mailFromTitle' => "Gympluscoffee.com",
        'mailHost' => 'smtp.yandex.ru',
	'mailSMTPAuth' => true,
	'mailSMTPSecure' => "ssl",
	'mailPort' => 465,
	'mailUsername' => "system@home-studio.pro",
	'mailPassword' => "1q2w3e4rDD",
	'mailFrom' => "system@home-studio.pro",
	'mailFromTitle' => "new-home-studio-project.com",
        'emailForOrderBackups' => "system@home-studio.pro",
        //payments
        'stripeSecretKey' => 'sk_test_GSA7h5GSeGDMmroEFBB4GGTC',
        'stripePublicKey' => 'pk_test_3kwfshXYNZRczgbR5J8IB7s4',
        'hybridAuth' => array(
	    //поддомен www подставляется автоматически, если он есть
	    "base_url" => "http://yii-template.tk/lib/hybridauth/",
	    "providers" => array(
		// openid providers
		"OpenID" => array(
		    "enabled" => true
		),
		"Google" => array(
		    "enabled" => true,
		    "keys" => array("id" => "482048281364-f40c9tdkd8tdpveibr9tsnr1ii1akdo7.apps.googleusercontent.com", "secret" => "NyN_7u9MS0s_UA2q4taBL3Te"),
		),
		"Facebook" => array(
		    "enabled" => true,
		    "keys" => array("id" => "480512522156647", "secret" => "1db17820d07e53e72df29f03fdea539a"),
		    "trustForwarded" => false
		),
		"Twitter" => array(
		    "enabled" => true,
		    "keys" => array("key" => "zWbUp04M5ACjXkq5epPbTpqZS", "secret" => "muzPEe3yYrVExLIX4Yj1ubAbUcRsmKF00CN158Tg8HeGoBfJe1"),
		    "includeEmail" => true
		),
		"LinkedIn" => array(
		    "enabled" => true,
		    "keys" => array("key" => "779ov63rlgdht6", "secret" => "evWKcyBWJ1f0nWSz")
		)
	    ),
	    // If you want to enable logging, set 'debug_mode' to true.
	    // You can also set it to
	    // - "error" To log only error messages. Useful in production
	    // - "info" To log info and error messages (ignore debug messages)
	    "debug_mode" => false,
	    // Path to file writable by the web server. Required if 'debug_mode' is not false
	    "debug_file" => "",
 	),
    ),
    'sourceLanguage' => 'en_Us',
    'language' => 'en_Us',
);
