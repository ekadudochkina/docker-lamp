<?php
namespace Hs\Helpers;
use Yii;
use FileHelper;
/**
 * Хелпер, помогающий найти файлы в проекте и получить другую инфомрацию.
 * Суть данного хелпера в том, что он обладает информацией о том, как устроены проекты в Home-Studio.
 *
 * @package Hs\Helpers
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class HomeStudioHelper
{
    
    protected static $allFilesAliases = [
        "application.controllers" => true,
        "application.views" => true,
        "application.components" => true,
        "application.models" => true,
        "root.lib-Yii.actions" => true,
        "root.lib-Yii.templates" => true,
        "root.lib-Yii.scripts" => true,
        "root.lib-Yii.tests" => true,
        "root.lib-Yii.views" => true,
        "root.lib-Yii" => false,
        "root.lib-Yii.components" => true,
    ];
    protected static $libClassesAliases = [
        "root.lib-Yii.components" => true,
        "root.lib-Yii.components.scripts.commands" => true,
        "root.lib-Yii.Bootstraper" => false,
    ];
    protected static $classAliases = [
        "root.lib-Yii.components" => true,
        "application.components" => true,
        "application.controllers" => true,
        "application.models" => true,
        //"root.lib-Yii.actions" => true,
        "root.lib-Yii.scripts.commands" => true,
        "root.lib-Yii.tests" => true,
        "root.lib-Yii.Bootstraper" => false,
    ];
    
    /**
     * Получает список всех файлов в папке, на которую указывает алиас
     * 
     * @param String[] $arrayOfAliases Массив алиасов Yii. Ключом является алиас, а значением рекурсивность поиска
     * @return String[] Список путей к файлам
     */
    protected static function getAllFilesFromAliases($arrayOfAliases)
    {
        $result = [];
        foreach ($arrayOfAliases as $aliases => $recursive)
        {
            $path = Yii::getPathOfAlias($aliases);
            //Если не директория, то это алиас файла
            if(!is_dir($path))
            {
                $result[] = $path;
                continue;
            }
            $files = FileHelper::getFilePathsInDirectory($path, $recursive);
            $result = array_merge($result, $files);
        }
        $filtered = \FileHelper::filterExtensionsFromArray($result,["php"]);
        return $filtered;
    }
    
    /**
     * Получает все файлы в проекте
     * 
     * @return String[] Список путей к файлам
     */
    public static function getAllFilesInProject()
    {
        return static::getAllFilesFromAliases(static::$allFilesAliases);
    }
    
    /**
     * Получает все файлы классов в проекте
     * 
     * @return String[] Список путей к файлам
     */
    public static function getAllClassesInProject()
    {
        return static::getAllFilesFromAliases(static::$classAliases);
    }
    
    /**
     * Получает имя класса из пути к его файлу. Учитывает наличие неймспейсов.
     * 
     * @param String $path Путь к файлу
     * @return string Полное имя класса
     */
    public static function getClassName($path)
    {
        $content = file_get_contents($path);
        $lines = explode("\n",$content);
        foreach($lines as $line)
        {
            $stripped = trim($line);
            if(\StringHelper::hasSubstring($stripped,"namespace") && strpos($stripped,"namespace") === 0)
            {
                $namespace = str_replace(["namespace",";"," ","\n","\r\n","\r","\t"],"",$line);
                $classname= ClassHelper::getClassName($path);
                //\bug::drop($classname,$namespace);
                $fullname= $namespace."\\".$classname;
                return $fullname;
            }
        }
        return ClassHelper::getClassName($path);
    }

    /**
     * Получает все файлы классов в библиотеке
     * 
     * @return String[] Список путей к файлам
     */
    public static function getLibClasses()
    {
        return static::getAllFilesFromAliases(static::$libClassesAliases);
    }

}
