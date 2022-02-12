<?php

/**
 * Класс прослойка между моделью Yii и моделями приложения.
 * 
 * @method String[] getActionErrors() Возвращает все ошибки действия (или коды, если сайт мультиязычный).
 * @method String getActionError() Возвращает первую ошибку метода или ошибку по-умолчанию (или код, если сайт мультиязычный).
 * @method String addActionError(String $text) Добавение ошибки в модель (или кода, если сайт мультиязычный).
 * @method String getFirstError() Возвращает первую ошибку валидации или ошибку по-умолчанию.
 * @method Boolean hasActionErrors() Проверка, есть ли ошибки действий
 * @method Boolean mergeErrors(String $model) Забирает ошибки действий у модели и добавляет их себе
 * @see ActionErrorsBehaviour
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
abstract class ActiveRecord extends CActiveRecord implements IActiveRecord
{
    /**
     * Флаг означающий, что модель сейчас валидируется.
     * Необходим для исключения рекурсивной валидации
     * 
     * @var Bool 
     */
    protected $validating = false;
    
    /**
     * Флаг озночающий, что модель сейчас сохраняется.
     * Необходим для исключения рекурсивного сохранения
     * 
     * @var Bool 
     */
    protected $saving = false;
  
    /**
     * Связи belongTo, которые необходимо сохранить с данной моделью
     * 
     * @var CActiveRecord[]
     */
    protected $childrenToSave = array();

    /**
     * Связи hasMany и hasOne, которые необходимо сохранить с данной моделью
     * 
     * @var CActiveRecord[]
     */
    protected $parentsToSave = array();
    
    /**
     * Отдельное подключение к базе данных
     * @var CDbConnection 
     */
    protected static $newDb = [];
    
    /**
     * Устанавливает отдельное подлючение к базе данных для моделей этого типа
     * 
     * @param CDbConnection $connection
     */
    public function setDbConnection(CDbConnection $connection)
    {
        self::$newDb[get_called_class()] = $connection;
    }

    /**
     * Перегрузка встроенной функции получения подключений к базе данных, 
     * с учетом возможности подмены базы данных
     * 
     * @param CDbConnection $connection
     */
    public function getDbConnection()
    {
        //@todo Возможно это нужно будет убрать, потому что код ниже намного круче, но я не уверен нужно ли 2 типа моделей с разными БД одновременно
        if (isset(static::$newDb[get_called_class()]))
        {
            return static::$newDb[get_called_class()];
        }

        self::$db = Yii::app()->getDb();
        if (self::$db instanceof CDbConnection)
            return self::$db;
        else
            throw new CDbException(Yii::t('yii', 'Active Record requires a "db" CDbConnection application component.'));
    }

    /**
     * Возвращает первичный ключ модели.
     * Фунция является псевдонимом для getPrimaryKey()
     * 
     * @return Integer Идентификатор модели
     */
    public function getPk()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Возвращает первичный ключ модели
     * 
     * @return Int Первичный ключ модели
     * @throws Exception
     */
    public function getPrimaryKey()
    {
        $ret = parent::getPrimaryKey();
        
        //@todo Необходимо что-то с этим придумать
        if($ret == null)
            ;//    throw new Exception("Попытка получить первичный ключ у несохраненной модели. Так поступать нельзя из-за ленивых связей.");
        return $ret;
    }
    
    /**
     * Получает ссылку на идентификатор модели.
     * <b>Данную функцию нельзя использовать без разрешения</b>
     * @param  $check  Секретка для функции
     * @return Int Ссылка на первичный ключ
     */
    public function &getPrimaryKeyPointer($check = false)
    {
        $schema = $this->getTableSchema();
        $idName = $schema->primaryKey;
        if($check !== true)
            throw new Exception ("Данную функцию нельзя использовать! Она для внутренних нужд! Читай описание.");
        return $this->$idName;
    }
    
    /**
     * Проверяет является ли связь сохраненной в базе данных
     * Функция является псевдонимом для getIsNewRecord()
     * 
     * @return Boolean True, если запись новая
     */
    public function isNew()
    {
        return $this->getIsNewRecord();
    }

    /**
     * Возвращает массив поведений класса
     * @see ActionErrorsBehaviour
     * @return Array[]
     */
    public function behaviors()
    {
        $arr = parent::behaviors();
        $arr['ActionErrors'] = array("class" => "ActionErrorsBehaviour");
        return $arr;
    }

    /**
     * Получение имени таблицы для модели. Поумолчанию это имя модели во множественном числе.
     * Если имя получилось не красивое, то функцию можно перегрузить в классе-наследнике.
     * 
     * @return String
     */
    public function tableName()
    {
        $class = parent::tableName();
        $tableName = strtolower($class);
        if ($tableName[strlen($tableName) - 1] == 's')
            $tableName .= 'es';
        else
            $tableName .= 's';
        return $tableName;
    }

    /**
     * Создание новой модели. 
     * Используется в случае, если модель представляет собой ссылку на класс для поиска или создания экземпляров .
     * 
     * Метод перегружен. И в дальнейшей прегрузке не нуждается.
     * @param String $className
     * @return static
     */
    public static function model($className = null)
    {
        if (!$className)
            $className = get_called_class();

        return parent::model($className);
    }
  
    /**
     * Превращает связь в массив значений для <select>. Это часто необходимо при создании форм
     *  
     * @param String $relationName Имя связи
     * @param String $titleField Название поля связи, которое будет выступать в качестве название <option>
     * @param String $valueField Название поля связи, которое будетв выступать в качестве значения <option>. По-умолчанию первичный ключ.
     * @param String $emptyTitle Название пустого элемента. Если оно задано, то в начало списка будет добавлен пустой элемент. (Обычно это делается, еси выбор делать необязательно)
     * @return Array
     */
    public function relationToDropDownValues($relationName, $titleField, $valueField = null, $emptyTitle = null)
    {
        $array = $this->getRelated($relationName);
        $res = ActiveRecordHelper::modelsToDropDownValues($array, $titleField, $valueField, $emptyTitle);
        return $res;
    }

    /**
     * Добавление нового поля для правила
     * 
     * @param Array $rules Массив, который генерирует 
     * @param String $field Имя поля, которое необходимо добавить
     * @param String $rulename Имя правила
     * @return Array Обновлненный массив правил
     */
    public function addFieldRule(&$rules, $field, $rulename)
    {
        return $this->modifyRules($rules, $rulename, $field, 1);
    }

    /**
     * Удаление нового поля для правила
     * 
     * @param Array $rules Массив, который генерирует 
     * @param String $field Имя поля, которое необходимо удалить
     * @param String $rulename Имя правила
     * @return Array Обновлненный массив правил
     */
    public function removeFieldRule(&$rules, $field, $rulename)
    {
        return $this->modifyRules($rules, $rulename, $field, 0);
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
    protected function modifyRules(&$rules, $rulename, $fieldname, $action)
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
                //Не нужен return, так как правила могут повторяться
                //return $rules;
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
     * Sets the attribute values in a massive way.
     * @param array $values attribute values (name=>value) to be set.
     * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
     * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
     * @see getSafeAttributeNames
     * @see attributeNames
     */
    public function setAttributes($values, $safeOnly = true)
    {
        $ret = parent::setAttributes($values, $safeOnly);

        //по умолчанию 0 - это отсутствие связи в <select>
        //но если в БД есть внешние ключи - это вызовет ошибки
        //поэтому мы заменяем связи, в которых 0 на null.
        $attrs = array_keys($values);
        $relations = array();
        foreach ($this->relations() as $val)
            $relations[] = $val[2];
        $relationSet = array_intersect($attrs, $relations);
        foreach ($relationSet as $field)
        {
            if ($this->$field === "0" || $this->$field === 0)
                $this->$field = null;
        }

        return $ret;
    }

    /**
     * Назначение связи для модели. 
     * В дальнейшем необходимо вызвать функцию модели в базе данных.
     * 
     * 
     * @param CActiveRecord $model Модель, которую необходимо связать
     * @param String $fieldName Имя поля связи (нужно, если есть несколько связей с данной моделью)
     * @return boolean True, если связь была назначена
     * @throws Exception
     */
    public function setRelated(CActiveRecord $model, $fieldName = null)
    {
        //Данная функция пляшет от BelongsTo
        //Если да
        $relationClass = get_class($model);

        //Получаем конфигурацию связи
        $conf = null;
        $relations = $this->relations();
        if ($fieldName !== null)
        {
            $conf = array_merge(array($fieldName), $relations[$fieldName]);
        } else
        {
            foreach ($relations as $key => $arr)
            {
                if ($relationClass == $arr[1] && $arr[0] == CActiveRecord::BELONGS_TO)
                {
                    if ($conf === null)
                    {
                        $conf = array_merge(array($key), $arr);
                    } else
                    {
                        throw new Exception("Для класса '$relationClass' в модели " . get_class($this) . " объявленно несколько связей. Укажите параметр \$fieldName, чтобы уточнить связь.");
                    }
                }
            }
        }

        if (!$conf)
        {
            throw new Exception("Relation  for '$relationClass' not found in " . get_class($this));
        }

        //Получаем данные
        $fieldName = $conf[0];
        $relationType = $conf[1];
        $key = $conf[3];
        
        if($fieldName == $key)
        {
            throw new Exception("Поле '$key' и внешний ключ должны иметь разные имена");
        }
        

        //Обрабатываем  BelongsTo
        if ($relationType == CActiveRecord::BELONGS_TO)
        {
            //Убрал данную проверку, так как она создает кучу SELECT запросов к БД.
//            //Проверяем на дубли и рекурсию
//            if($this->$fieldName && $model->equals($this->$fieldName))
//            {
//                return false;
//            }
            
            //Ставим свои поля
            
            $this->$key = &$model->getPrimaryKeyPointer(true);
            $this->$fieldName = $model;

            //Пытаемся назначить обратную, если она есть
            $modelRelations = $model->relations();
            $modelFieldName = $this->findRelationFieldName($modelRelations, get_class($this), $key,[self::HAS_MANY,self::HAS_ONE]);
            //Debug::show($modelRelations,$key,$modelFieldName);
            if ($modelFieldName)
            {
                //bug::show($modelFieldName);
                $model->setRelated($this,$modelFieldName);
                //Не вижу смысла в дальнеших строчках 
//                $type = $modelRelations[$modelFieldName][0];
//                if ($type == CActiveRecord::HAS_MANY)
//                {
//                    //Нужно доабвлять связь именно так, потому что
//                    //связь является свойством а не полем
//                    //Поэтому массив можно только перезаписать полностью!
//                    //Добавить элементы туда не получится!
//                    $many = $model->$modelFieldName;
//                    $many[] = $this;
//                    $model->$modelFieldName = $many;
//                } elseif ($type == CActiveRecord::HAS_ONE)
//                {
//                    $model->$modelFieldName = $this;
//                }
            }

            //Сохранение новых моделей обрабатывается со стороны belongsTo
            $this->parentsToSave[$fieldName] = $model;
            //Debug::show($this->relationsToSave);
            return true;
        }

        //Если связь со стороны многих
        if ($relationType == CActiveRecord::HAS_MANY || $relationType == CActiveRecord::HAS_ONE)
        {
            //Проверяем на дубли и рекурсию
            if($this->$fieldName)
                foreach($this->$fieldName as $existingModel)
                    if(!$model->isNew() && $model->equals($existingModel))
                    {
                        
                        return false;
                    }
                    
            if (!property_exists($model, $key))
                throw new Exception("У модели '$relationClass' нет поля внешнего ключа '$key'");

            //Ищем обратную связь и если она есть, то назначаем через этот же интерфейс
            //Если ее нет, то назначаем ключ
            $modelRelations = $model->relations();
            $modelFieldName = $this->findRelationFieldName($modelRelations, get_class($this), $key);
            if ($modelFieldName)
                $model->setRelated($this, $modelFieldName);
            else
            {
                //Назначаем внешний ключ вручную
                //$ref =  $this->getPrimaryKeyPointer("check");
                $model->$key = &$this->getPrimaryKeyPointer(true);;
            }
                //И заполняем собственные данные
                $relation = $this->$fieldName;
                if ($relationType == CActiveRecord::HAS_MANY)
                    $relation[] = $model;
                else
                    $relation = $model;

                $this->$fieldName = $relation;
            
            
            ArrayHelper::addKeyIfNotExists($this->childrenToSave, $fieldName);
            $this->childrenToSave[$fieldName][] = $model;
            return true;
        }
        return false;
    }

    /**
     * Поиск конфигурации для конкретной связи по внешнему ключу
     * 
     * @param Array[] $relations массив связей
     * @param String $class Имя класса для связи
     * @param String $foreignKey Имя внешнего ключа связи
     * @param String[] $typeArr Массив типов свзяли, например [CActiveRecord::BELONGS_TO,CActiveRecord::HAS_MANY]
     * @return Array[] Конфигурация связи или null в случае отсутствия
     */
    protected function findRelationFieldName(&$relations, $class, $foreignKey, $typeArr = null)
    {
        foreach ($relations as $key => $arr)
        {
            if ($arr[1] == $class && $arr[2] == $foreignKey && ($typeArr !== null && in_array($arr[0],$typeArr)))
            {
                return $key;
            }
        }

        return null;
    }

    /**
     * Сохраняет модель в базе данных
     * 
     * @param Bool $runValidation Запускать ли валидацию
     * @param String[] $attributes Массив имен полей, которые необходимо сохранить
     * @param Bool $saveRelations Сохранять ли связанные модели в базе данных (Это необходимо выключать в очень редких случаях, например, когда главная модель является логирующей)
     * @return Bool True, в случае успеха
     */
    public function save($runValidation = true, $attributes = null,$saveRelations = true)
    {
        if($this->saving)
        {
            return true;
        }
        
        $this->saving = true;
        if ($this->hasRelationsToSave() && $saveRelations)
        {
            $saved = $this->saveWithRelatedModels($runValidation);
            $this->saving = false;
            return $saved;
        }

        $ret =  parent::save($runValidation, $attributes);
        if(!$ret)
            {
                Yii::log ("Can't save ".get_class($this)." in database",  CLogger::LEVEL_INFO);
            }
        $this->saving = false;
	    return $ret;
    }
    
    /**
     * Валидирует текущую модель
     * 
     * @param String[] $attributes Массив атрибутов, которые необходимо првоерить. Null - значит все.
     * @param Bool $clearErrors Очищать ли существующие ошибки, перед валидацией
     * @return Bool True, если нет ошибок у модели
     */
    public function validate($attributes = null, $clearErrors = true)
    {
        if($this->validating)
            return true;
        //debug::show(get_class($this),$this->id);
        $this->validating = true;
        
        if($this->hasRelationsToSave())
            return $this->validateWithRelatedModels($clearErrors);
        $ret =  parent::validate($attributes, $clearErrors);
	if(!$ret)
	    Yii::log ("Validation for  ".get_class($this)." has been failed: ".print_r($this->getErrors(),true),  CLogger::LEVEL_INFO);
        
        $this->validating = false;
	return $ret;
    }

    /**
     * Валидирует текущую модель и ее связи
     * 
     * @param Bool $clearErrors Очищать ли существующие ошибки, перед валидацией
     * @return Bool True, если нет ошибок ни у модели, ни у связей
     */
    protected function validateWithRelatedModels($clearErrors = true)
    {
         $result = true;
        foreach($this->parentsToSave as $parent)
	{
	   if(!$parent->validate ($clearErrors))
	   {
	       $msg = "'".get_class($this)."' has error on validation of parent '".get_class($parent)."' : ".$parent->getFirstError();
		Yii::log($msg,  CLogger::LEVEL_INFO);
		$result &= false;
	   }
	}

        if (!$this->isNew())
            foreach($this->childrenToSave as $childs)
                foreach($childs as $child)
                {
		    if(!$child->validate ($clearErrors))
		    {
			$msg = "'".get_class($this)."' has error on validation of child '".get_class($child)."' : ".$child->getFirstError();
			Yii::log($msg,  CLogger::LEVEL_INFO);
			$result &= false;
		    }
		}

        //Валидируем модель
        //Внешние ключи приходится исключить из данной валидации, 
        //так как они могут быть необъявлены
        //но на валидации во время сохранения они будут отвалидированы
        $fks = ActiveRecordHelper::getForeignKeys($this);
        $allAttrs = array_keys($this->getAttributes());
        $attrs = array_diff($allAttrs, $fks);
        $result &= parent::validate($attrs,$clearErrors);

        if ($this->isNew())
             foreach($this->childrenToSave as $childs)
                  foreach($childs as $child)
                    if(!$child->validate ($clearErrors))
                        {
                            $msg = "'".get_class($this)."' has error on validation of child '".get_class($child)."' : ".$child->getFirstError();
                            Yii::log($msg,  CLogger::LEVEL_INFO);
                            $result &= false;
                        }
                    
        if(!$result)
	    Yii::log ("Validation for with related models for ".get_class($this)." has been failed.",  CLogger::LEVEL_INFO);
			
        return $result;
    }
    
    /**
     * Проверяет есть ли зависимости, которые нужно сохранить вместе с моделью
     * 
     * @return Bool
     */
    protected function hasRelationsToSave()
    {
        $result = !empty($this->childrenToSave);
        $result |= !empty($this->parentsToSave);
        return $result;
    }
    
    /**
     * Осуществляет сохранение зависимостей в базу данных
     *
     * Сохранение является транзакционным - если одна зависимость не сохранилась, то изменения откатятся
     * Сохранение является ленивым - как только одна модель не отвалидировалась или не сохранилась
     * остельные модели сохраняться не будут - только валидироваться
     * То что валидация не ленивая - это опять же здравый смысл
     *
     * Алгоритм работы со связями (в целом это просто здравый смысл):
     * Если belongsTo то всегда сохраняем сначала другую сторону, потом себя
     * Если hasMany и данная модель старая то надо сохранить сначала другую сторону, потом себя
     * Если hasMany и текущая модель новая, то сначала сохраняем себя, потом другую модель
     * 
     * @param Bool $runValidation Запускать ли валидацию
     * @return Bool True, в случае успеха
     */
    protected function saveWithRelatedModels($runValidation = true)
    {
        //Создаем транзакцию или получаем существующую
        $transaction = $this->getDbConnection()->beginTransaction();
        
        $result = true;
        if (!empty($this->parentsToSave))
        {
//            $new = [];
//            foreach($this->parentsToSave as $model)
//            {
//                if($model->isNew())
//                {
//                    $new[] = $model;
//                }
//            }
            $result &= $this->saveModels($this->parentsToSave, $runValidation, $result);
        }
        
        //Вот зачем здесь это????
        if (!empty($this->childrenToSave) && !$this->isNew())
        {
            foreach ($this->childrenToSave as $childs)
            {
                $result &= $this->saveModels($childs, $runValidation, $result);
            }
        }

        //Валидируем модель и сохраняем
        $result &= parent::validate();      
        if ($result)
        {
            $result &= parent::save(false);
        }

        if (!empty($this->childrenToSave))
        {
            foreach ($this->childrenToSave as $childs)
            {
                $result &= $this->saveModels($childs, $runValidation, $result);
            }
        }

        //Коммитим транзакцию или откатываемся
        if($result)
        {
            $transaction->commit();
        }
        else
        {
            $transaction->rollback();
        }
	
	if(!$result)
	{
            Yii::log ("Can't save ".get_class($this)." with related models in database: ".print_r($this->getErrors(),1),  CLogger::LEVEL_INFO);
        }
        
        //Ничего не понимаю. Почему-то эта строка вылечила утечки памяти.
        //Вроде как циклических валидаций не было
        //Хотя может быть циклические сохранения были
        $this->parentsToSave = array();
        $this->childrenToSave = array();
	return $result;
    }

    /**
     * Выполняет валидацию и лениво сохраняет модели
     * 
     * @param CActiveRecord[] $models Модели, которые необходимо сохранить
     * @param Bool $runValidation Выполнять ли валидацию моделей перед сохранением
     * @param Bool $result Предыдущий результат (полезно передавать, функция вызывается несколько раз)
     * @return Bool Результат сохранения. True, если все модели сохранились
     */
    protected function saveModels(&$models, $runValidation, $result = true)
    {
        //Вынимаем модель из массива, чтобы небыло бесконечного
        //При назначении связей с двух сторон функцией setRelated()
        while($model = array_shift($models))
        {
            if (!$result)
                continue;
            
            //По-умолчанию мы запускаем валидацию для всех моделей, 
            //но сохраняем только пока они успешно сохраняются
            if ($runValidation)
                $result &= $model->validate();

            if (!$result)
            {
                Yii::log("Can't save '".get_class($model)."' as related model to '".get_class($this)."': ".print_r($model->getErrors(),1), CLogger::LEVEL_WARNING);
                continue;
            }

            $result &= $model->save($runValidation);
            //Debug::drop($this,$model);
        }
	
        return $result;
    }
    
    /**
     * Создает новую модель или получает существующую из базы данных
     * 
     * @param String[] Список имен аттрибутов по котрым должен осуществляться поиск
     * @return \ActiveRecord Модель, сохраненная в БД
     */
    public function saveOrFind($attributes = null)
    {
        $model = $this->search($attributes);
        if($model)
            return $model;
        
        $this->save();
        return $this;
    }
    
    /**
     * Поиск идентичной модели в БД.
     * 
     * @param String[] Список имен аттрибутов по котрым должен осуществляться поиск
     * @return ActiveRecord Модель или null
     */
    public function search($attributes = null)
    {
        $attributes = $this->getAttributes($attributes);
        unset($attributes['id']);
        //bug::drop($attributes);
        $models = $this->model()->findByAttributes($attributes);
        return $models;
    }

    /**
     * Делает правильное сравнение моделей.
     * Модели нельзя сравнивать оператором ==
     * 
     * @param CActiveRecord $record Модель
     * @return boolean True, в случае, если модели равны
     */
    public function equals($record)
    {
        if($record == null)
            return false;
        
        return parent::equals($record);
    }

    /**
     * @param mixed $pk
     * @param string $condition
     * @param array $params
     * @return static[]
     */
    public function findAllByPk($pk, $condition = '', $params = array())
    {
        return parent::findAllByPk($pk, $condition, $params);
    }

    /**
     * @param mixed $pk
     * @param string $condition
     * @param array $params
     * @return static
     */
    public function findByPk($pk, $condition = '', $params = array())
    {
        return parent::findByPk($pk, $condition, $params);
    }


    /**
     * @param string $condition
     * @param array $params
     * @return static[]
     */
    public function findAll($condition = '', $params = array())
    {
        return parent::findAll($condition, $params);
    }

    /**
     * @param string $condition
     * @param array $params
     * @return static
     */
    public function find($condition = '', $params = array())
    {
        return parent::find($condition, $params);
    }


    /**
     * @param array $attributes
     * @param string $condition
     * @param array $params
     * @return static[]
     */
    public function findAllByAttributes($attributes, $condition = '', $params = array())
    {
        return parent::findAllByAttributes($attributes, $condition, $params);
    }

    /**
     * @param array $attributes
     * @param string $condition
     * @param array $params
     * @return static
     */
    public function findByAttributes($attributes, $condition = '', $params = array())
    {
        return parent::findByAttributes($attributes, $condition, $params);
    }

    /**
     * @return static
     */
    public function with()
    {
        return call_user_func_array([$this,"CActiveRecord::with"],func_get_args());
    }
}

?>
