<?php

/**
 * Объект для работы с рефлексией
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class ReflectionHelper
{

    /**
     * Поиск всех подклассов от данного родителя.
     * <b>Функция не работает с автолодом. То есть если классы не были вызваны, то они не найдутся.</b>
     *
     * @param String $parent Имя класса родителя
     * @param Bool $nonAbstract Если True, то будет возвращен массив последних наследников из иерархии
     * @return String[] Список имен подклассов
     */
    public static function getSubclassesOf($parent, $nonAbstract = false)
    {
        $result = array();
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            if (is_subclass_of($class, $parent))
                $result[] = $class;
        }

        //Если необходимо удалить промежуточные классы
        if ($nonAbstract) {
            $temp = array();
            foreach ($result as $class) {
                $ref = new ReflectionClass($class);
                $abstract = $ref->isAbstract();
                if(!$abstract)
                {
                    $temp[] = $class;
                }
                $result = $temp;
            }

        }
        return $result;
    }

    /**
     * Подключение всех классов из директории
     * @param String $folder Путь к директории
     * @param Boolean $recursive Рекурсивно ли добавлять файлы
     * @return string[] Имена классов (вычисляется из файла)
     */
    public static function includeAllClasses($folder, $recursive)
    {

        $paths = FileHelper::getFilePathsInDirectory($folder, $recursive);
//        bug::Drop($paths);
        $filenames = [];
        foreach ($paths as $filename) {
//            Debug::show($filename);
            //Try Catch, потому что с наследниками мы не можем ничего поделать с некоторыми
            try {
                require_once $filename;
                $info = pathinfo($filename);
                $filenames[] = $info["filename"];
            } catch (Exception $e) {
//                throw $e;
            }
        }
        return $filenames;
    }

    /**
     * Данная функция включает все классы, которые только можно.
     * <b>Не использовать в production. Только для тестов или генерации кода!!!!</b>
     */
    public static function includeAll()
    {
        if (EnvHelper::isProduction() && EnvHelper::isDemo())
            throw new Exception("includeAll нельзя использовать в продакшене");

        $paths = array();
        $paths['application.models'] = true;
        // @todo Вызывает ошибку при подгрузке всех php скриптов, не классов
        // $paths[] = 'application.components';
        $paths['webroot.lib-Yii.components'] = false;

        foreach ($paths as $alias => $recursive) {
            $path = Yii::getPathOfAlias($alias);
            static::includeAllClasses($path, $recursive);
        }
    }

    /**
     * Проверяет значение на соответствие константам класса (замена перечислений в PHP)
     *
     * @param Object $object Объект класса, где находятся константы
     * @param Mixed $constvalue Значение, которое необходимо проверить
     * @param String $hint Часть имени константы, общая для всех констант (нужно, если в классе много перечислений)
     * @return Boolean True, если значение соответствует одной из констант
     */
    public static function checkConstant($object, $constvalue, $hint = null)
    {
        $reflected = new ReflectionClass($object);
        $constants = $reflected->getConstants();
        foreach ($constants as $name => $value) {
            if ($hint && !StringHelper::hasSubstring($name, $hint, true))
                continue;
            if ($value == $constvalue)
                return true;
        }
        return false;
    }

    /**
     * @param $path string Путь к директории содержащий классы
     * @param $recursive boolean Рекурсивно ли подключение классов
     * @param array $params Параметры для конструктора
     * @return Object[]
     * @throws ReflectionException
     */
    public static function createObjectsFromFolder($path, $recursive,$params = [])
    {
        $classes = ReflectionHelper::includeAllClasses($path,$recursive);
        $objects = [];
        foreach($classes as $class)
        {
            $x = new ReflectionClass($class);
            $instance = $x->newInstanceArgs($params);
            $objects[] = $instance;
        }
        return $objects;
    }

    public static function getConstants($class, $hint, $flipped = false)
    {
        $oClass = new ReflectionClass($class);
        $constants =  $oClass->getConstants();
        $filtered = [];
        foreach($constants as $key => $const)
        {
            if(StringHelper::hasSubstring($key,$hint))
            {
                $filtered[$key] = $const;
            }
        }
        if($flipped)
        {
            $filtered = array_flip($filtered);
        }
        return $filtered;
    }
}
