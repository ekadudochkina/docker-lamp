<?php

/**
 * Автоматический контроллер для создания панели управления.
 * Содержит в себе все необходимые заготовки для большинства моделей.
 *
 * @package Hs\Controllers
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class SimpleAdminController extends NeonAdminController
{
    const VIEW_BEFORE_ACTION_LIST = "before-action-list";
    const VIEW_AFTER_ACTION_LIST = "after-action-list";

    /**
     * Ассоциативный массив необязательных вьюшек 
     * @var String[] 
     */
    protected $subviews = [];
    
    /**
     * Номер текущего элемента в списке
     * @var Integer
     */
    protected $number = 0;
    
    
    /**
     * Инструмент для фильтрации и пагинации моделей
     * @var ModelFilter
     */
    protected $filter;

    /**
     * Получение экземпляра объекта модели. Подойдет даже пустая модель.
     * 
     * @return ActiveRecord
     */
    abstract public function getModel();

    /**
     * Вьюшка по-умолчанию
     */
    public function actionIndex()
    {
        $this->actionList();
    }

    /**
     * Отображение списка моделей
     */
    public function actionList()
    {
        $filter = $this->getModelFilter();
        $models = $filter->getModels();
        $this->setNumber($filter);
        $this->addViewData($filter, "filter");
        $this->addViewData($models, "models");
        $this->render("root.lib-Yii.views.admin.crud.list");
    }

    /**
     * Метод создания модели
     */
    public function actionCreate()
    {
        $this->createOrUpdate($this->getModel());
    }

    /**
     * Метод изменения моделей
     * 
     * @param Stirng $id Идентификатор модели
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel();
        $obj = $model->findByPk($id);
        $this->createOrUpdate($obj);
    }
    
    /**
     * Метод просмотра модели
     * 
     * @param Stirng $id Идентификатор модели
     */
    public function actionView($id)
    {
        $model = $this->getModel();
        $obj = $model->findByPk($id);
        $this->addViewData($obj,"model");
        $this->render("root.lib-Yii.views.admin.crud.read");
    }
    
    /**
     * Обобщенный метод заполнения данных модели и сохрания
     * 
     * @see actionCreate()
     * @see actionUpdate()
     * @param ActiveRecord $model Модель
     */
    public function createOrUpdate($model)
    {

        if ($this->setParamsToModels($model))
        {
            $this->afterParamsSet($model);
            $title = $this->getModelTitle($model);
            $new = $model->isNew();
            $successMessage = $new ? $title . " successfully created" : $title . " successfully updated";
            $result = $model->save();
            if ($this->showErrorOrSuccessMessage($result, $model->getFirstError(), $successMessage))
            {
                $url = $new ? $this->getRedirectUrlForCreate($model) : $this->getRedirectUrlForUpdate($model);
                $this->redirect($url);
            }
        }

        $this->addViewData($model, "model");
        $this->render("root.lib-Yii.views.admin.crud.create");
    }

    /**
     * Метод удаления модели
     * 
     * @param String $id Идентификатор модели
     */
    public function actionDelete($id)
    {
        $model = $this->getModel();
        $obj = $model->findByPk($id);
        $title = $this->getModelTitle($model);
        $result = $this->safeDelete($obj, $title);
        $this->showErrorOrSuccessMessage($result, "$title can't be deleted. Probably $title is bound with other objects.", "$title successfully deleted");
        $this->redirectToRoute("index");
    }

    /**
     * Экспорт моделей в CSV
     */
    public function actionExport()
    {
        EnvHelper::enableHs();

        $columnTitles = $this->getExportTitles();
        $exportFileds = $this->getExportFields();
        $models = $this->getModelFilter()->getModels();
        $array = ActiveRecordHelper::modelsToArray($models, $exportFileds, false, true, false);

        $sheet = ArrayHelper::arrayToExcel($array, $columnTitles, null);
        $csv = \Hs\Helpers\PHPExcelHelper::excelToCSV($sheet);

        $title = $this->getListTitle();
        $manager = new Hs\Output\DownloadManager();
        $manager->startDownload($csv, "$title.csv");
    }

    /**
     * Метод мягкого удаления моделей. 
     * Он борется с тем, что возникает исключение связанное с внешними ключами при удалении моделей.
     * В случае неудачи ошибка заносится в список ошибок модели.
     * 
     * @param ActiveRecord $model Модель
     * @param String $objectTitle Название модели, отображаемое пользователю
     * @return boolean True в случае успеха
     * @throws CDbException
     */
    protected function safeDelete(ActiveRecord $model, $objectTitle)
    {
        try
        {
            $model->delete();
        } catch (CDbException $ex)
        {
            if (StringHelper::hasSubstring($ex->getMessage(), "ntegrity constraint violation"))
            {
                $model->addActionError("$objectTitle cannot be deleted because it's already bound with other objects");
                return false;
            }
            throw $ex;
        }
        return true;
    }

    /**
     * Получение списка полей модели, которые необходимо отобразить на контроллере списка.
     * 
     * @return String[]
     */
    public function getListFields()
    {
        $model = $this->getModel();
        $attributes = $model->getAttributes();
        $keys = array_keys($attributes);

        //Сортируем по порядк полей
        $reflectionClass = new ReflectionClass($model);
        $props = $reflectionClass->getProperties();
        $propNames = [];
        array_map(function($a) use(&$propNames) {
            $propNames[] = $a->name;
        },$props);
        $sorted = ArrayHelper::sortWithMap($keys,$propNames);
        return $sorted;
    }

    /**
     * Получение заголовка H1 страницы со списком моделей
     * 
     * @return String
     */
    public function getListTitle()
    {
        $model = $this->getModel();
        $title = $model->tableName();
        $formatted = ucfirst(strtolower($title));
        return $formatted;
    }

    /**
     * Получение заголовка H1 страницы при создании модели
     * 
     * @return string
     */
    public function getCreateTitle()
    {
        $model = $this->getModel();
        $name = $this->getModelTitle($model);
        $title = "Create " . $name;
        return $title;
    }

    /**
     * Получение списка полей модели, которые необходимо отобразить при создании модели
     * 
     * @return String[]
     */
    public function getCreateFields()
    {
        $fields = $this->getListFields();
        $flipped = array_flip($fields);
        unset($flipped["id"]);
        $result = array_flip($flipped);

        //Сортируем по порядк полей
        $reflectionClass = new ReflectionClass($this->getModel());
        $props = $reflectionClass->getProperties();
        $propNames = [];
        array_map(function($a) use(&$propNames) {
            $propNames[] = $a->name;
        },$props);
        $result = array_intersect($result,$propNames);

        return $result;
    }

    /**
     * Получение заголовка H1 страницы при редактировании модели
     * 
     * @return string
     */
    public function getUpdateTitle()
    {
        $model = $this->getModel();
        $name = $this->getModelTitle($model);
        $title = "Edit " . $name;
        return $title;
    }

    /**
     * Получение списка полей модели, которые необходимо отобразить при редактировании модели
     * 
     * @return String[]
     */
    public function getUpdateFields()
    {
        $fields = $this->getCreateFields();
        return $fields;
    }

    /**
     * Получение названия поля модели для отображения пользователю
     * 
     * @param String $field Имя поля
     * @return String Название поля, отображаемое пользователю
     */
    public function getFieldTitle($field)
    {
        $model = $this->getModel();

        $alternatives = $this->getAlternativeFieldTitles();
        if(isset($alternatives[$field]))
        {
            return $alternatives[$field];
        }
        //Сложность возникает, когда поле является внешним ключом и представляет другую модель
        $isForeign = ActiveRecordHelper::getForeignFieldForForeignKey($model, $field) != null;
        if ($isForeign)
        {
            $relationSpec = ActiveRecordHelper::getRelationByFieldName($model, $field);
            $class = $relationSpec[1];
            $obj = new $class;
            $title = $this->getModelTitle($obj);
            return $title;
        }
        $labels = $model->attributeLabels();
        if (!isset($labels[$field]))
        {
            return ucfirst($field);
        }
        return $labels[$field];
    }

    /**
     * Получение значения поля модели
     * 
     * @param ActiveRecord $model Модель
     * @param String $field Имя поля
     * @return String значение поля
     */
    public function getFieldValue(ActiveRecord $model, $field)
    {

        $isForeign = ActiveRecordHelper::getForeignFieldForForeignKey($model, $field) != null;
        if ($isForeign != null)
        {
            $relatedField = ActiveRecordHelper::getRelationFieldForForeignKey($model, $field);
            $relationSpec = ActiveRecordHelper::getRelationByFieldName($model, $field);
            $class = $relationSpec[1];
            $obj = new $class;
            return $this->getForeignFieldValue($model, $relatedField, $obj);
        }
        
        $value = ActiveRecordHelper::getFieldByPath($model, $field);
        return $value;
    }

    /**
     * Получение значения поля модели, которое является внешним ключом
     * 
     * @param ActiveRecord $model Оригинальная модель
     * @param String $field Имя поля
     * @param ActiveRecord $relatedModel Связанная модель
     * @return String значение поля
     */
    public function getForeignFieldValue(ActiveRecord $model, $field, ActiveRecord $relatedModel)
    {
        if ($model->$field == null)
        {
            return "";
        }
        if ($this->isInstanceOf($relatedModel, "BaseImage"))
        {
            return "<div class='simple-table-image'><img src='{$model->$field->getUrl()}' /></div>";
        }


        $title = $this->getTitleFieldForRelatedModel($relatedModel);
        $result =  $model->$field->$title;

        return $result;
    }

    /**
     * Пытается получить название модели отображаемое пользователю из название модели
     * Подходит для большинства моделей, но иногда может выглядеть некорректно.
     * 
     * @param ActiveRecord $model Модель
     * @return String
     */
    public function getModelTitle(ActiveRecord $model)
    {
        $tableName = get_class($model);
        $title = ucfirst($tableName);
        return $title;
    }

    /**
     * Получение лейбла для инпута модели
     * 
     * @param ActiveRecord $model Модель
     * @param String $field Имя поля модели
     * @return String HTML <label>
     */
    public function getFieldInputLabel(ActiveRecord $model, $field)
    {
        $helper = $this->getFormHelper();
        $html = $helper->labelEx($model, $field, array("class" => "col-sm-3 control-label"));
        return $html;
    }

    /**
     * Получение html ошибки для поля модели
     * 
     * @param ActiveRecord $model Модель
     * @param String $field Поле модели
     * @return String html ошибки для инпута
     */
    public function getFieldInputErrorHtml(ActiveRecord $model, $field)
    {
        $helper = $this->getFormHelper();
        $isForeign = ActiveRecordHelper::getForeignFieldForForeignKey($model, $field) != null;
        if ($isForeign != null)
        {
            $relationSpec = ActiveRecordHelper::getRelationByFieldName($model, $field);
            $class = $relationSpec[1];
            $obj = new $class;
            $fileClass = "BaseFile";
            if (is_a($obj, $fileClass) || is_subclass_of($obj, $fileClass))
            {
                $html = $obj::error();
                return $html;
            }
        }
        $html = $helper->error($model, $field);
        return $html;
    }

    /**
     * Получение HTML инпута для поля модели
     * 
     * @param ActiveRecord $model Модель
     * @param String $field Поле модели
     * @return String html для инпута
     */
    public function getFieldInputHtml(ActiveRecord $model, $field)
    {
        $helper = $this->getFormHelper();
        $isForeign = ActiveRecordHelper::getForeignFieldForForeignKey($model, $field) != null;
        if ($isForeign != null)
        {
            $result = $this->getForeignFieldInputHtml($model, $field);
            return $result;
        }
        if ($field == "text" || $field == "description")
        {
            return $helper->textArea($model, $field, array("class" => "form-control", "style" => "height: 150px"));
        }
        $html = $helper->textField($model, $field, array("class" => "form-control"));
        return $html;
    }

    /**
     * Получение HTML инпута для поля модели, которое является внешним ключом
     * 
     * @param ActiveRecord $model Модель
     * @param String $field Поле модели
     * @return String html для инпута
     */
    public function getForeignFieldInputHtml(ActiveRecord $model, $field)
    {
        $relationSpec = ActiveRecordHelper::getRelationByFieldName($model, $field);
        $class = $relationSpec[1];
        $obj = new $class;

        if ($this->isInstanceOf($obj, "BaseFile"))
        {
            $html = $obj::fileField();
            return $html;
        }

        $titleField = $this->getTitleFieldForRelatedModel($obj);
        $models = $obj->findAll();
        $isRequired = ActiveRecordHelper::hasValidator($model, $field, "required");
        $values = ActiveRecordHelper::modelsToDropDownValues($models, $titleField, null, $isRequired ? null : "None");
        $html = $this->getFormHelper()->dropDownList($model, $field, $values, array("class" => "form-control"));
        return $html;
    }

    /**
     * Метод, который вызывается после назначение стандартных полей модели
     * 
     * @param ActiveRecord $model Модель
     */
    public function afterParamsSet(ActiveRecord $model)
    {
        $relations = $model->relations();
        foreach ($relations as $relatedFieldName => $spec)
        {
            $type = $spec[0];
            if ($type != ActiveRecord::BELONGS_TO)
            {
                continue;
            }

            $class = $spec[1];
            $relatedModel = new $class();
            $fileClass = "BaseFile";
            if (is_a($relatedModel, $fileClass) || is_subclass_of($relatedModel, $fileClass))
            {
                $file = $relatedModel::createAndSave();
                //bug::show($file);
                if ($file != null)
                {
                    $model->setRelated($file, $relatedFieldName);
                }
            }
        }
    }

    /**
     * Проверка является ли модель наследником класса
     * 
     * @param Object $obj Объект
     * @param String $class Название класса
     * @return Bool True, если объект является представителем класса
     */
    public function isInstanceOf($obj, $class)
    {
        $isClass = is_a($obj, $class);
        $isSubSclass = is_subclass_of($obj, $class);
        $result = $isClass || $isSubSclass;
        return $result;
    }

    /**
     * Создает экшены для модели
     * 
     * @param ActiveRecord $model Модель
     * @return \ActionSpec[] Массив объектов действий
     */
    public function getActionsForModel(ActiveRecord $model)
    {
        $actions = [];
        $actions[] = $this->generateUpdateAction($model);
        $actions[] = $this->generateDeleteAction($model);
        if ($model instanceof \Hs\Admin\Simple\ICanBeDisabled)
        {
            $actions[] = $this->generateEnableOrDisableAction($model);
        }
        return $actions;
    }

    /**
     * Получение списка кнопок для списка моделей (отображаются над таблицей)
     * 
     * @return \ActionSpec[] Массив объектов действий
     */
    public function getActionsForList()
    {
        $actions = [];
        $actions["create"] = new ActionSpec("Add", $this->createAbsoluteUrl("create", []), "btn btn-green btn-sm btn-icon icon-left", "entypo-plus");
        return $actions;
    }

    /**
     * Получение фильтра моделей для списка
     * 
     * @return \ModelFilter
     */
    public function getModelFilter()
    {
        if($this->filter)
        {
            return $this->filter;
        }
        $this->filter = new ModelFilter($this->getModel(), $this);
        $this->filter->setModelsPerPage(20);
        return $this->filter;
    }

    /**
     * Создает кнопки для экшенов для управления порядком отображения модели
     * 
     * @param ActiveRecord $model Модель
     * @return \ActionSpec[] Массив из двух экшенов изменения порядка моделей (вверх и вниз)
     */
    public function generateOrderingActions(ActiveRecord $model)
    {
        $manager = new OrderingManager($model);
        $class = "btn btn-blue";
        $up = new ActionSpec("", $manager->createUpUrl($this, $model), $class, "entypo-up");
        $down = new ActionSpec("", $manager->createDownUrl($this, $model), $class, "entypo-down");
        $result = [$up, $down];
        return $result;
    }

    /**
     * Cоздает кнопку для экшена редактирования модели
     * 
     * @param ActiveRecord $model Модель
     * @return \ActionSpec Описание кнопки обновления модели
     */
    public function generateUpdateAction(ActiveRecord $model)
    {
        $action = new ActionSpec("Edit", $this->createAbsoluteUrl("update", ["id" => $model->getPk()]), "btn btn-green btn-sm btn-icon icon-left", "entypo-pencil");
        return $action;
    }

    /**
     * Cоздает кнопку для экшена эспорта списка моделей
     *
     * @param String[] $params Параметры
     * @return \ActionSpec Описание кнопки обновления модели
     */
    public function generateExportAction($params = [])
    {
        $action = new ActionSpec("Export", $this->createAbsoluteUrl("export",$params), "btn btn-green btn-sm btn-icon icon-left", "entypo-download");
        return $action;
    }

    protected function generateBackAction($url)
    {
        $actionSpec = new ActionSpec("Back",$url,$this->getDefaultButtonClasses()." btn-blue","entypo-left");
        return $actionSpec;
    }

    /**
     * Создает кнопку для удаления модели
     * 
     * @param ActiveRecord $model Модель
     * @return \ActionSpec Описание кнопки удаления модели
     */
    public function generateDeleteAction(ActiveRecord $model)
    {
        $action = new ActionSpec("Delete", $this->createAbsoluteUrl("delete", ["id" => $model->getPk()]), "btn btn-danger btn-sm btn-icon icon-left", "entypo-cancel");
        return $action;
    }

    /**
     * Получение ширины колонки в таблице списка моделей для поля
     * 
     * @param String $field
     * @return string Значение для css свойства width, например 50px или 30%
     */
    public function getColumnWidthFor($field)
    {
        switch ($field)
        {
            case "id" : return "20px";
                break;
            case "imageId" : return "220px";
                break;
            case "_actions" : return "250px";
                break;
        }
    }

    /**
     * Возвращает список заголовков колонок для экспорта
     * 
     * @return String[]
     */
    public function getExportTitles()
    {
        return $this->getListFields();
    }

    /**
     * Возвращает список полей для экспорта
     * 
     * @return String[]
     */
    public function getExportFields()
    {
        return $this->getListFields();
    }

    /**
     * Возвращает Url на который будет направлен пользователь после обновления модели
     * 
     * @param ActiveRecord $model Модель
     * @return Sting Url
     */
    public function getRedirectUrlForUpdate(ActiveRecord $model)
    {
        return $this->getRedirectUrlForCreate($model);
    }

    /**
     * Возвращает Url на который будет направлен пользователь после создания модели
     * 
     * @param ActiveRecord $model Модель
     * @return Sting Url
     */
    public function getRedirectUrlForCreate(ActiveRecord $model)
    {
        $url = $this->createUrl("index");
        return $url;
    }

    protected function getDefaultButtonClasses()
    {
        return "btn btn-sm btn-icon icon-left";
    }

    /**
     * Отключает Создает кнопки включения и отключения модели. Необходимо, чтобы модель имела интерфейс
     * 
     * @param Hs\Admin\Simple\ICanBeDisabled $model Модель
     */
    public function generateEnableOrDisableAction(Hs\Admin\Simple\ICanBeDisabled $model)
    {
        if ($model->isEnabled())
        {
            $disable =  new ActionSpec("Disable", $this->createAbsoluteUrl("disable", ["id" => $model->getPk()]), "btn btn-red btn-sm btn-icon icon-left", "entypo-pencil");
            return $disable;
        }
        $enable  = new ActionSpec("Enable", $this->createAbsoluteUrl("enable", ["id" => $model->getPk()]), "btn btn-green btn-sm btn-icon icon-left", "entypo-pencil");
        return $enable;
    }
    
    /**
     * Включает модель. Модель должна иметь интерфейс.
     * 
     * @see Hs\Admin\Simple\ICanBeDisabled
     * @param Integer $id Идентификатор модели
     */
    public function actionEnable($id)
    {
        $model = $this->getModel()->findByPk($id);
        $this->showErrorOrSuccessMessage($model->enable(),"Can't enable {$this->getModelTitle($this->getModel())}: {$model->getFirstError()}");
        $this->redirectToRoute("index");
    }
    
    /**
     * Отключает модель. Модель должна иметь интерфейс.
     * 
     * @see Hs\Admin\Simple\ICanBeDisabled
     * @param Integer $id Идентификатор модели
     */
    public function actionDisable($id)
    {
        $model = $this->getModel()->findByPk($id);
        $this->showErrorOrSuccessMessage($model->disable(),"Can't disable {$this->getModelTitle($this->getModel())}: {$model->getFirstError()}");
      
        $this->redirectToRoute("index");
    }

    /**
     * Есть ли колонка actions в списке моделей
     */
    public function hasActionsColumn()
    {
        return true;
    }

    public function showNumbersInList()
    {
        return true;
    }

    public function getNumberSign()
    {
        return "#";
    }

    public function getCurrentNumber()
    {
        
        if($this->areNumbersNegative())
        {
            return --$this->number;
        }
        return ++$this->number;
    }
    
    public function areNumbersNegative()
    {
        return true;
    }

    public function setNumber(ModelFilter $filter)
    {
        $offset = ($filter->getActivePage()-1)*$filter->getModelsPerPage();
        $this->number = $offset;
        if($this->areNumbersNegative())
        {
            $this->number = $filter->getModelCount()- $offset+1;
        }
    }

    public function getAlternativeFieldTitles()
    {
        return [];
    }

    public function addSubview($name,$content)
    {
        $this->subviews[$name] = $content;
    }
    
    /**
     * 
     * @param type $name
     */
    public function getSubview($name)
    {
        if(isset($this->subviews[$name]))
        {
            return $this->subviews[$name];
        }
        return  "";
    }

    protected function getTitleFieldForRelatedModel($obj)
    {
        $arr = ["title","name"];
        foreach ($arr as $el)
        {
            if($obj->hasAttribute($el))
            {
                return $el;
            }
        }
        return "id";
    }
}
