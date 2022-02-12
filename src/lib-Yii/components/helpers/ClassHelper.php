<?php
namespace Hs\Helpers;
use \ReflectionClass;
use \StringHelper;
use \ArrayHelper;
use \bug;
/**
 * Хелпер для работы с классами
 *
 * @package Hs\Helpers
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ClassHelper
{
    /**
     * Получение коментария к классу. 
     * Возвращает комментарий целиком с тегами аттрибутами и спецсимволами('*').
     * 
     * @param String $classname Имя класса
     * @return String Комментарий к модели
     */
    public static function getCommentForClass($classname)
    {
        $reflector = new \ReflectionClass($classname);
        $comment = $reflector->getDocComment();
        return $comment;
    }

    /**
     * Получает предполагаемое имя класса из пути к файлу.
     * 
     * @param String $path путь к файлу или имя файла класса
     * @return String Имя класса
     */
    public static function getClassName($path)
    {
        $file = basename($path);
        $str = str_replace(".php", "", $file);
        return $str;
    }

    /**
     * Получение коментария к полю класса. 
     * Возвращает комментарий целиком с тегами аттрибутами и спецсимволами('*').
     * 
     * @param String $classname Имя класса..
     * @param String $fieldname Имя поля модели
     * @return String Комментарий к модели
     */
    public static function getCommentForField($classname, $fieldname)
    {
        $reflector = new ReflectionClass($classname);
        $prop = $reflector->getProperty($fieldname);
        $comment = $prop->getDocComment();
        if($comment === false)
        {
            return null;
        }
        return $comment;
    }

    /**
     * Получение коментария к методу класса. 
     * Возвращает комментарий целиком с тегами аттрибутами и спецсимволами('*').
     * 
     * @param String $classname Имя класса.
     * @param String $methodname Имя метода модели
     * @return String Комментарий к модели
     */
    public static function getCommentForMethod($classname, $methodname)
    {
        $reflector = new ReflectionClass($classname);
        $prop = $reflector->getMethod($methodname);
        $comment = $prop->getDocComment();
        if($comment === false)
        {
            return null;
        }
        return $comment;
    }

    /**
     * Данная функцию возвращает информацию тега из сырого комментария.
     * 
     * @param String $comment Сырой комментарий (со спец-символами)
     * @param String $tagname Название тега, например "var" или "method"
     * @return String Строка от начала заданного тега до начала следующего
     */
    public static function parseCommentForTag($comment, $tagname)
    {
        $search = "@" . $tagname;
        $parts = static::stripComment($comment);
        
        $arr = array();
        $collect = false;
        foreach ($parts as $line)
        {
            if ($collect)
            {
                //останавливаемся, если нашли новый тэг
                if (preg_match("/@.{2,}/", $line) === 1)
                {
                    break;
                }
                $arr[] = $line;
            }

            if (strpos($line, $search) === 0)
            {
                $collect = true;
                $arr[] = $line;
            }
        }
        if (!$arr)
            return null;
        //bug::show($parts,$arr);
        $result = join("\n", $arr);
        return $result;
    }

    /**
     * Данная функцию возвращает информацию о тегах из сырого комментария.
     * 
     * @param String $comment Сырой комментарий (со спец-символами)
     * @param String $tagname Название тега, например "var" или "method". Если не указан, то функция вернет все теги.
     * @return String[] Массив тагов
     */
    public static function parseCommentForTags($comment, $tagname = "")
    {
        $array = array();
        //Нужно заранее очистить комментарий, иначе не получится сделать реплейс многострочных тэгов
        $stripped = static::stripComment($comment);
        $comment = join("\n",$stripped);
        while ($tag = static::parseCommentForTag($comment, $tagname))
        {
            $array[] = $tag;
            //bug::reveal($stripped,$tag,$comment);
            $comment = str_replace($tag, "", $comment);
        }
        //bug::drop($array);
        return $array;
    }

    /**
     * Данная функцию получает описательную часть из сырого комментария.
     * То есть ту часть, которая описывает комментируемый объект.
     * 
     * @param String $comment Сырой комментарий (со спец-символами)
     * @return String Описательная часть комментария.
     */
    public static function parseCommentForDescription($comment)
    {
        $parts = static::stripComment($comment);
        //\bug::drop($parts);
        $arr = array();
        foreach ($parts as $line)
            if (preg_match("/^@.{2,}/", $line) === 1)
                break;
            else
                $arr[] = $line;
        //\bug::drop($arr,$parts);
        if (empty($arr))
            return null;
        $result = join("\n", $arr);
        $result = trim($result);
        return $result;
    }

    /**
     * Очищает сырой комментарий от спецсимволов и пустых строк. 
     * 
     * @param String $comment Сырой комментарий (со спец-символами)
     * @return String[] Непустые строки комментария без спецсимволов
     */
    protected static function stripComment($comment)
    {
        $parts = explode("\n", $comment);
        $arr = array();
        foreach ($parts as $val)
        {
            $val = trim($val);
            $val = str_replace(["/*","*/"],"",$val);
            $val = trim($val, "*");
            $val = trim($val);
            $val = trim($val);
            if ($val != "")
                $arr[] = $val;
        }
        return $arr;
    }

    /**
     * Получение описания к классу. То есть та часть комментария, которая описывает назначение класса.
     * 
     * @param String $classname Имя класса
     * @return String Описательная часть комментария, без тэгов
     */
    public static function getDescriptionForClass($classname)
    {
        $comment = static::getCommentForClass($classname);
        if (!$comment)
        {
            return null;
        }
        $description = static::parseCommentForDescription($comment);
        if ($description)
        {
            return $description;
        }
        return null;
    }

    /**
     * Получение описания к классу. То есть та часть комментария, которая описывает назначение метода.
     * 
     * @param String $classname Имя класса
     * @param String $methodName Имя метода
     * @return String Описательная часть комментария, без тэгов
     */
    public static function getDescriptionForMethod($classname,$methodName)
    {
        $comment = static::getCommentForMethod($classname,$methodName);
        if (!$comment)
        {
            return null;
        }
        $description = static::parseCommentForDescription($comment);
        if ($description)
        {
            return $description;
        }
        return null;
    }
    
    /**
     * Получает строку тега для класса
     * 
     * @param String $classname Имя класса
     * @param String $tagname Название тега, например "var" или "method"
     * @return String Строка от начала заданного тега до начала следующего 
     * @throws Exception
     */
    public static function getTagForClass($classname, $tagname)
    {
        if (trim($tagname) == "")
            throw new Exception("Enter valid tag name");
        $comment = static::getCommentForClass($classname);
        $tag = static::parseCommentForTag($comment, $tagname);
        return $tag;
    }
    
    /**
     * Получает строку тега для поля класса
     * 
     * @param String $classname Имя класса
     * @param String $fieldname Имя поля модели
     * @param String $tagname Название тега, например "var" или "method"
     * @return String Строка от начала заданного тега до начала следующего 
     * @throws Exception
     */
    public static function getTagForField($classname, $fieldname, $tagname)
    {
        if (trim($tagname) == "")
            throw new Exception("Enter valid tag name");
        $comment = static::getCommentForField($classname, $fieldname);
        $tag = static::parseCommentForTag($comment, $tagname);
        return $tag;
    }

    /**
     * Получает строку тега для метода класса
     * 
     * @param String $classname Имя класса
     * @param String $methodname Имя метода модели
     * @param String $tagname Название тега, например "var" или "method"
     * @return String Строка от начала заданного тега до начала следующего 
     * @throws Exception
     */
    public static function getTagForMethod($classname, $methodname, $tagname)
    {
        if (trim($tagname) == "")
            throw new Exception("Enter valid tag name");
        $comment = static::getCommentForMethod($classname, $methodname);
        $tag = static::parseCommentForTag($comment, $tagname);
        return $tag;
    }

    /**
     * Получение описания к полю. То есть та часть комментария, которая описывает назначение поля.
     * 
     * @param String $classname Имя класса
     * @param String $fieldname Имя поля модели
     * @return String Описательная часть комментария, без тэгов
     */
    public static function getDescriptionForField($classname, $fieldname)
    {
        $comment = static::getCommentForField($classname, $fieldname);
        $description = static::parseCommentForDescription($comment);
        if ($description)
        {
            return $description;
        }

        //Если нет комментария сверху, то можно поискать его после имени типа в тэге @var
        $var = static::getTagForField($classname, $fieldname, "var");
        $var = StringHelper::removeDoubleSpaces($var);
        //Удаляем название тега, тип и название поля
        $parts = explode(" ", $var);
        $parts[0] = "";
        $parts[1] = "";
        //$parts[2] = ""; //@todo удалить, если не всплывает зачем было нужно
        $parts = ArrayHelper::removeEmptyElements($parts);
        $description = join(" ", $parts);

        if ($description != "")
        {
            return $description;
        }

        return null;
    }
    
    /**
     * Получает теги для класса
     * 
     * @param String $classname Имя класса
     * @param String $tagname Название тега, например "var" или "method". Если не указан, то возвращает все теги
     * @return String[] Строки тегов
     * @throws Exception
     */
    public static function getTagsForClass($classname, $tagname = "")
    {
        $comment = static::getCommentForClass($classname);
        //bug::drop($comment);
        $tags = static::parseCommentForTags($comment, $tagname);
        return $tags;
    }
    
    /**
     * Получает теги для поля класса
     * 
     * @param String $classname Имя класса
     * @param String $fieldname Имя поля
     * @param String $tagname Название тега, например "var" или "method". Если не указан, то возвращает все теги
     * @return String[] Строки тегов
     * @throws Exception
     */
    public static function getTagsForField($classname,$fieldname,$tagname = "")
    {
        $comment = static::getCommentForField($classname,$fieldname);
        $tags = static::parseCommentForTags($comment, $tagname);
        return $tags;
    }
    
    /**
     * Получает теги для поля класса
     * 
     * @param String $classname Имя класса
     * @param String $methodname Имя поля
     * @param String $tagname Название тега, например "var" или "method". Если не указан, то возвращает все теги
     * @return String[] Строки тегов
     * @throws Exception
     */
    public static function getTagsForMethod($classname,$methodname,$tagname = "")
    {
        $comment = static::getCommentForMethod($classname,$methodname);
        $tags = static::parseCommentForTags($comment, $tagname);
        return $tags;
    }
    
    /**
     * Получает значение тега из строки тега.
     * Если указывается параметр $position, то возвращается только часть тега,
     * являющаеся искомым значением
     * 
     * @param String $tag Целая строка тега
     * @param Int $position Номер слова в теге, которое является значением
     * @return String Значение тега
     */
    public static function getTagValue($tag,$position = null){
        $parts  = explode(" ",$tag);
        if($position !== null)
            return $parts[$position];
        
        $parts[0] = "";
        $str = join(" ",$parts);
        $result = trim($str);
        return $result;
    }
    
    /**
     * Получает название тега из строки тега
     * 
     * @param String $tag Целая строка тега
     * @param Bool $keepAt Флаг, оставлять ли собаку в имени тега
     * @return String Название тега
     */
    public static function getTagName($tag,$keepAt = false){
        $parts  = explode(" ",$tag);
        $name = $parts[0];
        if(!$keepAt)
            $name = substr ($name, 1);
        return $name;
    }
}
