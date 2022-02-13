<?php
//overriding production config
$config = include(__DIR__ . "/demo.php");
$db = &$config['components']['db'];
//Логирование всегда работает на локале
$db['enableProfiling'] = true;
$db['enableParamLogging'] = true;
$db['host'] = "mysql";
$db['dbname'] = "docker-lamp";
$db['username'] = 'user';
$db['password'] = 'root';

$config["params"]["baseUrl"] = "http://127.0.0.1:80";

return $config;
