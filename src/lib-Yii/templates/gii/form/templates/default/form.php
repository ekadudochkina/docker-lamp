<?php
//Ну это реально одна и та же вьюха, почему бы не заинклудить?
$file =  __DIR__."/../../../crud/templates/default/create.php";
$path = realpath($file);
//Debug::drop($file,$path);
include($path);