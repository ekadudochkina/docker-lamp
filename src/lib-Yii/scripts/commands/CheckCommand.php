<?php

/**
 * Команда проверки кода
 *
 * @package Hs\Shell\Commands
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class CheckCommand extends ConsoleCommand
{

    /**
     * Список ошибок
     * 
     * @var String[]
     */
    protected $errors = [];

    /**
     * Вызов всех экшенов
     */
    public function actionIndex()
    {
        $this->actionModels();
        $this->actionViews();

        $errorNum = count($this->errors);
        $this->log("Done: {$errorNum} errors found.");
    }

    /**
     * Проверка вьюшек
     */
    public function actionViews()
    {
        $this->log("Проверяем вью");
        $path = Yii::getPathOfAlias("application.views");
        $this->checkViews($path);
    }

    /**
     * Проверка моделей
     */
    public function actionModels()
    {
        $this->log("Проверяем модели");

        $path = Yii::getPathOfAlias("application.models");
        ReflectionHelper::includeAllClasses($path);
        $classes = ReflectionHelper::getSubclassesOf("CModel", true);

        foreach ($classes as $class)
            $this->checkModel($class);
    }

    /**
     * Проверка модели
     * 
     * @param String $class Модель
     */
    public function checkModel($class)
    {
        $obj = ActiveRecordHelper::createModelInstance($class);

        $desc = \Hs\Helpers\ClassHelper::getDescriptionForClass(get_class($obj));

        $desc = trim($desc);
        if (!$desc)
            $this->addError($class, 'Отсутствует комментарий к классу');

        if (StringHelper::hasSubstring($desc, "Description of", true))
            $this->addError($class, "Стандартный комментарий к классу");

        $author = \Hs\Helpers\ClassHelper::getTagForClass(get_class($obj), "author");
        if (!$author)
            $this->addError($class, "Не указан автор класса");

        if (!is_subclass_of($obj, "CActiveRecord"))
            return;

        //Проверяем связи
        $relations = $obj->relations();
        foreach ($relations as $propertyName => $arr)
        {
            $type = $arr[0];
            $classname = $arr[1];
            $field = $arr[2];
            if ($type == CActiveRecord::BELONGS_TO)
            {
                if (!isset($obj->$field))
                {
                    $this->addError($class, "Нет ключа '$field' для связи '$propertyName'");
                }
            }
            $tags = \Hs\Helpers\ClassHelper::getTagForClass(get_class($obj), "property");
            foreach ($tags as $tag)
            {
                $fieldName = \Hs\Helpers\ClassHelper::getTagValue($tag, 2);
                //bug::drop($propName,$fieldName);
                if (StringHelper::hasSubstring($fieldName, $propertyName))
                {
                    if (StringHelper::hasSubstring($tag, "КОММЕНТАРИЙ"))
                    {
                        $this->addError($class, "Стандартный комментарий для свойства '$fieldName'");
                    }
                    break;
                }
            }
            $this->addError($class, "Нет закомментированного свойства '$propertyName' для свзяи '$propertyName' ");
        }
    }

    /**
     * Добавление ошибки в список ошибок
     * 
     * @param String $obj Имя объекта
     * @param String $msg Текст ошибки
     */
    protected function addError($obj, $msg)
    {
        $level = CLogger::LEVEL_ERROR;
        $newMsg = "'$obj': " . $msg;
        $this->errors[] = $newMsg;
        $this->log($newMsg, $level);
    }

    /**
     * Рекурсивная проверка файлов и папок вьюшек
     * 
     * @param String $path
     */
    protected function checkViews($path)
    {
        //Пропускаем ссылки . и ..
        $rev = strrev($path);
        if ($rev[0] == ".")
            return;

        //$this->log("Checking ".$path);

        if (is_dir($path))
        {
            $files = scandir($path);
            foreach ($files as $file)
            {
                $subPath = $path . "/" . $file;
                $this->checkViews($subPath);
            }
            return;
        }

        if (!StringHelper::hasSubstring($path, ".php"))
            return;

        $content = file_get_contents($path);
        $this->checkView($path, $content);
    }

    /**
     * Проверка файла вьюшки
     * 
     * @param String $path Путь к файлу
     * @param String $content Содержимое файла
     */
    public function checkView($path, $content)
    {
        if (!StringHelper::hasSubstring($content, '/* @var $this', true))
            $this->addError($path, "Не обнаружена подсказка для \$this");

        $vars = $this->checkViewVariables($path, $content);
    }

    /**
     * Получение списка имен переменных во вьюшке
     * 
     * @param String $content Содержимое файла
     */
    public function extractVariables($content)
    {
        $pattern = '/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
        preg_match_all($pattern, $content, $matches);
        $varMatches = $matches[0];
        $variables = array_unique($varMatches);
        //bug::show($variables);
    }

    /**
     * Проверка содержимого вьюшки
     * 
     * @param String $path Путь к файлу
     * @param String $content Содержимое файла
     */
    public function checkViewVariables($path, $content)
    {
        //Сначала получаем имя контроллера
        $dir = dirname($path);
        $viewDir = Yii::getPathOfAlias("application.views");
        if ($viewDir != dirname($dir))
        {
            //Данный выход может случиться, если это подвьюшка
            return;
        }

        $viewDirName = basename($dir);
        if ($viewDirName == "layouts")
        {
            return;
        }

        $controllerName = ucfirst($viewDirName) . "Controller";
        $viewFilename = basename($path);

        Yii::import("application.controllers." . $controllerName);
        $view = str_replace(".php", "", $viewFilename);
        $pattern = '/render\(.' . $view . '("|\')/';
        $actions = CodeGenHelper::getMethodsWithPattern($controllerName, $pattern);
        if (!$actions)
        {
            $this->addError($path, "Не найден экшен в контроллере '$controllerName'");
        }


        foreach ($actions as $action)
        {
            $vars = CodeGenHelper::getViewVariables($controllerName, $action);
            foreach ($vars as $var)
            {
                $pattern = "@var \$$var";
                if (!StringHelper::hasSubstring($content, $pattern))
                {
                    $this->addError($path, "Не найдена подсказка для переменной '\$$var', объявленной в методе '$action'.");
                }
            }
        }
    }

}
