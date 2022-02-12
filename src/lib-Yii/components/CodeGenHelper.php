<?php
use Hs\Helpers\ClassHelper;
/**
 * Содержит функции для генерации кода
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class CodeGenHelper
{

    /**
     * Комментарий по-умолчанию
     * @var String 
     */
    protected static $defaultComment = "КОММЕНТАРИЙ";

    /**
     * Дополнительный комментарий к внешнему ключу
     * @var String
     */
    protected static $foreignKeyRemark = "\n<b>Внешний ключ.</b>";

    /**
     * Переводит значение в литерал.
     * 
     * @param Mixed Значение в исходном типе данных
     * @return String Строка, которую можно использовать в качестве литерала
     */
    public static function valueToString($value)
    {
        $type = gettype($value);

        switch ($type)
        {
            case "boolean" : return $value ? "true" : "false";
                break;
            case "NULL" : return "null";
            case "string" : return "'$value'";
                break;
            default : return $value;
        }
    }

    /**
     * Превращает массив правил в код объявления массива
     * 
     * @param Array $rules Реальный массив правил
     * @return String[] Массив объявлений правил формата "array('field1, field2','validator')"
     */
    public static function rulesToCode($rules)
    {
        $text = array();
        foreach ($rules as $validator)
        {
            $fields = $validator[0];
            $validatorName = $validator[1];
            $otherKeys = array_diff($validator, array($validatorName, $fields));
            $other = "";
            foreach ($otherKeys as $key => $value)
            {
                $other .= ",'$key' => " . CodeGenHelper::valueToString($value);
            }
            $str = "array('$fields','$validatorName'$other)";
            $text[] = $str;
        }
        return $text;
    }

    /**
     * Превращает массив связей в код объявления тэгов связи
     * 
     * @param Array $relations Реальный массив связей
     * @param String $tablename Имя таблицы, для получения комментариев
     * @return String[] Массив тэгов property
     */
    public static function relationsToTags($relations, $tablename)
    {
        $schema = Yii::app()->getDb()->getSchema();
        $table = $schema->getTable($tablename);
        $tags = array();
        foreach ($relations as $key => $relation)
        {
            $class = $relation[1];
            $type = $relation[0];
            $foreignKey = $relation[2];
            $stringType = $class;
            //Debug::drop($relation);
            //тут запутанно
            //Если есть комментарий к полю в БД, то используем его
            //Если нет, то пытаемся использовать комментарий к классу
            //Если и его нет, то используем комментарий по умолчанию
            $column = $table->getColumn($foreignKey);

            $classComment = ClassHelper::getDescriptionForClass($class);

            $comment = $classComment ? $classComment : static::$defaultComment;
            if ($type == ActiveRecord::BELONGS_TO)
            {
                if (!$column)
                {
                    throw new Exception("Поле '$foreignKey' не найдено в БД. Проверьте relations().");
                }

                //Удаляем автоматические данные из комментария
                $columnComment = str_replace(static::$foreignKeyRemark, "", $column->comment);

                $comment = $columnComment ? $columnComment : $comment;
            }

            switch ($type)
            {
                case ActiveRecord::HAS_MANY : $stringType .= "[]";
                    break;
                case ActiveRecord::MANY_MANY : $stringType .= "[]";
                    break;
            }
            $field = $key;
            $tag = "@property $stringType $$field $comment";
            $tags[] = $tag;
        }
        //Debug::drop($tags);
        return $tags;
    }

    /**
     * Превращает массив связей в код объявления массива
     * 
     * @param Array $relations Реальный массив связей
     * @return String[] Массив объявлений связей формата "array(self::BELONGS_TO,'Model','keyId')"
     */
    public static function relationsToCode($relations)
    {
        $code = array();
        foreach ($relations as $relationName => $relation)
        {
            $type = $relation[0];
            switch ($type)
            {
                case ActiveRecord::HAS_ONE : $stringType = "HAS_ONE";
                    break;
                case ActiveRecord::HAS_MANY : $stringType = "HAS_MANY";
                    break;
                case ActiveRecord::MANY_MANY : $stringType = "MANY_MANY";
                    break;
                case ActiveRecord::BELONGS_TO : $stringType = "BELONGS_TO";
                    break;
            }
            $className = $relation[1];
            $key = $relation[2];

            //Описываем текст
            $text = "array(self::$stringType, '$className', '$key'";
            if (!empty($relation["update"]))
                $text .= ", 'update' => '{$relation['update']}'";
            if (!empty($relation["delete"]))
                $text .= ", 'delete' => '{$relation['delete']}'";
            $text .= ")";

            $code[$relationName] = $text;
        }
        return $code;
    }

    /**
     * Меняет порядок правил, согласно заданным приоритетам
     * 
     * @param Array $rules Массив правил модели
     * @param String $rulesOrder массив имен валидаторов, устанавливающий приоритет.
     * @return Array Отсортированный массив правил
     */
    public static function reorderRules($rules, $rulesOrder)
    {
        $rulesOrder = array_unique($rulesOrder);
        $newRules = array();
        foreach ($rulesOrder as $key => $name)
        {
            $name = strtolower(trim($name));
            $rulesOrder[$key] = $name;
            foreach ($rules as $rule)
            {
                $validator = strtolower(trim($rule[1]));
                if ($validator == $name)
                    $newRules[] = $rule;
            }
        }

        foreach ($rules as $rule)
        {
            $name = strtolower(trim($rule[1]));
            if (!in_array($name, $rulesOrder))
                $newRules[] = $rule;
        }

        return $newRules;
    }

    /**
     * Анализирует информацию о колонках и создает массив меток для полей
     * 
     * @param String $tableName Имя таблицы
     * @return Array Массив меток модели
     */
    public static function createLabels($tableName, $useComments = false)
    {
        /* @var CMysqlTableSchema[] $tables */
        $schema = Yii::app()->getDb()->getSchema();
        $table = $schema->getTable($tableName);

        $result = array();
        foreach ($table->getColumnNames() as $colName)
        {
            $col = $table->getColumn($colName);
            $key = $col->name;
            if ($useComments && $col->comment)
                $value = $col->comment;
            else
            {
                $name = $col->name;
                //Разбиваем строку по кэмел кейсу
                preg_match_all('/((?:^|[A-Z])[a-z]+)/', $name, $matches);
                $parts = $matches[0];

                //Если в поле нет кэмл кейса, например для абривеатур
                //то мы просто ставим в лейбл его значение
                if (!$parts)
                {
                    $value = $name;
                    continue;
                }

                //Для внешних ключей удаляем Id
                if ($col->isForeignKey)
                {
                    foreach ($parts as $partKey => $value)
                        if (strtolower($value) == "id")
                            unset($parts[$partKey]);
                    $parts = array_values($parts);
                }
                if (!$parts)
                {
                    throw new Exception("Не удается создать лейбл. Похоже, что внешний ключ описан неправильно.");
                }
                //Делаем человеческое предложение из слов
                $parts[0] = ucfirst($parts[0]);
                for ($i = 1; $i < count($parts); $i++)
                    $parts[$i] = lcfirst($parts[$i]);

                $value = join(" ", $parts);
            }
            $result[$key] = $value;
        }
        //Debug::drop($result);
        return $result;
    }

    /**
     * Анализирует информацию о колонках и создает массив правил модели
     * 
     * @param String $tableName Имя таблицы
     * @return Array Массив правил модели
     */
    public static function createRules($tableName)
    {
        /* @var CMysqlTableSchema[] $tables */
        $schema = Yii::app()->getDb()->getSchema();
        $table = $schema->getTable($tableName);

        //Формируем списки полей для валидаторов
        $requiredFields = array();
        $lengthFields = array();
        $boolFields = array();
        $numericFields = array();
        $integerFields = array();
        $datetimeFields = array();
        $dateFields = array();
        $exceptions = array("id");
        foreach ($table->getColumnNames() as $colName)
        {
            $col = $table->getColumn($colName);
            if (in_array(strtolower($col->name), $exceptions))
                continue;

            //bug::show($colName,$col);
            if (!$col->allowNull)
                $requiredFields[] = $col->name;

            if (StringHelper::hasSubstring($col->dbType, "tinyint"))
                $boolFields[] = $col->name;
            elseif (StringHelper::hasSubstrings($col->dbType, array("int", "float", "double")))
            {
                if (StringHelper::hasSubstring($col->dbType, "int"))
                    $integerFields[] = $col->name;
                else
                    $numericFields[] = $col->name;
            }
            if (StringHelper::hasSubstring($col->dbType, "datetime"))
                $datetimeFields[] = $col->name;
            elseif (StringHelper::hasSubstring($col->dbType, "date"))
                $dateFields[] = $col->name;


            if (StringHelper::hasSubstring($col->dbType, "varchar"))
            {
                ArrayHelper::addKeyIfNotExists($lengthFields, $col->size);
                $lengthFields[$col->size][] = $col->name;
            }
        }

        //Составляем массив, в котором перечислены все валидаторы
        $validators = array();
        if (!empty($requiredFields))
            $validators[] = array(join(", ", $requiredFields), "required");
        if (!empty($boolFields))
            $validators[] = array(join(", ", $boolFields), "boolean");
        if (!empty($numericFields))
            $validators[] = array(join(", ", $numericFields), "numerical");
        if (!empty($integerFields))
            $validators[] = array(join(", ", $integerFields), "numerical", "integerOnly" => true);
        if (!empty($datetimeFields))
            $validators[] = array(join(", ", $datetimeFields), "type", "type" => "datetime", "datetimeFormat" => "yyyy-MM-dd hh:mm:ss");
        if (!empty($dateFields))
            $validators[] = array(join(", ", $dateFields), "type", "type" => "date", "dateFormat" => "yyyy-MM-dd");

        krsort($lengthFields);
        foreach ($lengthFields as $length => $fields)
            $validators[] = array(join(",", $fields), "length", "max" => $length);

        return $validators;
    }

    /**
     * Создает связи исходя из имени таблицы
     * 
     * @param String $tablename Имя таблицы
     * @return Array Массив связей для модели
     */
    public static function createRelations($tablename)
    {
        /* @var CMysqlTableSchema[] $tables */
        $schema = Yii::app()->getDb()->getSchema();
        $tables = $schema->getTables();
        $hasMany = array();
        foreach ($tables as $table)
        {
            $fks = $table->foreignKeys;
            foreach ($fks as $field => $fk)
            {
                $fkTableName = $fk[0];
                $key = $fk[1];

                if ($fkTableName == $tablename)
                {
                    $hasMany[] = array($field, $table->name, $key);
                }
            }
        }
        $belongsTo = array();
        foreach ($schema->getTable($tablename)->foreignKeys as $field => $fk)
        {
            $belongsTo[] = array_merge(array($field), $fk);
        }

        $relations = array();
        foreach ($belongsTo as $fk)
        {
            $key = $fk[0];
            $table = $fk[1];
            $modelName = static::guessModelName($table);
            $keyName = str_replace("Id", "", $key);
            $relations[lcfirst($keyName)] = array(ActiveRecord::BELONGS_TO, $modelName, $key);
        }
        foreach ($hasMany as $fk)
        {
            $key = $fk[0];
            $table = $fk[1];
            $modelName = static::guessModelName($table);
            $relations[lcfirst($table)] = array(ActiveRecord::HAS_MANY, $modelName, $key);
        }

        //Debug::drop($belongsTo,$hasMany,$relations);
        return $relations;
    }

    /**
     * Получает комментарий из имени таблицы или возвращает комментарий по-умолчанию
     * 
     * @param String $tableName
     */
    public static function createModelComment($tableName)
    {
        $db = Yii::app()->getDb();

        //Проверяем существование таблицы
        $query = "SHOW TABLES LIKE '$tableName'";
        $queryResult = $db->createCommand($query)->queryAll(false);
        if (!$queryResult)
            return static::$defaultComment;

        //Получем комментарий
        $query = "SHOW create table `$tableName`;";
        $queryResult = $db->createCommand($query)->queryAll(false);
        $string = $queryResult[0][1];
        $parts = explode("\n", $string);
        $last = ArrayHelper::getLast($parts);
        if (!StringHelper::hasSubstring($last, "COMMENT="))
            return static::$defaultComment;
        $splat = explode("COMMENT='", $last);
        $commentRaw = $splat[1];

        //Удаляем кавычки
        $commentRaw = str_replace("''", "*", $commentRaw);
        $commentRaw = str_replace("'", "", $commentRaw);
        $comment = str_replace("*", "'", $commentRaw);

        return $comment;
    }

    /**
     * Создает поля для модели из данных таблицы
     * 
     * @param String $tableName Имя таблицы
     * @return CodeEntity[] Список полей
     */
    public static function createFields($tableName)
    {
        /* @var CMysqlTableSchema[] $tables */
        $schema = Yii::app()->getDb()->getSchema();
        $table = $schema->getTable($tableName);
        $result = array();
        foreach ($table->getColumnNames() as $colName)
        {
            $column = $table->getColumn($colName);
            //Debug::show($column);
            $field = new CodeEntity($column->name, CodeEntity::TYPE_FIELD);
            $type = $column->type;
            //bug::show($colName,$type,$column->dbType);
            $comment = static::$defaultComment;
            $tags = array();
            $defaultValue = null;
            $hasDefaultValue = false;

            //Получаем комментарий
            if ($column->comment)
                $comment = $column->comment;

            //Для первичного ключа, у меня есть коммент
            if ($column->isPrimaryKey)
            {
                $comment = $column->comment ? $column->comment : "Первичный ключ";
                $type = "int";
            }

            //Проставляем десятичные
            if (StringHelper::hasSubstring($column->dbType, "decimal"))
            {
                $type = "float";
                $tags[] = "@sqltype " . $column->dbType;
            }

            //Проставляем типы для дат
            if (StringHelper::hasSubstring($column->dbType, "datetime"))
                $tags[] = "@sqltype DATETIME";
            elseif (StringHelper::hasSubstring($column->dbType, "date"))
                $tags[] = "@sqltype DATE";

            //tinyint это bool, а не integer
            if (strpos(strtolower($column->dbType), "tinyint") !== false)
            {
                $type = "boolean";
                if ($column->defaultValue === 0)
                    $defaultValue = false;
                elseif ($column->defaultValue === 1)
                    $defaultValue = true;
            }
            //Внешние ключи всегда integer
            if ($column->isForeignKey)
            {

                $type = "int";

                $key = DbHelper::getForeignKey($table->name, $column->name);
                try
                {

                    $className = ActiveRecordHelper::getClassByTableName($key->referencedTable);
                    $obj = ActiveRecordHelper::createModelInstance($className);
                    $modelComment = ClassHelper::getDescriptionForClass($obj);

                    if ($comment == static::$defaultComment)
                        $comment = $modelComment;
                } catch (Exception $ex)
                {
                    
                }
                $comment .= static::$foreignKeyRemark;
                $tags[] = "@update " . $key->update;
                $tags[] = "@delete " . $key->delete;
            }

            //Для красоты
            if ($type == "int")
                $type = "integer";
            $type = ucfirst($type);

            //Получаем значение по-умолчанию
            if ($column->defaultValue !== null || $column->allowNull)
            {
                if ($defaultValue === null)
                    $defaultValue = $column->defaultValue;
                $defaultValue = CodeGenHelper::valueToString($defaultValue);
                $hasDefaultValue = true;
            }
            //Формируем строку значения по умолчанию
            $defaultString = "";
            if ($hasDefaultValue)
                $defaultString = " = $defaultValue";

            //Формируем теги
            $tags[] = "@var " . $type;
            $tags[] = static::getAutogeneratedTag();

            $comment = static::createComment(0, $comment, $tags, true);
            $field->commentary = $comment;
            $field->code = static::pad(4) . "public $" . $field->getName() . "$defaultString;";
            $result[$field->getName()] = $field;
        }
        //Debug::drop($result);
        return $result;
    }

    /**
     * Получение имени модели по ее названию в таблице
     * 
     * @see ActiveRecord
     * @param type $tableName
     */
    public static function guessModelName($tableName)
    {
        //Сначала попробуем узнать по настоящей модели, если нет будем гадать
        $name = ActiveRecordHelper::getClassByTableName($tableName);
        if ($name != null)
            return $name;

        //Думаю тут не все очевидно
        //Чем объяснять, проще взглянуть на функцию 
        $last = strlen($tableName) - 1;
        $ending = $tableName[$last - 1] . $tableName[$last];
        if ($ending == "es")
        {
            $third = $tableName[$last - 2];
            if ($third = "s")
                $modelName = substr($tableName, 0, $third);
            else
                $modelName = substr($tableName, 0, $last - 1);
        }
        else
        {
            $modelName = substr($tableName, 0, $last);
        }
        return $modelName;
    }

    /**
     * Генерирует строку с указанной длинной пробелов.
     * 
     * @param Int $length Длина пробелов
     * @return String Строка из пробелов
     */
    public static function pad($length)
    {
        $space = " ";
        $result = "";
        for ($i = 0; $i < $length; $i++)
            $result .= $space;
        return $result;
    }

    /**
     * Принимает на вход массив строк и выводит их красиво.
     * 
     * @param Integer $padding Количество пробелов слева (отступы)
     * @param String $prepend Строка, которую можно добавить перед каждой строкой
     * @param String[] $lines Массив строк, которые необходимо вывести
     * @param String $append Строка, которую можно добавить после каждой строки
     * @return String Красивая результирующая строка
     */
    public static function wrapLines($padding, $prepend, $lines, $append = "")
    {
        //В целом эта функция дублирует StringHelper::wrapLines
        //Но ее интерфейс помогает лучше видеть что происходит в кодогенераторе
        $string = join("\n", $lines);
        $prepend = CodeGenHelper::pad($padding) . $prepend;
        $result = StringHelper::wrapLines($string, $prepend, $append);
        $result.= "\n";
        return $result;
    }
       
    /**
     * Обрамляет куски кода, выводя их красиво.
     * 
     * @param Integer $padding Количество пробелов слева (отступы)
     * @param CodeEntity[] $snippets Кусочки кода 
     * @return string Красивая результирующая строка
     */
    public static function wrapCodeEntities($padding, $snippets)
    {
        if (count($snippets) == 0)
            return "";
        $result = "";
        foreach ($snippets as $snippet)
        {
            $result .= static::wrapCodeEntity($padding, $snippet);
            //Добавляем 2 переноса строк справа, если их нету
            $result = rtrim($result) . "\n\n";
        }

        return $result;
    }

    /**
     * Обрамляет кусок кода, выводя их красиво.
     * 
     * @param Integer $padding Количество пробелов слева (отступы)
     * @param CodeEntity $snippet Кусок кода 
     * @return string Красивая результирующая строка
     */
    public static function wrapCodeEntity($padding, $snippet)
    {
        //Переделываем комментарий заново, вдруг предыдущий через очко сделан
        if ($snippet->commentary)
        {
            $description = ClassHelper::parseCommentForDescription($snippet->commentary);
            $tags = ClassHelper::parseCommentForTags($snippet->commentary);
            $comment = static::createComment(0, $description, $tags);
            $snippet->commentary = $comment;
        }
        //Убирам паддинг из кода, так как он тоже через очко может быть сделан
        $snippet->code = static::removePadding($snippet->code);

        $text = $snippet->commentary . "" . $snippet->code;
        $lines = explode("\n", $text);
        $result = static::wrapLines($padding, "", $lines);
        //Debug::drop($result);
        return $result;
    }

    /**
     * Подставляет перенос строки.
     * 
     * @param String $string Строка, которую необходимо вывести
     */
    public static function endline($string)
    {
        return $string . "\n";
    }

    /**
     * Cоздает красивый комментарий
     * 
     * @param Integer $padding Отступ слева
     * @param String $text Текст комментария
     * @param String[] $tags Массив тегов
     * @return String комментарий для класса, функции или метода
     */
    public static function createComment($padding, $text, $tags = array())
    {
        $start = "/**\n";
        $end = "\n */";
        $tagsText = join("\n", $tags);
        $text .="\n\n" . $tagsText;
        $wrapped = StringHelper::wrapLines($text, " * ");
        $comment = $start . $wrapped . $end;
        $lines = explode("\n", $comment);
        $result = static::wrapLines($padding, "", $lines);
        return $result;
    }

    /**
     * Создает тег autogenerated.
     * 
     * @return String Тег с датой генерации
     */
    public static function getAutogeneratedTag()
    {
        $tag = "@autogenerated " . date("d-m-Y");
        return $tag;
    }

    /**
     * Добавляет тег autogenerated в теги, если его нет
     * 
     * @param String[] $tags Исходный массив тегов
     * @param Bool $merged Если да, добавляет информацию о том, что было произведено слияние
     * @return String Новый массив тегов
     */
    public static function addAutogeneratedTag($tags, $merged = false)
    {
        $newTag = static::getAutogeneratedTag();
        $newTag = $merged ? $newTag . " Merged" : $newTag;
        $result = ActiveRecordHelper::addTagIfNotExists($tags, $newTag, true);
        return $result;
    }

    /**
     * Совмещает 2 массива полей, возвращая один без повторений.
     * 
     * @param CodeEntity[] $fields1 Исходный массив полей
     * @param СodeEntity[] $fields2 Дополнительный массив полей
     * @return CodeEntity[] Совмещенный массив полей
     */
    public static function mergeFields($fields1, $fields2)
    {
        //Debug::drop($fields1,$fields2);
        $result = $fields1;
        foreach ($fields2 as $field)
        {
            if (isset($result[$field->getName()]))
            {
                $existingField = $result[$field->getName()];
                //Получаем данные с двух комментариев
                $existingDescription = ClassHelper::parseCommentForDescription($existingField->commentary);
                $existingTags = ClassHelper::parseCommentForTags($existingField->commentary);
                $description = ClassHelper::parseCommentForDescription($field->commentary);
                $tags = ClassHelper::parseCommentForTags($field->commentary);
                $existingTags = ActiveRecordHelper::mergeTags($existingTags, $tags);

                //Формируем новые данные комментария
                if (!$existingDescription || $existingDescription == static::$defaultComment)
                    $existingDescription = $description;

                $existingTags = static::addAutogeneratedTag($existingTags, true);
                $newComment = static::createComment(0, $existingDescription, $existingTags);
                $existingField->commentary = $newComment;

                //Код изменяем только в том случае, если у новых полей есть значение
                // а у старых - нет
                $existingValue = static::getFieldValueFromCode($existingField->code);
                $value = static::getFieldValueFromCode($field->code);
                $code = $existingField->code;
                if ($value !== null)
                {
                    //Если нет существующего значения, то смело заменяем
                    if ($existingValue === null)
                        $code = $field->code;
                    elseif ($existingValue != $value)
                    {
                        //Несовпадение значений это серьезно дело! Нужно добавить комментарий
                        $code = static::addComment($code, "suggested value: " . $value);
                    }
                }
                $existingField->code = $code;
            } else
            {
                $result[$field->getName()] = $field;
            }
        }

        return $result;
    }

    /**
     * Получает значение поля по его коду
     * 
     * @param String $fieldCode Код поля
     * @return String Строка содержащая значение поля или NULL
     */
    public static function getFieldValueFromCode($fieldCode)
    {
        $parts = explode("=", $fieldCode);
        if (count($parts) == 1)
            return null;

        $parts[0] = "";
        $str = join("=", $parts);
        $rawValue = substr($str, 1);
        $trimmed = trim($rawValue);
        if ($trimmed[strlen($trimmed) - 1] == ";")
            $trimmed = substr($trimmed, 0, strlen($trimmed) - 1);
        return $trimmed;
    }

    /**
     * Добавляет комментарий в строку, удаляя существующий
     * 
     * @param String $string Строка кода
     * @param String $comment комментарий
     * @param Bool $rewrite Если True, то удаляет существующие комментарии
     * @return String Строка кода с коментарием
     */
    public static function addComment($string, $comment, $rewrite = true)
    {
        if (!$rewrite)
            return $string . " //" . $comment;

        $parts = explode(";", $string);
        $len = count($parts);
        $parts[$len - 1] = $comment = " //" . $comment;
        $newstring = join(";", $parts);
        // Debug::drop($newstring);
        return $newstring;
    }

    /**
     * Возвращает количество пробелов слева
     * 
     * @param String $string Исходная строка
     * @return Int Количество пробелов
     */
    public static function getPadding($string)
    {
        $len = strlen($string);
        $spaces = 0;
        for ($i = 0; $i < $len; $i++)
        {
            if ($string[$i] == " ")
                $spaces++;
            else
                break;
        }
        return $spaces;
    }

    /**
     * Удаляет отступы из каждой строки текста
     * 
     * @param String $sourceText Исходный текст
     * @param Integer $padding Количество отступов
     * @retrun String Текст без отступов
     */
    public static function removePadding($sourceText, $padding = null)
    {
        //Заменяем паддинги на 4 пробела 
        $text = str_replace("\t", str_pad("", 8), $sourceText);

        if ($padding == null)
            $padding = static::getPadding($text);
        $parts = explode("\n", $text);
        foreach ($parts as $key => $part)
        {
            $len = strlen($part);
            $newPart = substr($part, $padding, $len - 1);
            $parts[$key] = $newPart;
        }

        $newText = join("\n", $parts);
        return $newText;
    }

    /**
     * Принимает на вход строки и массивы, и создает результирующую строку
     * где на каждый элемент массивов склеивается со строками
     * Массивы должны быть одинакового размера и не ассоциативные
     * 
     * @param Integer $padding Количество пробелов слева
     * @param Mixed $_ Строки или массивы, которые нобходимо вывести
     * @return String
     */
    public static function magicWrap($padding, $value)
    {
        if (!is_int($padding))
            throw new Exception("Первые аргумент это количество пробелов. Тип должен быть чиcловой");

        $args = func_get_args();
        array_shift($args);
        $len = null;
        $arr = null;
        foreach ($args as $arg)
        {
            if (is_array($arg))
            {
                $arr = $arg;
                $len = $len === null ? count($arg) : $len;
                if (count($arg) != $len)
                    throw new Exception("Переданные массивы должны быть одинакового размера");
            }
        }
        if ($len == 0)
            return "";
        if (!is_array($arr))
            throw new Exception("Хотя бы один аргумент должен быть массивом");

        $str = "";
        foreach ($arr as $key => $value)
        {
            $str .= static::pad($padding);
            foreach ($args as $obj)
            {
                if (is_array($obj))
                    $str .=$obj[$key];
                else
                    $str .=$obj;
            }
            $str.="\n";
        }
        return $str;
    }

    /**
     * Удаляет из списка полей, поля, который уже объявленны в базовом классе
     * 
     * @param ActiveRecord $model Объект модели, можно пустой
     * @param CodeEntity[] $fields Список полей
     * @return CodeEntity[] Ассоциативный массив полей, которые объявленны в данной модели
     */
    public static function excludeBaseClassFields($model, $fields)
    {
        $reflector = new ReflectionClass($model);
        $result = array();
        foreach ($fields as $key => $value)
        {
            if (!$reflector->hasProperty($value->getName()))
            {
                $result[$value->getName()] = $value;
                continue;
            }

            $prop = $reflector->getProperty($value->getName());
            if ($prop->getDeclaringClass() != get_class($model))
                continue;

            $result[$value->getName()] = $value;
        }
        //Debug::drop($result);
        return $result;
    }

    /**
     * Получение имен методов, которые содержат в себе код
     * 
     * @param String $classname Имя класса
     * @param String $pattern Шаблон кода
     * @return String[] Массив имен методов
     */
    public static function getMethodsWithPattern($classname, $pattern)
    {
        $reflector = new ReflectionClass($classname);
        $methods = $reflector->getMethods();
        $result = [];
        foreach ($methods as $method)
        {
            /* @var $method ReflectionMethod */
            $code = ActiveRecordHelper::getMethodCode($classname, $method->getName());
            $matches = null;
            preg_match_all($pattern, $code, $matches);
            if ($matches[0])
            {
                $result[] = $method->getName();
            }
        }
        return $result;
    }

    /**
     * Получение переменных вьюшки
     * 
     * @param String $controllerName Имя класса контроллера
     * @param String $action Имя экшена (или метода)
     * @return String[] Массив имен переменных для вида
     */
    public static function getViewVariables($controllerName, $action)
    {
        $result = [];
        $code = ActiveRecordHelper::getMethodCode($controllerName, $action);

        $pattern = "/addViewData\(.*\n/";
        $matches = null;
        preg_match_all($pattern, $code, $matches);

        if (!$matches[0])
        {
            return $result;
        }
        $lines = $matches[0];

        foreach ($lines as $line)
        {
            $parts = explode(",", $line);
            $rev = array_reverse($parts);
            $namePart = $rev[0];
            $name = str_replace(['"', "'", ");", "\n", " ", "\r"], "", $namePart);
            $result[] = $name;
        }

        return $result;
    }

    /**
     * Возвращает комментарий по-умолчанию
     * 
     * @return String
     */
    public static function getDefaultComment()
    {
        return static::$defaultComment;
    }

}
