<?php
require_once __DIR__ . "/lib-Yii/Bootstraper.php";
$bs = new Bootstraper(true);
$bs->disableDatabase();
$app = $bs->createWebApplication();
$app->run();