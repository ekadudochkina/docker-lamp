<?php

require_once __DIR__ . "/../Bootstraper.php";
$bs = new Bootstraper();

//Отключаем БД по умолчанию, ведь проекты бывают разные
$bs->disableDatabase();

$app = $bs->createConsoleApplication(__DIR__ . "/commands");
//Ведь наследование и композиция должны работать
//Добавляем файлы в автозагрузку
$alias = "root.lib-Yii.scripts.commands.*";
Yii::import($alias);
Yii::log("MODE:".EnvHelper::getCurrentMode());
//Взлетаем, капитан!
$app->run();
