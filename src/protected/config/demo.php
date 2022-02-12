<?php
//overriding production config
$config = include(__DIR__ . "/production.php");
$projectName = $config['name'];
$db = &$config['components']['db'];
//$db['host'] = "test.db.asin24.com";
$db['type'] = "mysql";
//$db['dbname'] = "amazontracker";
//$db['username'] = 'test';
//$db['password'] = 'demo';
//Логирование sql по умолчанию отключено
$db['enableProfiling'] = defined("YII_DEBUG") && YII_DEBUG;
$db['enableParamLogging'] = defined("YII_DEBUG") && YII_DEBUG;
$config['components']['db'] = $db;

// направляем результаты профайлинга в ProfileLogRoute 
// (отображается внизу страницы)
$log = &$config['components']['log'];
//$log['routes'][] =  array(
//    'class'=>'CProfileLogRoute',
//    'levels'=>'profile,info',
//    'ignoreAjaxInFireBug'=>true,
//    'enabled'=>defined("YII_DEBUG") && YII_DEBUG,
//);

//CWebLogRoute получше будет
//$log['routes'][] =  array(
//    'class'=>'CWebLogRoute',
//    'levels'=>'error,info,profile',
//    'ignoreAjaxInFireBug'=>true,
//    'enabled'=>defined("YII_DEBUG") && YII_DEBUG,
//);

return $config;
