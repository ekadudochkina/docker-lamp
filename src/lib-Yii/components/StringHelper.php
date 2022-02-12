<?php

/**
 * Хелпер для работы со строками.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class StringHelper
{

    /**
     * Возвращает строку, ограниченную по количеству символов. Если символов слишком много, то ставит троеточие.
     * 
     * @param String $string Входящая строка
     * @param Integer $limit Максимальное количество символов в строке
     * @param String $add При достижении лимита эта строка завершает исходную. Эта строка неявным образом уменьшает лимит на свою длину.
     * @return String Укороченная строка 
     */
    public static function limit($string, $limit, $add= "...")
    {
	if (strlen($string) > $limit)
	{
	    $string = substr($string, 0, $limit - strlen($add));
	    $string .= $add;
	    return $string;
	}

	return $string;
    }

    /**
     * Форматирвоание десятичного числа
     * 
     * @param Float $number Число
     * @param Integer $numberAfterFloatingPoint Количество символов после точки
     * @param Interger $numberBeforeFloatingPoint Количество символов до точки (будут ведущие нули)
     * @return type
     */
    public static function formatDecimal($number, $numberAfterFloatingPoint = 2, $numberBeforeFloatingPoint = "")
    {
	$ret = sprintf("%$numberBeforeFloatingPoint.{$numberAfterFloatingPoint}f", $number);
	return $ret;
    }

    /**
     * Удаляет лишние пробелы из строки
     * 
     * @param String $string Исходная строка
     * @return String Строка без пробелов
     */
    public static function removeDoubleSpaces($string)
    {
	$parts = explode(" ", $string);
	$parts = ArrayHelper::removeEmptyElements($parts, true);
	$str = join(" ", $parts);
	return $str;
    }

    /**
     * Удаление специальных символов из строки. 
     * 
     * Данная функция нужна, так как функции mysql_escape_string устарели, а новым функциям необхоидмо подключание.
     * Тем не менее данная функция не безопасна для постоянного использования.
     * 
     * @param String $string Исходная строка
     * @return String Экранированная строка
     */
    public static function mysqlEscapeString($string)
    {
	return Yii::app()->getDb()->quoteValue($string);
    }
    
    /**
     * Проверяет есть ли вхождение подстрок в строку. По-умолчанию ищет любую из подстрок в строке.
     * 
     * @param String $haystack Исходная строка
     * @param String[] $needles Искомые подстроки
     * @param Bool $all Если True, то исходная строка должна иметь все подстроки
     * @param Bool $caseInsensitive Если True, то поиск не зависит от регистра
     * @return Bool True, если строка имеет подстроки
     */
    public static function hasSubstrings($haystack, $needles,$all=false,$caseInsensitive = false){
        foreach($needles as $needle){
            $has = StringHelper::hasSubstring($haystack, $needle,$caseInsensitive);
            if($has && !$all)
                return true;
            if(!$has && $all)
                return false;
        }
        //Этот момент может быть сложным
        //Если мы до сюда дошли, значит ленивый выход не сработал
        //Соответсвенно, если мы искали все подстроки, то все подстроки имеются
        //Если мы искали хотябы одну подстроку, то ни одной из них нет в строке
        if($all)
            return true;
        else
            return false;          
    }
    
    /**
     * Проверяет есть ли вхождение подстроки в строку
     * 
     * @param String $haystack Исходная строка
     * @param String $needle Искомая подстрока
     * @param Bool $caseInsensitive Если True, то поиск не зависит от регистра
     * @return Bool True, если строка имеет подстроки
     */
    public static function hasSubstring($haystack,$needle,$caseInsensitive = false){
        if($caseInsensitive){
            $haystack = strtolower($haystack);
            $needle = strtolower($needle);
        }
        //echo $haystack,$needle."\n";
        $ret = strpos($haystack, $needle) !== false;
        return $ret;
    }
    
    /**
     * Добавляет подстроку перед каждой строкой в исходном тексте. 
     * 
     * То есть после каждого символа переноса строки.
     * 
     * @param String $haystack Исходный текст
     * @param String $prepend Подстрока, котрую необходимо добавить в начале строки
     * @param String $append Подстрока, котрую необходимо добавить в конце строки
     * @return String Строка с добавленными данными
     */
    public static function wrapLines($haystack,$prepend,$append = ""){
        $parts = explode("\n",$haystack);
        foreach($parts as $key=>$part)
            $parts[$key] = $prepend.$parts[$key].$append;
        $result = join("\n",$parts);
        return $result;
    }
    
    /**
     * Соединяет пути к файлам - не нужно заботиться о слешах
     * 
     * @param String $path1 Кусок пути
     * @param String $path2 Кусок пути
     * @return String Путь к файлу
     */
    public static function joinPaths($path1,$path2)
    {
        $args = func_get_args();
        //Debug::show($args);
        
        $len  = count($args);
        //Первый и последний элемент тримим аккуратно
        $args[0] = rtrim($args[0],"/");
        $args[$len-1] = ltrim($args[$len-1],"/");
        
        //Трим серединку
        for($i = 1; $i < $len-1; $i++) 
        {
            $args[$i] = trim($args[$i],"/");
        }
        //Debug::show($args);
        $result = join("/",$args);
        return $result;
    }
    
    /**
     * Создает случайную строку указаной длины
     * 
     * @param Integer $length Длина
     * @param String $alphabet Алфавит для генерации
     * @return String Сгенерированная строка
     */
    public static function generateString($length,$alphabet = null)
    {
	$characters = $alphabet ? $alphabet : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++)
	{
	    $randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
    }

    /**
     * Генерирует типографический наполнитель
     * 
     * @param Number $words Количество слов
     * @return string Типографический наполнитель необходимой длины
     */
    public static function loremIpsum($words = 150)
    {
        $str = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
    
        $result = "";
        $parts = explode(" ",$str.$str.$str);
        for($i = 0; $i < $words; $i++)
            $result .= " ".$parts[$i];
            
        $result.=".";
        return $result;
    }

    /**
     * В функцию передаются аргументы, которые являются либо строками, либо массивами строк
     * Данная функция может быть полезна для конвертации ассоциативного массива в строку HTML аттрибутов
     * или для создания параметров для Url
     * 
     * @param Mixed $arg1
     * @param Mixed $arg2
     * @return String[] массив, элементы которого обернуты строками
     */
    public static function wrapArrays($arg1,$arg2)
    {
        $result = [];
        $args = func_get_args();
        $count = 0;
        foreach($args as $arg)
        {
            if(is_array($arg))
            {
                $count = count($arg);
                continue;
            }
        }
        if($count == 0)
        {
            return $result;
        }
        
        for($i=0; $i < $count; $i++)
        {
            $result[$i] = "";
            foreach($args as $arg)
            {
                if(is_array($arg))
                {
                    $result[$i] .= $arg[$i];
                }
                else
                {
                    $result[$i] .= $arg;
                }
                
            }
        }
        return $result;
    }

}
