<?php
include __DIR__ . "/../lib-Yii/Bootstraper.php";
$bs = new Bootstraper();

//Отключаем БД по умолчанию, ведь проекты бывают разные
$bs->disableDatabase();

$commandPaths = [
    __DIR__ . "/../lib-Yii/scripts/commands",
];
$app = $bs->createConsoleApplication($commandPaths);

//Ведь наследование и композиция должны работать
//Добавляем файлы в автозагрузку
$alias = "root.lib-Yii.scripts.commands.*";
Yii::import($alias);

Debug::enableProfilingInConsole();

Yii::log("MODE:".EnvHelper::getCurrentMode());
Yii::log(print_r($_SERVER["argv"],1));
//Взлетаем, капитан!
$app->run(); 
