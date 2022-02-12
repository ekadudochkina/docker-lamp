<?php
$config = include(__DIR__ . "/production.php");

$db = &$config['components']['db'];
//$db['host'] = "db.asin24.com";
return $config;
