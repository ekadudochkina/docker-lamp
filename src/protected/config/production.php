<?php
$config['name']= "Html";


$db = &$config['components']['db'];
//$db['host'] = "";
//$db['dbname'] = "";
//$db['username'] = "";
//$db['password'] = "";
//$db['pdoClass'] = "";


$config["import"][] = 'application.models.data.*';
$config["import"][] = 'application.components.interfaces.*';
$config["import"][] = 'application.components.yii.*';
$config["import"][] = 'application.components.commands.*';
$config["import"][] = 'application.components.amazon.*';
$config["import"][] = 'application.components.finders.*';
$config["import"][] = 'application.components.parsers.*';
$config["import"][] = 'application.components.alerts.*';

//$config['params']['writeLogFiles'] = true;
//$config['params']['mailHost'] = 'smtp.yandex.ru';
//$config['params']['mailSMTPAuth'] = true;
//$config['params']['mailSMTPSecure'] = "ssl";
//$config['params']['mailPort'] = 465;
//$config['params']['mailUsername'] = "";
//$config['params']['mailPassword'] = "";
//$config['params']['mailFrom'] = "";
//$config['params']['mailFromTitle'] = "";



$config['components']['urlManager']['rules'] = array(
    'landing' => 'landing/index',
    'gii' => 'gii',
    'gii/<controller:\w+>' => 'gii/<controller>',
    'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
    '<language:\w{2}>/<controller:\w+>/<action:\w+>/<id>' => '<controller>/<action>',
    '<language:\w{2}>/<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    '<language:\w{2}>/<controller:\w+>/' => '<controller>',
    '<language:\w{2}>' => 'landing/index',
    '/<language:\w{2}>' => '/',
    '<controller:\w+>/<action:\w+>/<id>' => '<controller>/<action>',   
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>'
);

$log = &$config['components']['log'];
$log['routes'][] =  array(
    'class'=>'WebLogRoute',
    'levels'=>'error,info,profile',
    'ignoreAjaxInFireBug'=>true,
    'enabled'=>defined("YII_DEBUG") && YII_DEBUG,
);
$db['enableProfiling'] = true;
$db['enableParamLogging'] = true;

return $config;
