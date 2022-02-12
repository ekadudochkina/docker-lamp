<?php

include __DIR__ . "/../components/yii/AmazonBootstrapper.php";
$bs = new AmazonBootstrapper();
$bs->createTestApplication(false, false);
Yii::import("application.components.tests.*");
