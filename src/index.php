<?php
require_once __DIR__ . "/lib-Yii/Bootstraper.php";
$bs = new Bootstraper(false);
//$bs->disableDatabase();
$app = $bs->createWebApplication();
$app->run();