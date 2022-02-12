<?php
EnvHelper::enableHs();
/**
 * Хелпер для работы с моделями
 *
 * @todo Раскидать методы этого хелпера по более подходящим
 * 
 * @see ActiveRecord
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class ActiveRecordHelper
{

    /**
     * Удаляет из массива модели с одинаковыми Id.
     * 
     * @param ActiveRecord $array Массив моделей
     * @param String $fieldname Имя поля по которому фильтровать
     * @return ActiveRecord[] Массив уникальных моделей
     */
    public static function uniqueArray($array, $fieldname = null)
    {

        $ids = array();
        $result = array();
        foreach ($array as $model)
        {
            $id = $fieldname == null ? $model->getPrimaryKey() : $model->$fieldname;
            if (in_array($id, $ids))
                continue;
            $ids[] = $id;
            $result[] = $model;
        }
        return $result;
    }

    /**
     * Приводит модели к массиву
     * 
     * @param CActiveRecord $model Модель
     * @param String[] $attributes Массив в котором названия аттрибутов или пути к аттрибутам дочерних моделей
     * @param Boolean $flat Если true, то массив будет плоским (одно измерение), а пути станут ключами массива
     * @param Boolean $useIdAsIndex Если true, то в качестве ключа массива используется id-модели, иначе проставляется порядквое число в качестве ключа
     * @return String[] Массив, ключами которого являются названия атрибутов, а значениями - значения
     */
    public static function modelToArray($model, $attributes = null,$flat = false,$useIdAsIndex = true)
    {
        
        $data = array();
        if($attributes === null)
        {
            $attributes = array_keys($model->getAttributes());
        }

        //Поддержка wildcard
        $convertedAttributes = [];
        foreach($attributes as $attribute)
        {
            if($attribute == "*")
            {
                $ownAttrs = array_keys($model->getAttributes());
                $convertedAttributes = array_merge($convertedAttributes,$ownAttrs);
                continue;
            }
            $convertedAttributes[] = $attribute;
        }
        $attributes = $convertedAttributes;


         //bug::useStdout();
        foreach ($attributes as $attr)
        {
            $path1 = null;
            $path2 = null;
            $value = static::getFieldOrArray($model, $attr,$path1,$path2);
            //bug::drop($value);
            if($path2 != null)
            {
               $newAttrs = [];
               //Это нужно, чтобы не только 1 значение попадало
               //(хотя все-равно это не очень эффективно, так как сколько вложенных полей, столько и проходов
               foreach($attributes as $attr)
               {
                   if(strpos($attr,$path1) === 0)
                   {
                       $newAttrs[] = str_replace($path1.".","", $attr);
                   }
               }
               $value = static::modelsToArray($value,$newAttrs,null,$flat,$useIdAsIndex);
               //bug::drop($value,$path1,$path2,$attr,$newAttrs,$useIdAsIndex);
               $attr = $path1;
            }
            if(is_object($value) && is_subclass_of($value,"ActiveRecord"))
            {
                
                $value = static::modelToArray ($value);
            }
            if(is_array($value) && count($value) != 0 && is_object(ArrayHelper::getFirst($value)))
            {
                $value = static::modelsToArray($value);
               
            }
	    if(!$flat)
		ArrayHelper::makeKeysForPath($data, $attr,$value);
	    else
		$data[$attr] = $value;
        }
        
        return $data;
    }
    
    /**
     * Получение значение поля по пути к нему
     * 
     * @param CActiveRecord $model Модель
     * @param String $path Путь к полю дочерней модели
     * @return Mixed Значение поля
     */
    public static function getFieldByPath($model, $path)
    {
        $parts = explode(".", $path);
        $field = array_shift($parts);
        if (empty($parts))
        {
            return $model[$field];
        }
        else
            return static::getFieldByPath($model[$field], join(".", $parts));
    }
    
    /**
     * Вспомогательная функция для конвертации модели в массив, с учетом того, что каждое поле может оказаться массивом других моделей. 
     * Функция движется по модели как каретка, складывая пройденные поля в leftPath, а оставшиеся в rightPath.
     * 
     * @param ActiveRecord $model
     * @param String $path Пусть внутри модели, 
     * @param String $leftPath Пройденная часть пути часть пути до текущего 
     * @param String $rightPath Путь, который необходимо пройти
     * @return Mixed Значение поля находящегося по пути
     */
    static protected function getFieldOrArray($model, $path,&$leftPath,&$rightPath)
    {
        
        $parts = explode(".", $path);
        $field = array_shift($parts);
        $leftPath = $leftPath ? $leftPath.".".$field : $field;
        $val = $model[$field];
        if(is_array($val))
        {
            $rightPath = join($parts);
            //$leftPath = $path;
            return $val;
        }
        
        if (empty($parts))
        {
            //bug::useStdr();
            //bug::show("====================\n",$model,$path);
            return $val;
        }
        else
            return static::getFieldOrArray($model[$field], join(".", $parts),$leftPath,$rightPath);
    }

    /**
     * Приводит модели к массиву
     * 
     * @param CActiveRecord[] $models Модели
     * @param String[] $attributes Массив в котором названия аттрибутов или пути к аттрибутам дочерних моделей
     * @param String[] $emptyValue Пустой элемент, который будет добавлен в начале (к примеру для заголовков в таблице xl)
     * @param Boolean $flat Если true, то массив будет плоским (одно измерение), а пути станут ключами массива
     * @param Boolean $useIdAsIndex Если true, то в качестве ключа массива используется id-модели, иначе проставляется порядквое число в качестве ключа
     * @param Callable($model) $callback Функция обратного вызова, которая должна вернуть массив дополнительных данных для строки
     * @return Array[] Массив, массивов, ключами которых являются названия атрибутов, а значениями - значения
     */
    public static function modelsToArray($models,$attributes = null,$emptyValue = null,$flat = false, $useIdAsIndex = true, $callback = null)
    {
        $result = array();
        
        if($emptyValue !== null)
        {
            $result[0] = $emptyValue;
        }
        
        $i = 0;
        foreach($models as $model){           
        $id = $useIdAsIndex ? $model->getPk() : $i++;
              $result[$id] = static::modelToArray ($model,$attributes,$flat,$useIdAsIndex);
              if(is_callable($callback))
              {
                  $arr = call_user_func($callback, $model);
                  foreach($arr as $key => $value)
                  {
                      $result[$id][$key] = $value;
                  }
              }
        }
        return $result;
    }
    
    /**
     * Получение класса модели по названию таблицы
     * 
     * @param String $tableName Название таблицы
     * @return String Класс модели или NULL
     */
    public static function getClassByTableName($tableName)
    {
        ReflectionHelper::includeAll();
        $models = ReflectionHelper::getSubclassesOf("ActiveRecord", true);
        foreach ($models as $modelname)
        {
            //Тут может выстрелить ошибка из-за абстрактного класса или чего еще хуже
            try{
                $reflected = new ReflectionClass($modelname);
                if ($reflected->isAbstract())
                    continue;
                $obj = new $modelname;
            }
            catch(Exception $e)
            {
                continue;
            }
            
            $table = $obj->tableName();
            //Debug::show($table,$tableName);
            if (strtolower($table) == strtolower($tableName))
            {
                return $modelname;
            }
        }
        return null;
    }

    /**
     * Превращает модели в массив значений для <select>. Это часто необходимо при создании форм
     *  
     * @param ActiveRecord[] $models Массив моделей
     * @param String $titleField Название поля модели, которое будет выступать в качестве название <option>
     * @param String $valueField Название поля модели, которое будетв выступать в качестве значения <option>. По-умолчанию первичный ключ.
     * @param String $emptyTitle Название пустого элемента. Если оно задано, то в начало списка будет добавлен пустой элемент. (Обычно это делается, если выбор делать необязательно)
     * @return Array
     */
    public static function modelsToDropDownValues($models, $titleField, $valueField = null, $emptyTitle = null)
    {
        $array = $models;
        $result = array();

        //Если задано название для пустого элемента, то добавляем его
        if ($emptyTitle !== null)
            $result["0"] = $emptyTitle;

        foreach ($array as $model)
        {
            //Тут немного путаница в словах, так как ключ в масиве это Id, а значение это название для <option>
            $key = $valueField == null ? $model->getPrimaryKey() : ActiveRecordHelper::getFieldByPath($model,$valueField);
            $value = ActiveRecordHelper::getFieldByPath($model,$titleField);
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Автоматически создает миграции для всех моделей проекта
     * <b>Чтобы использовать данный метод ДЛЯ ВСЕХ МОДЕЛЕЙ ПРОЕКТА 
     * необходимо указать тип для всех пременных, связи и валидаторы.</b>
     * 
     * @param CDbConnection $connection Подключение к базе данных
     */
    public static function executeMigrationForModels(CDbConnection $connection)
    {
        //Сначала получим все классы моделей
        $path = Yii::getPathOfAlias("application.models");
        $files = scandir($path);
        $modelNames = array();
        foreach ($files as $file)
            if (strpos($file, ".php") !== false)
                $modelNames[] = str_replace(".php", "", $file);

        //Затем их надо отсортировать по зависимости
        //Для этого делаем проходы по массиву и слоями заносим в результат
        //модели, у которых либо нет зависимостей, либо все зависимости уже добавлены
        $result = array();
        while (count($modelNames) != count($result))
        {
            $added = false;
            foreach ($modelNames as $name)
            {
                if (isset($result[$name]))
                    continue;

                $add = false;
                $instance = ActiveRecordHelper::createModelInstance($name);
                //Мы создаем только те модели, которые сохраняются в БД
                //формы и прочее нас не интересуют
                if(!is_subclass_of($instance, "CActiveRecord"))
                   continue;
                $keys = ActiveRecordHelper::getForeignKeys($instance);

                //Добавляем, если нет внешних ключей
                //Или если все внешние таблицы уже добавленны
                if (count($keys) == 0)
                    $add = true;
                else
                {
                    $add = true;
                    foreach ($keys as $key)
                    {
                        $config = ActiveRecordHelper::getRelationByFieldName($instance, $key);
                        $type = $config[0];
                        $class = $config[1];
                        //Если хотябы один ключ еще не находится добавлен, то не добавляем модель
                        if ($type === CActiveRecord::BELONGS_TO && !isset($result[$class]))
                        {
                            $add = false;
                            break;
                        }
                    }
                }

                //Добавляем
                if ($add)
                    $result[$name] = $instance;
                //Собираем инфо для итерации  while
                $added |= $add;
            }
            //Если за итерацию while не добавилось ни одного элемента, останавливаемся
            if (!$added)
                break;
        }
        
        //А теперь делаем миграции
        foreach($result as $class => $model)
            ActiveRecordHelper::executeMigrationForModel ($class, $connection);
    }

    /**
     * Создает миграцию для модели, исходя из ее данных.
     * <b>Чтобы использовать данный метод необходимо указать тип для всех пременных, связи и валидаторы</b>
     *
     * @param String $modelname Имя модели
     * @param CDbConnection $connection Подключение к базе данных
     * @param bool $addForeignKeys Добавлять ли внешние ключи
     * @throws ReflectionException
     */
    public static function executeMigrationForModel($modelname, CDbConnection $connection,$addForeignKeys = true)
    {
        $model = static::createModelInstance($modelname);
        if($model == null)
        {
            throw new Exception("Can't make instance for '$modelname'");
        }
        $schema = static :: createTableSchemaForModel($model,$addForeignKeys);
        static::checkSchemaForMigration($schema);
        $compositeIndexes = static::getCopmositeIndexes($schema);
        
        $cmd = $connection->createCommand();

//	$schema->name = "xxx";
//	try{ $cmd->dropTable($schema->name);}
//	catch(Exception $e){}
        //Создаем таблицу и добавляем в нее главный ключ
        $primaryKey = $schema->getColumn($schema->primaryKey);
        $options = "";
        if(!EnvHelper::isSQLite())
        {
            $options = "ENGINE=InnoDB DEFAULT CHARSET=UTF8";
            $options .= " COMMENT = " . StringHelper::mysqlEscapeString($schema->comment);
        }
        $incrementText = EnvHelper::isSQLite() ? "AUTOINCREMENT" : "AUTO_INCREMENT";
        $type = $primaryKey->dbType . " primary key $incrementText NOT NULL";
        $cmd->createTable($schema->name, array($primaryKey->name => $type), $options);
        
        
        //echo $cmd->text."<br>";
        //Добавляем колонки
        foreach ($schema->getColumnNames() as $columnName)
        {
            self::addFieldToDatabase($schema,$columnName,$connection);
        }
        
        if (!empty($compositeIndexes)){
            $fieldName = str_replace(',','',$compositeIndexes);
            $nameIndex = $schema->name . '_'.$fieldName.'_composite';
            $cmd->createIndex($nameIndex, $schema->name, $compositeIndexes, TRUE);
        }
    }

    /**
     * Проверяет целостность схемы таблицы для осуществления миграции.
     * В исключениях находятся подсказки о том, как искать причину ошибок.
     * 
     * @param MigrationTableSchema $schema Схема таблицы
     * @throws Exception
     */
    protected static function checkSchemaForMigration(MigrationTableSchema $schema)
    {

        if (!$schema->name)
            throw new Exception("У схемы нет имени. Я даже не могу представить, как такое произошло.");
        if (!$schema->primaryKey)
            throw new Exception("Не удалось найти первичный ключ(primary key) для схемы '{$schema->name}'. Проверьте наличие поля \$id в модели");

        $columnNames = $schema->getColumnNames();
        foreach ($columnNames as $name)
        {
            $column = $schema->getColumn($name);
            if (!$column->type)
                throw new Exception("Не удалось найти тип данных PHP для поля '{$column->name}' для схемы '{$schema->name}'. Вероятно тэг @var был задан некорректно.");
            if (!$column->dbType)
                throw new Exception("Не удалось найти тип данных SQL для поля '{$column->name}' для схемы '{$schema->name}'. Вероятно тэг @var был задан некорректно или не заданы связи и валидаторы.");

            $isVarchar = strpos(strtolower($column->dbType), "varchar") !== false;
            if ($isVarchar && !$column->size)
                throw new Exception("Не удалось найти размерность поля '{$column->name}' для схемы '{$schema->name}'. Тип был определен как VARCHAR: скорее всего не хватает валидатора length для поля.");

            if ($column->isForeignKey)
            {
                $data = $schema->foreignKeys[$column->name];
                $foreignTable = $data[0];
                $foreignField = $data[1];
                if (!$foreignTable)
                    throw new Exception("Не удалось найти внешнюю таблицу для внешнего ключа '{$column->name}' для схемы '{$schema->name}'. Вероятно в массиве Rerations указаны неверные данные.");
                if (!$foreignField)
                    throw new Exception("Не удалось найти внешнее поле для внешнего ключа '{$column->name}' для схемы '{$schema->name}'. Вероятно в массиве Relations указаны неверные данные.");
            }
        }
    }

    /**
     * Создает схему таблицы для модели, исходя из данных модели и комментариев
     *
     * @param ActiveRecord $model Обект модели. Можно пустой
     * @param bool $addForeignKeys Добавлять ли внешние ключи
     * @return MigrationTableSchema Объект схемы базы данных
     * @throws ReflectionException
     */
    public static function createTableSchemaForModel($model,$addForeignKeys = true)
    {
        $className = get_class($model);
        $reflector = new ReflectionClass($className);

        //Создаем схему для таблиц
        $tableSchema = new MigrationTableSchema();
        $tableSchema->name = $model->tableName();
        $tableSchema->rawName = "`" . $model->tableName() . "`";
        $tableSchema->comment = \Hs\Helpers\ClassHelper::getDescriptionForClass($className);

        //Создаем схемы для ячеек
        $excludeFields = array("db");
        $props = $reflector->getProperties();
        foreach ($props as $prop)
        {
            if (in_array($prop->getName(), $excludeFields))
                continue;
            
            //Считается, что поля хранящиеся в БД  - публичные. 
            //Я пока не уверен будет ли так в будущем. 30.05.16
            if($prop->isProtected())
                continue;
            
            $schema = new MigrationColumnSchema();
            $schema->name = $prop->getName();
            $schema->rawName = "`" . $prop->getName() . "`";
            $schema->allowNull = !static::hasValidator($model, $prop->getName(), "required");
            $schema->isPrimaryKey = strtolower($prop->getName()) == "id";
            $schema->autoIncrement = strtolower($prop->getName()) == "id";
            $schema->isForeignKey = $addForeignKeys ? static::isForeignKey($model, $prop->getName()) : false;
            $schema->type = static::getTypeForField($model, $prop->getName());
            $schema->dbType = static::getSQLTypeForField($model, $prop->getName());
            $schema->size = static::getSizeForField($model, $prop->getName());
            $schema->precision = static::getSizeForField($model, $prop->getName());
            $schema->comment = \Hs\Helpers\ClassHelper::getDescriptionForField($model, $prop->getName());
            $schema->hasDefaultValue = static::hasDefaultValue($model, $prop->getName());
            $schema->defaultValue = static::getDefaultValueForField($model, $prop->getName());
            $schema->isCompositeIndex=  static::isCopmositeIndex($model,$prop->getName());
            $tableSchema->addColumn($schema);

            //Добавляем внешний ключ в схему таблицы
            if ($schema->isForeignKey)
            {
                $tablename = static::getForeignTableForForeignKey($model, $prop->getName());
                
                $keyname = static::getForeignFieldForForeignKey($model, $prop->getName());
                $update = static::getForeignKeyCascadeRuleForUpdate($model, $prop->getName());
                $delete = static::getForeignKeyCascadeRuleForDelete($model, $prop->getName());
                $tableSchema->addForeignKey($schema->name, $tablename, $keyname, $update, $delete);
            }
            //print_r($schema);
            //print_r($model->tableSchema->getColumn($prop->getName()));
        }

//	echo "<pre>";
//	print_r($model->tableSchema);
//	print_r($tableSchema);
        return $tableSchema;
    }

    /**
     *  Проверяет есть ли у поля значение по-умолчанию.
     * 
     * @param ActiveRecord $model Объект модели
     * @param String $fieldname Имя поля
     * @return boolean
     */
    public static function hasDefaultValue($model, $fieldname)
    {
        $reflected = new ReflectionClass($model);
        $props = $reflected->getDefaultProperties();
        if (isset($props[$fieldname]))
            return true;
        return false;
    }

    /**
     * Получает значение по умолчанию для поля
     * <b>Для точного результата необходимо использовать в паре с функцией hasDefaultValue. Иначе null имеет 2 смысла.</b>
     * 
     * @param ActiveRecord $model Объект модели
     * @param String $fieldname Имя поля
     * @return Mixed Значение по умолчанию для поля или null, если его нет.
     */
    public static function getDefaultValueForField($model, $fieldname)
    {
        $reflected = new ReflectionClass($model);
        $props = $reflected->getDefaultProperties();
        if (isset($props[$fieldname]))
            return $props[$fieldname];
        return null;
    }

    /**
     * Получает код уникальных полей модели
     * 
     * @param String $modelname Имя модели
     * @param String[] $deleteFields Поля, которые не нужно возвращать
     * @return CodeEntity[] Ассоциативный массив кусков кода. Ключем является имя поля.
     */
    public static function getCustomFieldsCode($modelname)
    {
        $model = static::createModelInstance($modelname);
        if (!$model)
            return null;

        $reflector = new ReflectionClass($modelname);
       
        $result = array();
        foreach($reflector->getProperties() as $property)
        {
            if($property->getDeclaringClass()->getName() != $modelname)
                continue;
            $comment = $property->getDocComment();
            $field = new CodeEntity($property->getName(),  CodeEntity::TYPE_FIELD);
            $field->commentary = $property->getDocComment();
            $field->code = static::getFieldCode($modelname,$property->getName());
            $result[$field->getName()] = $field;
        }
        return $result;
    }
    
    /**
     * Получение кода класса, что не относится к полям и методам.
     * Например туда войдут комментарии и константы
     * 
     * @param String $modelname Имя класса
     * @return String Уникальный код
     */
    public static function getCustomCode($modelname){
        
         $model = static::createModelInstance($modelname);
        if (!$model)
            return null;
        
        $reflector = new ReflectionClass($modelname);
        $classCode = file_get_contents($reflector->getFileName());
        $methods = $reflector->getMethods();
        foreach ($methods as $method)
            $classCode = static::removeMethodFromCode($classCode, $model, $method->getName());

        //Удаляем код автополей
        $fields = $reflector->getProperties();
        foreach ($fields as $field)
            $classCode = static::removeFieldFromCode($classCode, $model, $field->getName());
        
        $classCode = static::stripClassCode($classCode);
        //Debug::drop($classCode);
        return $classCode;
    }
    /**
     * Получение кода методов
     * 
     * @param String $modelname Имя модели
     * @return ReflectionEntity[] Массив данных о коде
     */
    public static function getCustomMethodsCode($modelname)
    {
        $model = static::createModelInstance($modelname);
        if (!$model)
            return array();
        $reflector = new ReflectionClass($modelname);
        $result = array();
        //Удаляем код методов
        $methods = $reflector->getMethods();
        foreach ($methods as $method)
        {
            if ($method->getDeclaringClass()->getFileName() != $reflector->getFileName())
                continue;
            $reflectionEntity = new CodeEntity($method->getName(), CodeEntity::TYPE_METHOD);
            $reflectionEntity->autogenerated = Hs\Helpers\ClassHelper::getTagForMethod($model, $method->getName(), "autogenerated");
            $reflectionEntity->code = static::getMethodCode($modelname, $method->getName());
            $reflectionEntity->commentary = Hs\Helpers\ClassHelper::getCommentForMethod($model, $method->getName());
            $result[$reflectionEntity->getName()] = $reflectionEntity;
        }
//	bug::drop($result);
        return $result;
    }

    /**
     * Удаляет метод из кода класса.
     * 
     * @param String $classCode Код класса
     * @param ActiveRecord $model Объект модели
     * @param String $methodname Имя метода
     * @return String Код класса без метода
     */
    protected static function removeMethodFromCode($classCode, $model, $methodname)
    {
        
        $code = Hs\Helpers\ClassHelper::getCommentForMethod($model, $methodname);
        if (trim($code) != "")
            $classCode = str_replace($code, "", $classCode);
        $code = static::getMethodCode($model, $methodname);
        if (trim($code) != "")
            $classCode = str_replace($code, "", $classCode);
        return $classCode;
    }

    /**
     * Удаляет поле из кода класса.
     * 
     * @param String $classCode Код класса
     * @param ActiveRecord $model Объект модели
     * @param String $fieldname Имя поля
     * @return String Код класса без поля
     */
    protected static function removeFieldFromCode($classCode, $model, $fieldname)
    {

        $code = Hs\Helpers\ClassHelper::getCommentForField($model, $fieldname);
        if (trim($code) != "")
            $classCode = str_replace($code, "", $classCode);
        $code = static::getFieldCode($model, $fieldname);
        if (trim($code) != "")
            $classCode = str_replace($code, "", $classCode);
        return $classCode;
    }

    /**
     * Очищает код класса от объявления класса, оставляя только методы и поля
     * 
     * @param String $classCode Код класса
     * @return String Код полей и методов
     */
    protected static function stripClassCode($classCode)
    {
        //Удаляем часть до первого {
        $initialPart = strstr($classCode, "{", true) . "{";
        $classCode = str_replace($initialPart, "", $classCode);

        //Удаляем все что после последнего }
        $parts = explode("}", $classCode);
        array_pop($parts);
        $classCode = join("}", $parts);


        //удаляем пробелы из начала и конца
        $classCode = trim($classCode);
//	echo "<pre>";
//	var_dump(explode("\n", $classCode));
//	die();

        return $classCode;
    }

    /**
     * Получение кода метода модели
     * 
     * @param String $modelname Имя модели
     * @param String $methodname Имя метода
     * @return String Код метода
     */
    public static function getMethodCode($modelname, $methodname)
    {
        $reflector = new ReflectionClass($modelname);
        $method = $reflector->getMethod($methodname);
        $filename = $method->getDeclaringClass()->getFileName();
        $start_line = $method->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
        $end_line = $method->getEndLine();
        $length = $end_line - $start_line;

        $source = file($filename);
        $body = implode("", array_slice($source, $start_line, $length));
        
        //Теперь обработаем тело, выяснив паддинг
       // $padding = CodeGenHelper::getPadding($body);
       // $body = CodeGenHelper::removePadding($body,$padding);
        
        return $body;
    }

    /**
     * Получение кода поля модели
     * 
     * @param String $modelname Имя модели
     * @param String $fieldname Имя поля модели
     * @return String Код поля
     */
    public static function getFieldCode($modelname, $fieldname)
    {
        $reflector = new ReflectionClass($modelname);
        $filename = $reflector->getFileName();
        $classCode = file_get_contents($filename);
        $lines = explode("\n", $classCode);
        $modifiers = array("public", "protected", "private");
        foreach ($lines as $line)
        {
            $parts = explode(" ", $line);
            $intersection = array_intersect($modifiers, $parts);
            $isField = !empty($intersection);

            $hasFieldname = strpos($line, $fieldname) !== false;

            if ($isField && $hasFieldname)
                return $line;
        }
        return null;
    }

    /**
     * Создает модель ActiveRecord в обход конструктора. Необходимо, если нет таблицы в БД.
     * 
     * @param String $modelname Имя класса модели
     * @return ActiveRecord Объект модели или null
     */
    public static function createModelInstance($modelname)
    {
        try
        {
            $reflector = new ReflectionClass($modelname);
            if($reflector->getName() != $modelname)
            {
                return null;
            }
            $instance = $reflector->newInstance(null);
            return $instance;
        } catch (Exception $ex)
        {
            null;
        }
    }

    /**
     * Получение массива связи по названию поля, явзяющегося внешним ключом
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldName Имя поля
     * @return Array
     */
    public static function getRelationByFieldName($model, $fieldName)
    {
        $relations = $model->relations();
        foreach ($relations as $relation)
            if ($relation[2] == $fieldName)
            {
                return $relation;
            }
        return null;
    }

    /**
     * Получение значение каскадного поведения UPDATE для внешнего ключа.
     * 
     * @see ForeignKey
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldName Имя поля
     * @return Int Значение константы каскадности или NULL если она не указана
     */
    public static function getForeignKeyCascadeRuleForUpdate($model, $fieldName)
    {
       $tag = Hs\Helpers\ClassHelper::getTagForField($model, $fieldName, "update");
        if(!$tag)
            return null;
        $value = Hs\Helpers\ClassHelper::getTagValue($tag);
        return $value;
    }

    /**
     * Получение значение каскадного поведения DELETE для внешнего ключа.
     * 
     * @see ForeignKey
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldName Имя поля
     * @return Int Значение константы каскадности или NULL если она не указана.
     */
    public static function getForeignKeyCascadeRuleForDelete($model, $fieldName)
    {
        $tag = Hs\Helpers\ClassHelper::getTagForField($model, $fieldName, "delete");
        if(!$tag)
            return null;
        $value = Hs\Helpers\ClassHelper::getTagValue($tag);
        return $value;
    }

    /**
     * Получение названия внешнего поля по названию поля, являющегося внешним ключом
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldName Имя поля
     * @return String Название таблицы
     */
    public static function getForeignFieldForForeignKey($model, $fieldName)
    {
        $relation = static::getRelationByFieldName($model, $fieldName);
        if (!$relation)
            return null;
        if (isset($relation["on"]))
            return $relation["on"];
        return "id";
    }

    /**
     * Получение названия внешней таблицы по названию поля, являющегося внешним ключом
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldName Имя поля
     * @return String Название таблицы
     */
    public static function getForeignTableForForeignKey($model, $fieldName)
    {

        $relation = static::getRelationByFieldName($model, $fieldName);
        if (!$relation)
            return null;
        $modelClass = $relation[1];
        
        //Если внешняя таблица и есть обыкновенная таблица
        //В миграциях, где еще таблиц нет без данной строки будет ошибка
        //Все из-за того, что $model была создана рефлексией
        if($modelClass == get_class($model))
            return $model->tableName();

        $obj = new $modelClass;
        $tablename = $obj->tableName();
        return $tablename;
    }

    /**
     * Получает тип даных SQL для поля модели.
     * <b>Тип данных получается исходя из комментариев и связей</b>
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $field Имя поля модели
     * @return String Тип данных SQL и размерность, например VARCHAR(30)
     */
    public static function getSqlTypeForField($model, $field)
    {
        //если указан sqltype, то возвращаем его
        $tag = Hs\Helpers\ClassHelper::getTagForField($model, $field, "sqltype");
        if ($tag)
        {
            $parts = explode(" ",StringHelper::removeDoubleSpaces($tag));
            $type = $parts[1];
            return $type;
        }

        //Если нет задан тип, то вычисляем его от типа php
        $phpType = static::getTypeForField($model, $field);

        //для строк, нужно отличить varchar от text
        //для этого нужно будет проверить валидаторы
        if ($phpType == "string")
        {
            $varcharValidators = array("length");
            $validators = static::getValidatorsForField($model, $field);
            $intersection = array_intersect($validators, $varcharValidators);
            $isVarchar = !empty($intersection);
            if ($isVarchar)
            {
                $length = static::getSizeForField($model, $field);
                $length = $length ? $length : 255;
                return "VARCHAR($length)";
            } else
                return "TEXT";
        }

        //Необходимо определить первичные и внешние ключи
        if (strpos($phpType, "int") !== false)
        {
            if (strtolower($field) == "id" || static::isForeignKey($model, $field))
                return EnvHelper::isSQLite () ? "INTEGER" : "INT(11) UNSIGNED";
        }
        //тут более простые типы данных
        switch (strtolower($phpType))
        {
            case "boolean" :
            case "bool" : return "TINYINT(1)";
                break;
            case "integer" : return "INT(10)";
                break;
            case "int" : return "INT(10)";
                break;
            case "number" : return "DOUBLE";
                break;
            case "double" : return "DOUBLE";
            case "float" : return "DOUBLE";
                break;
        }
        return null;
    }

    /**
     * Получает тип даных для поля модели.
     * <b>Тип данных получается исходя из комментариев</b>
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldname Имя поля модели
     * @return String Название типа, указаного в комментарии
     */
    public static function getTypeForField($model, $fieldname)
    {
        $tag = Hs\Helpers\ClassHelper::getTagForField($model, $fieldname, "var");
        if (!$tag)
            return null;
        $parts = explode(" ", $tag);
        $parts = ArrayHelper::removeEmptyElements($parts);
        //идеальный тег var выглядит так: "@var String $fieldname Коментарий о том для чего это поле"
        //для тега var значение типа будет первым после @var
        $type = $parts[1];
        $type = strtolower($type);
        return $type;
    }

    

    /**
     * Получение валидаторов для определенного поля модели
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldname Имя поля модели
     * @return String[] Массив имен валидаторов
     */
    public static function getValidatorsForField($model, $fieldname)
    {
        $rules = static::getRulesForField($model, $fieldname);
        $validators = array();
        foreach ($rules as $rule)
            $validators[] = $rule[1];
        $validators = array_unique($validators);
        return $validators;
    }

    /**
     * Получение правила, которые применяются к полю модели
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldname Имя поля модели
     * @param String $rulename Имя валидатора
     * @return String[] Массив, описывающий правило валидации или NULL если его нет
     */
    public static function getRuleForField($model, $fieldname, $rulename)
    {
        $rules = static::getRulesForField($model, $fieldname);
        foreach ($rules as $rule)
            if (trim($rule[1]) == $rulename)
                return $rule;
        return null;
    }

    /**
     * Получение правил, которые применяются к полю модели
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldname Имя поля модели
     * @return Array Массив правил валидации
     */
    public static function getRulesForField($model, $fieldname)
    {
        $rules = $model->rules();
        $res = array();
        foreach ($rules as $rule)
        {
            if (strpos($rule[0], $fieldname) !== false)
                $res[] = $rule;
        }

        return $res;
    }

    /**
     * Получает максимальную длину строки в поле.
     * Данный метод работает на основе валидатора length.
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldname Имя поля модели
     * @return Integer Максимальная длина строки
     */
    public static function getSizeForField($model, $fieldname)
    {
        $rule = static::getRuleForField($model, $fieldname, "length");
        $result = isset($rule["max"]) ? $rule['max'] : null;

        return $result;
    }

    /**
     * Проверяет, есть ли у поля данный валидатор
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $fieldname Имя поля модели
     * @param String $validatorname Имя валидатора
     * @return Bool True, если является
     */
    public static function hasValidator($model, $fieldname, $validatorname)
    {
        $validator = static::getRuleForField($model, $fieldname, $validatorname);
        $result = $validator != null;
        return $result;
    }

    /**
     * Проверяет, является ли поле внешним ключем.
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $field Имя поля модели
     * @return Bool True, если является
     */
    public static function isForeignKey($model, $field)
    {
        $foreinKeys = static::getForeignKeys($model);
        $result = in_array($field, $foreinKeys);
        return $result;
    }

    /**
     * Получет внешние ключи модели.
     * Внешний ключ может быть только у связи BELONGS_TO.
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @return String[] Массив имен полей, которые являются внешними ключами для модели
     */
    public static function getForeignKeys($model)
    {
        $belongsConst = ActiveRecord::BELONGS_TO;
        $relations = $model->relations();
        $keys = array();
        foreach ($relations as $rel)
            if ($rel[0] == $belongsConst)
                $keys[] = $rel[2];

        return $keys;
    }
    
    
    /**
     * Проверяет, является ли поле уникальным составным индексом
     * 
     * @param ActiveRecord $model Объект модели. Можно пустой.
     * @param String $field Имя поля модели
     * @return Bool True, если является
     */
    public static function isCopmositeIndex($model, $field)
    {
        $tagIndex = Hs\Helpers\ClassHelper::getTagForField($model, $field, 'index');
        $tagComposite = Hs\Helpers\ClassHelper::getTagForField($model, $field, 'composite');
        $value = Hs\Helpers\ClassHelper::getTagValue($tagIndex,0);
        
        if ($value == 'Unique' && $tagComposite == "@composite")
            return TRUE;
        
        return FALSE;
    }
    
    /**
     * Получет уникальные составные индексы
     * 
     * @param MigrationTableSchema $schema Объект Схемы таблицы для модели. 
     * @return String[] Массив имен полей, которые являются уникальными составными индексами
     */
    public static function getCopmositeIndexes(MigrationTableSchema $schema)
    {
        $str = '';
        foreach ($schema->getColumnNames() as $columnName){
            $col = $schema->getColumn($columnName);
            if ($col->isCompositeIndex)
               $str.=$columnName . ',';
       }
       return $str;
    }   

    /**
     * Объединяет 2 массива правил модели в один.
     * Объединение происходит элегантно, правила не дублируются.
     * 
     * @param Array $rules1 Первый массив правил
     * @param Array $rules2 Второй массив правил
     * @return Array Массив содержащий все правила
     */
    public static function mergeRules($rules1, $rules2)
    {
        //Карта это массив  каждый элемент которого это массив с ключами "validator","params","fields".
        //Вся суть проблемы в дополнительных параметрах у некоторых правил. 
        //Чтобы их объединиь 2 таких правила нужно точное соответствие параметров
        $map = array();
        $rules = array_merge($rules1, $rules2);
        foreach ($rules as $rule)
        {
            $validator = $rule[1];
            //Получаем поля
            $fieldString = $rule[0];
            $fields = explode(",", str_replace(" ", "", $fieldString));
            //получаем параметры валидатора
            $params = array_diff($rule, array($validator, $fieldString));

            //Попытаемся включить валидаторы в те, что уже существуют в карте
            $merged = false;
            foreach ($map as $key => $arr)
            {
                //параметры валидатора
                $ruleParams = $arr['params'];
                $ruleValidator = $arr['validator'];
                if ($ruleValidator == $validator && ArrayHelper::equals($ruleParams, $params))
                {
                    $map[$key]['fields'] = array_unique(array_merge($arr['fields'], $fields));
                    $merged = true;
                    break;
                }
            }
            //Если не удалось включить правила существующий валидатор, то создаем новый
            if (!$merged)
                $map[] = array("fields" => $fields, "params" => $params, "validator" => $validator);
        }

        //Теперь просто надо пройтись по карте и готово!
        $result = array();
        foreach ($map as $arr)
        {
            $params = $arr['params'];
            $fields = $arr['fields'];
            $validator = $arr['validator'];
            $rule = array(join(", ", $fields), $validator);
            $final = array_merge($rule, $params);
            $result[] = $final;
        }

        return $result;
    }

    /**
     * Совмещает 2 массива связей, возвращая один без повторений.
     * 
     * @param Array $relations1 Массив связей
     * @param Array $relations2 Массив связей
     * @return Array Объединенный массив связей
     */
    public static function mergeRelations($relations1, $relations2)
    {
        $keys = array();
        $result = array();
        foreach ($relations1 as $key => $rel)
        {
            $result[$key] = $rel;
            $keys[] = $rel[1] . $rel[2];
        }
        foreach ($relations2 as $key => $rel)
        {
            if (!in_array($rel[1] . $rel[2], $keys))
                $result[$key] = $rel;
            //почему мы не используем $key?
            //А все из-за приоритета - второй массив имеет меньший приоритет 
            //и скорее всего был сгенирирован сомнительным путем (генератор кода)
            //поэтому мы не можем доверять названию его связи
        }
        return $result;
    }

    /**
     * Совмещает два массива тегов без повторений.
     * 
     * @param String[] $tags1 Первый массив тегов
     * @param String $tags2 Второй массив тегов
     * @return String[] Совмещенный массив тегов
     */
    public static function mergeTags($tags1, $tags2)
    {
        //В данный массив необходимо внести данные о том, какие теги могут повторяться много раз
        //Ключ это имя тега, а значение это номер слова который его однозначно идентифицирует
        //например для @param String $name - это будет 2, 
        //потому что другого параметра $name у функции быть не может
        $multiple = array("@param" => 2, "@property" => 2);
        $tagNames = array();
        $multipleValues = array();
        $result = array();
        $alltags = array_merge($tags1, $tags2);
        foreach ($alltags as $line)
        {
            $parts = explode(" ", $line);
            $name = $parts[0];
            $added = ArrayHelper::addValueIfNotExists($tagNames, $name);
            $isMultiple = isset($multiple[$name]);
            if ($isMultiple)
                $added = ArrayHelper::addKeyAndAddValueIfNotExists($multipleValues, $name, $parts[$multiple[$name]]);
            if ($added)
                $result[] = $line;
        }
        //Сейчас результат уже почти готов, только повторяющиеся теги
        //Могут быть неотсортированы
        $sorted = array();
        foreach ($result as $line)
        {
            $parts = explode(" ", $line);
            $name = $parts[0];
            foreach ($result as $key => $line2)
            {
                $parts = explode(" ", $line2);
                $name2 = $parts[0];
                if ($name == $name2)
                {
                    $sorted[] = $line2;
                    unset($result[$key]);
                }
            }
            //Debug::show("sorted",$sorted);
        }
        //Debug::drop($tags1, $tags2, $tagNames, $multipleValues, $result, $sorted);
        return $sorted;
    }
    
    /**
     * Совмещает два массива лейблов без повторений.
     * 
     * @param String[] $labels1 Первый массив лейблов
     * @param String[] $labels2 Второй массив лейблов
     * @return String[] Совмещенный массив лейблов
     */
    public static function mergeLabels($labels1,$labels2)
    {
        //Тут вообще нет причуд - лейбл добавляет только если него нет
        $result = $labels1;
        foreach($labels2 as $key => $value)
        {
            if(!isset($result[$key]))
                $result[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Получает список валидаторов, используемых в модели
     * 
     * @param ActiveRecord $model
     * @return String[] Массив имен валидаторов
     */
    public static function getValidators($model)
    {
        $rules = $model->rules();
        $vals = array();
        foreach ($rules as $rule)
            $vals[] = trim($rule[1]);
        return $vals;
    }
    
    /**
     * Добавляет тег в массив тегов, если его там раньше не было
     * 
     * @param String[] $tags Массив тегов
     * @param String $newTag Новый тег
     * @param Bool $rewrite Если True, то значение будет перезаписанно, если оно имелось ранее
     * @return String[] Дополненный массив тегов
     */
    public static function addTagIfNotExists($tags,$newTag,$rewrite = true){
        $newname = Hs\Helpers\ClassHelper::getTagName($newTag);
        $hasTag = false;
        $result = array();
        foreach($tags as $tag)
        {
            $name = Hs\Helpers\ClassHelper::getTagName($tag);
            //Debug::show($newname.$name);
            //Ищем, есть ли оригинальный тег
            if($name == $newname)
            {
                $hasTag = true;
                //Не добавляем оригинальный тег в коментарии, если будем его перезаписывать
                if($rewrite)
                    continue;
            }
            $result[] = $tag;
        }
        //Если мы перезаписываем, то оригинальный тег не добавился в комментарии
        if($rewrite)
            $result[] = $newTag;
        //Debug::drop($result);
        return $result;
    }
    
    
    
    /**
     * Принимает неограниченное количество моделей и сохраняет их в базе данных
     * Сохранение транзакционное - либо все сохраняются, либо никого. 
     * 
     * @param ActiveRecord $model Модель
     * @return boolean True, Если есть параметры
     */
    public static function saveModels(){
        $args = func_get_args();
        $db = Yii::app()->getDb();
        $transaction = $db->beginTransaction();
        $result = true;
        foreach($args as $model){
            $result &= $model->save();
        }
        
        if(!$result)
            $transaction->rollback ();
        else
            $transaction->commit ();
        
        return $result;
    }
    
     /**
     * Добавление нового поля для правила
     * 
     * @param Array $rules Массив, который генерирует 
     * @param String $field Имя поля, которое необходимо добавить
     * @param String $rulename Имя правила
     * @return Array Обновлненный массив правил
     */
    public static function addFieldRule(&$rules, $field, $rulename)
    {
        return static::modifyRules($rules, $rulename, $field, 1);
    }

    /**
     * Удаление нового поля для правила
     * 
     * @param Array $rules Массив, который генерирует 
     * @param String $field Имя поля, которое необходимо удалить
     * @param String $rulename Имя правила
     * @return Array Обновлненный массив правил
     */
    public static function removeFieldRule(&$rules, $field, $rulename)
    {
        return static::modifyRules($rules, $rulename, $field, 0);
    }

    /**
     * Изменение массива правил для перегрузки функции rules(). 
     * Проблема этой функции в том, что правила нельзя дублировать. Поэтому данная функция ищет правила и модифицирует их.
     * 
     * @param Array $rules Массив, который генерирует 
     * @param String $rulename Имя правила
     * @param String $fieldname Имя поля, которое необходимо добавить или удалить
     * @param Integer $action Тип действия 1 - прибавление поля, 2 - удаление.
     * @return Array Обновлненный массив правил
     */
    protected static function modifyRules(&$rules, $rulename, $fieldname, $action)
    {
        foreach ($rules as $key => $rule)
        {
            if ($rule[1] == $rulename)
            {
                $fieldString = str_replace(" ", "", $rule[0]);
                $fields = explode(",", $fieldString);
                if ($action == 0)
                    $fields = array_diff($fields, array($fieldname));
                else if ($action == 1)
                    $fields[] = $fieldname;

                $rules[$key][0] = join(",", $fields);
                return $rules;
            }
        }

        //Если мы здесь, то при добавлении поля правило было не найдено
        //Значит это правило нужно создать
        if ($action == 1)
        {
            $rules[] = array($fieldname, $rulename);
        }

        return $rules;
    }

    /**
     * Получение вхождение я массив rules() связываний для поля модели
     * 
     * @param ActiveRecord $model Модель
     * @param String $field Поле модели, являющиеся внешним ключом
     * @return String Название поля в котором лежит связь
     */
    public static function getRelationFieldForForeignKey(ActiveRecord $model, $field)
    {
        $relations = $model->relations();
        foreach($relations as $key => $relation)
        {
            if($relation[2] == $field)
            {
                return $key;
            }
        }
        return null;
    }
    
    /**
     * Создает поле в таблице БД.
     * 
     * @param MigrationTableSchema $schema Схема таблицы для модели
     * @param String $columnName Название поля, которое необходимо добавить
     * @param CDbConnection $connection Подключение к БД
     */
    public static function addFieldToDatabase($schema, $columnName,$connection)
    {
        $cmd = $connection->createCommand();
        $sqlGenerator = new CMysqlSchema($cmd->connection);
          /* @var $col MigrationColumnSchema */
            $col = $schema->getColumn($columnName);
            //Главный ключ мы добавили до этого
            if ($col->isPrimaryKey)
                return;
            
            $type = $col->dbType;
            if (!$col->allowNull)
                $type.= " NOT NULL";
            if($col->hasDefaultValue)
               $type.= " DEFAULT ". DbHelper::valueToString ($col->defaultValue);
            
            //CDbCommand->addColumn() не позволяет добавлять комментарии
            $sql = $sqlGenerator->addColumn($schema->name, $col->name, $type);
            if(!EnvHelper::isSQLite())
            {
                $sql .= " COMMENT  " . StringHelper::mysqlEscapeString($col->comment);
            }
            $cmd->setText($sql)->execute();

            //echo $cmd->text."<br>";
            //Добавляем внешний ключ, при необходимости
            if ($col->isForeignKey)
            {
                $data = $schema->foreignKeys[$col->name];
                $foreignTable = $data[0];
                $foreignField = $data[1];
                $update = $data[2];
                $delete = $data[3];
                $keyName = $schema->name ."_". $col->name;
                $cmd->addForeignKey($keyName, $schema->name, $col->name, $foreignTable, $foreignField,$delete,$update);
                //echo $cmd->text."<br>";
            }
    }

    /**
     * Находит модель в массиве по ее идентификатору
     * 
     * @param ActiveRecord[] $array Массив моделей
     * @param Integer $id Идентификатор модели
     * @return ActiveRecord Модель или null
     */
    public static function findModelInArrayById($array, $id)
    {
        foreach($array as $model)
        {
            if($model->getPk() == $id)
            {
                return $model;
            }
        }
        return null;
    }

    /**
     * Сохранение массива моделей внутри одной транзакции
     *
     * @param ActiveRecord[] $models Модели, которые необходимо сохранить
     * @param bool $useTransaction Использовать ли транзакцию при сохранении
     * @return bool Результат сохранения
     */
    public static function saveModelsArray($models,$useTransaction = true)
    {
        if(!$useTransaction)
        {
            $result = true;
            foreach($models as $model)
            {
                $result &= $model->save();
            }
            return $result;

        }

        $result = call_user_func_array(['ActiveRecordHelper', 'saveModels'], $models);
        return $result;
    }

    /**
     * Возвращает первое найденное сообщение об ошибке из моделей
     * 
     * @param ActiveRecord[] $models
     * @param Strom $defaultMessage Сообщение об ошибке по-умолчанию
     */
    public static function findFirstError($models, $defaultMessage = "Unknown error")
    {
        foreach($models as $model)
        {
            if($model->hasErrors() || $model->hasActionErrors())
            {
//                bug::Drop($model->getFirstError());
                return $model->getFirstError();
            }
        }
        return $defaultMessage;
    }

    public static function synchronizeTableForModel($modelname, $tableName = null)
    {
        $model = ActiveRecordHelper::createModelInstance($modelname);
        if($model == null)
        {
            throw new Exception("Can't make instance for '$modelname'");
        }
        $tableName = $tableName ? $tableName : $model->tableName();
        $db = Yii::app()->getDb();
        $sqlGenerator = new CMysqlSchema($db);
        $dbSchema = $db->getSchema()->getTable($tableName, true);
        $modelSchema = ActiveRecordHelper::createTableSchemaForModel($model);

        //Переименовываем таблицу
        Yii::log("Rename check");
        if ($model->tableName() != $tableName)
        {
            if($dbSchema != null)
            {
                Yii::log("Renaming table '{$tableName}' to {$model->tableName()}");
                $sql = $sqlGenerator->renameTable($tableName, $model->tableName());
                $db->createCommand($sql)->execute();
            }
            $dbSchema = $db->getSchema()->getTable($model->tableName(), true);
            
        }
        Yii::log("Updating columns");
        $same = array_intersect($modelSchema->getColumnNames(), $dbSchema->getColumnNames());
        foreach ($same as $column)
        {
            static::compareAndUpdateColumn($dbSchema, $modelSchema, $column, $sqlGenerator);
        }

//        bug::drop($same, get_class($modelSchema), get_class($dbSchema));
        Yii::log("Adding new columns");
        $diffNew = array_diff($modelSchema->getColumnNames(), $dbSchema->getColumnNames());
        foreach ($diffNew as $column)
        {
            static::addFieldToDatabase($modelSchema, $column, $db);
        }

        Yii::log("Renaming old columns");
        $diffOld = array_diff($dbSchema->getColumnNames(), $modelSchema->getColumnNames());
        foreach ($diffOld as $column)
        {
            static::makeColumnSafe($dbSchema,$column,$sqlGenerator);
        }
        $model->refreshMetaData();
        $db->getSchema()->refresh();
    }
    
    protected static function makeColumnSafe(CDbTableSchema $dbSchema, $column, CMysqlSchema $sqlGenerator)
    {
        $result = [];
        $to = $dbSchema->getColumn($column);
        if($to->name[0] == "_")
        {
            //The deed is done
            return;
        }
        
        if ($to->isForeignKey)
        {
            $key = DbHelper::getForeignKey($dbSchema->name, $to->name);
            $sql = $sqlGenerator->dropForeignKey($key->name, $dbSchema->name);
            $result[] = $sql; 
            $sqlGenerator->getDbConnection()->createCommand($sql)->execute();
        }
        if(!$to->allowNull)
        {
            $type = str_replace("NOT NULL"," ",$to->dbType);
            if(!StringHelper::hasSubstring($type,"DEFAULT"))
            {
                $type .= " DEFAULT NULL";
            }
            $sql = $sqlGenerator->alterColumn($dbSchema->name, $to->name,$type);
            $result[] = $sql;
            $sqlGenerator->getDbConnection()->createCommand($sql)->execute();
            $to = $sqlGenerator->getDbConnection()->getSchema()->getTable($dbSchema->name,true)->getColumn($column);
        }
        
        $sql2 = $sqlGenerator->renameColumn($dbSchema->name, $to->name,"_".$to->name);
        $sqlGenerator->getDbConnection()->createCommand($sql2)->execute();
        $result[] = $sql2;
    }
    
    protected static function compareAndUpdateColumn(CDbTableSchema $dbSchema, MigrationTableSchema $modelSchema, $column, CMysqlSchema $sqlGenerator)
    {
        $result = [];
        $to = $dbSchema->getColumn($column);
        $from = $modelSchema->getColumn($column);
        if ($to->name == "id")
        {
            return;
        }

        $type = $from->dbType;
        if (!$from->allowNull)
        {
            $type .= " NOT NULL";
        }
        if ($from->hasDefaultValue)
        {
            $type .= " DEFAULT " . DbHelper::valueToString($from->defaultValue);
        }
        $sql = $sqlGenerator->alterColumn($dbSchema->name, $to->name, $type);
        $sql .= " COMMENT  " . StringHelper::mysqlEscapeString($from->comment);
//        $sqlGenerator->getDbConnection()->createCommand($sql)->execute();


        if ($to->isForeignKey)
        {
            $dbName = $sqlGenerator->getDbConnection()->getDbName();
            $key = DbHelper::getForeignKey($dbSchema->name, $to->name,$dbName);
            $result[] = $sqlGenerator->dropForeignKey($key->name, $dbSchema->name);
        }
        $result[] = $sql;
        if($from->isForeignKey)
        {
            $data = $modelSchema->foreignKeys[$to->name];
            $foreignTable = $data[0];
            $foreignField = $data[1];
            $update = $data[2];
            $delete = $data[3];
            $keyName = $modelSchema->name ."_". $from->name;
            $result[] = $sqlGenerator->addForeignKey($keyName, $modelSchema->name, $from->name, $foreignTable, $foreignField,$delete,$update);
        }
        
        //Исполняем запросы
        foreach($result as $sql)
        {
            $sqlGenerator->getDbConnection()->createCommand($sql)->execute();
        }
    }

    /**
     * Переименование поля модели
     *
     * @param string $modelname Нащвание модели
     * @param string $oldName Старое имя поля
     * @param string $newName Новое имя поля
     * @return bool True, если были произведены операции с базой данных, False, если в них небыло необходимости
     * @throws CDbException
     */
    public static function renameFieldForModel($modelname,$oldName, $newName)
    {
        Yii::log("Renaming field '$oldName' to '$newName' for '$modelname'");
        $model = static::createModelInstance($modelname);
        if($model == null)
        {
            throw new Exception("Can't make instance for '$modelname'");
        }
        if(!property_exists($modelname,$newName))
        {
            throw new Exception("Field '$newName' does not exist in model '$modelname' ");
        }
        $tableName = $model->tableName();
        $db = Yii::app()->getDb();
        $dbSchema = $db->getSchema()->getTable($tableName, true);
        if($dbSchema->getColumn($newName))
        {
            Yii::log("Already has column '$newName'', skipping");
            return false;
        }

        if(!$dbSchema->getColumn($oldName))
        {
            Yii::log("Somehow old column '$oldName' doesn't exist in DB, fixing");
            $modelSchema = static::createTableSchemaForModel($model);
            static::addFieldToDatabase($modelSchema, $newName, $db);
            return true;
        }

        $sqlGenerator = new CMysqlSchema($db);
        $sql = $sqlGenerator->renameColumn($tableName,$oldName,$newName);
        $db->createCommand($sql)->execute();
        return true;
    }

    /**
     * Удаляет таблицу для модели
     * @param String $modelName Имя класса модели
     * @throws CDbException
     */
    public static function deleteTableForModel($modelName)
    {
        $instance = ActiveRecordHelper::createModelInstance($modelName);
        $name = $instance->tableName();
        $table = Yii::app()->getDb()->getSchema()->getTable($name);
        if($table)
        {
            Yii::log("Deleting table '$name'");
            Yii::app()->getDb()->createCommand()->dropTable($name);
        }
        else{
            Yii::log("Couldn't find table '$name' skipping");
        }
    }

}
