<?php
//overriding production config
$config = include(__DIR__ . "/demo.php");
$db = &$config['components']['db'];
//Логирование всегда работает на локале
$db['enableProfiling'] = true;
$db['enableParamLogging'] = true;
$db['host'] = "127.0.0.1";
$db['dbname'] = "html";
$db['username'] = 'root';
$db['password'] = '';

$config["params"]["baseUrl"] = "http://127.0.0.1:8000";

return $config;
