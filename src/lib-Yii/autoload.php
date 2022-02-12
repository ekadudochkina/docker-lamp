<?php
/**
 * Автозагрузчик файлов из библиотеки
 * 
 * @package Hs
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @param String $class Имя класса
 */
function HsAutoload($class)
{
    //echo "$class\n";
    $parts = explode('\\', $class);
    if($parts[0] != "Hs")
        return;
    $filename = $parts[count($parts)-1].".php";
    unset($parts[count($parts)-1]);
    unset($parts[0]);
    
    $path = __DIR__."/components/".  strtolower(join("/",$parts))."/".$filename;
    
    //die($path);
    require $path;
}

spl_autoload_register("HsAutoload");