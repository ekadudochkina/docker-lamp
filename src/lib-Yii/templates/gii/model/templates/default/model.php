<?php
use Hs\Helpers\ClassHelper;
//Тут много переменных объявлено неявно , но оказалось, что почти все мало полезны
//Оригинальный код можно поглядеть в папке фреймворка gii.

/* @var String $tableName Имя таблицы */
/* @var String $modelClass Имя модели */
/* @var ModelCode $this Кодогенератор */

$commentsAsLabels =  (bool)$this->commentsAsLabels;

$baseClass = "ActiveRecord";
$modelComment = CodeGenHelper::createModelComment($tableName);

$labelsFuncComment = "Возвращает информацию о том, как называются поля на человеческом языке.";
$labelsFuncTags = array(CodeGenHelper::getAutogeneratedTag(), "@return String[] Массив лейблов для полей (name=>label)");

$relationsFuncComment = "Возвращает массив связей моделей.\n<b>Внимание: связи BELONGS_TO являются внешними ключами.</b> Для них можно указать поведение при удалений родительской сущности.";
$relationsFuncTags = array(CodeGenHelper::getAutogeneratedTag(),"@return Array[] Массив связей");

$rulesFuncComment = " Возвращает правила валидации.\n<b>Внимание: для полей у которых в БД тип VARCHAR необходимо создать валидатор \"length\".</b>";
$rulesFuncTags = array(CodeGenHelper::getAutogeneratedTag(),"@return Array[] Массив правил валидации");

//Создаем код правил
$rules = CodeGenHelper::createRules($tableName);
$rulesCode = CodeGenHelper::rulesToCode($rules);

//Cоздаем код связей
$relations = CodeGenHelper::createRelations($tableName);
$relationsCode = CodeGenHelper::relationsToCode($relations);
$modelTags = CodeGenHelper::relationsToTags($relations,$tableName);
$modelTags = CodeGenHelper::addAutogeneratedTag($modelTags);

//Создаем код меток
$labels = CodeGenHelper::createLabels($tableName,$commentsAsLabels);
$labelsCode = $labels;

//Создаем код полей
$modelFields = CodeGenHelper::createFields($tableName);
$modelMethods = array();
$customCode = "";
$constructor = null;

//Флаг пропуска метода tableName()
$skipTableNameMethod = false;

//Если класс уже существует мы можем узнать больше проанализировав
$instance = ActiveRecordHelper::createModelInstance($modelClass);
if($instance)
{
    //Объединяем правила с существующими
    $mergedRules = ActiveRecordHelper::mergeRules($rules,$instance->rules());
    $order = ActiveRecordHelper::getValidators($instance);
    $reorderedMergedRules = CodeGenHelper::reorderRules($mergedRules,$order);
    $rulesCode = CodeGenHelper::rulesToCode($reorderedMergedRules);
    
    if(!empty($instance->rules()))
        $rulesFuncTags = CodeGenHelper::addAutogeneratedTag($rulesFuncTags,true);
    
    //Объединяем связи с существующими
    $relations = ActiveRecordHelper::mergeRelations($instance->relations(), $relations);
    $relationsCode = CodeGenHelper::relationsToCode($relations);
    $modelTags = CodeGenHelper::relationsToTags($relations, $tableName);
    $modelTags = CodeGenHelper::addAutogeneratedTag($modelTags,true);
    if(!empty($instance->relations()))
        $relationsFuncTags =  CodeGenHelper::addAutogeneratedTag($relationsFuncTags,true);
    
    //Объединяем поля с существующими
    $modelFields = CodeGenHelper::excludeBaseClassFields($instance,$modelFields);
    $customFields = ActiveRecordHelper::getCustomFieldsCode($modelClass);
    $modelFields = CodeGenHelper::mergeFields($customFields,$modelFields);
    
    //Объдинияем лейблы с существующими
    $labels = ActiveRecordHelper::mergeLabels($instance->attributeLabels(),$labels);
    if(!empty($instance->attributeLabels()))
        $labelsFuncTags =  CodeGenHelper::addAutogeneratedTag($labelsFuncTags,true);
    
    //Получаем код методов, которые уже объявлены
    $modelMethods = ActiveRecordHelper::getCustomMethodsCode($modelClass);
    if(!empty($modelMethods)){
        unset($modelMethods["relations"]);
        unset($modelMethods["rules"]);
        unset($modelMethods["model"]);
        unset($modelMethods['attributeLabels']);
        
        if(isset($modelMethods['tableName']))
            $skipTableNameMethod = true;
        if(isset($modelMethods['__construct']))
        {
            $constructor = $modelMethods['__construct'];
            unset($modelMethods['__construct']);
        }
    }
    //Получаем оставшийся код, помимо методов и полей
    $customCode = ActiveRecordHelper::getCustomCode($modelClass);
    
    //Ищем базовый класс
    $baseClass = get_parent_class($instance);
   
    //получаем коммент для таблицы
    $existingComment = ClassHelper::getDescriptionForClass(get_class($instance)); 
    $modelComment = $existingComment ? $existingComment : $modelComment;
    
    //получаем теги
    $existingTags = ClassHelper::getTagsForClass(get_class($instance));
    $modelTags  = ActiveRecordHelper::mergeTags($existingTags,$modelTags);    
    
    //Добавляем тег о слиянии для комментария модели
    if($existingComment || $existingTags)
        $modelTags = CodeGenHelper::addAutogeneratedTag($modelTags,true);
}
?>
<?=CodeGenHelper::endline("<?php")?>

<?=CodeGenHelper::createComment(0,$modelComment,$modelTags)?>
class <?=$modelClass?> extends <?=CodeGenHelper::endline($baseClass)?>
{
<?=CodeGenHelper::wrapCodeEntities(4,$modelFields)?>

<?=CodeGenHelper::wrapLines(4,"",explode("\n",$customCode))?>
<?php if($constructor != null): ?>

<?=CodeGenHelper::wrapCodeEntity(4,$constructor); ?>
<?php endif;?>
<?=CodeGenHelper::createComment(4,$rulesFuncComment,$rulesFuncTags)?>
    public function rules()
    {
        $arr = parent::rules();
<?=CodeGenHelper::wrapLines(8,'$arr[] = ', $rulesCode,";")?>
        return $arr;
    }
    
<?=CodeGenHelper::createComment(4,$relationsFuncComment,$relationsFuncTags)?>
    public function relations()
    {
        $arr = parent::relations();
<?=CodeGenHelper::magicWrap(8,'$arr["',  array_keys($relationsCode),'"] = ',  array_values($relationsCode),";")?>
        return $arr;
    }
    
<?=CodeGenHelper::createComment(4,$labelsFuncComment,$labelsFuncTags)?>
    public function attributeLabels()
    {
        $arr = parent::attributeLabels();
<?=CodeGenHelper::magicWrap(8,'$arr["',array_keys($labels),'"] = "',  array_values($labels),'";')?>
        return $arr;
    }
    
    /**
     * Возвращает новую модель данного класса. 
     * Этот метод необязателен, но улучшает работу подсказок.
     * 
     * <?=CodeGenHelper::getAutogeneratedTag()."\n"?>
     * @param String $className Имя класса модели
     * @return <?php echo $modelClass; ?> пустой объект модели
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
<?php if(!$skipTableNameMethod):?>
    /**
     * Получение имени таблицы в базе данных
     *
     * <?=CodeGenHelper::getAutogeneratedTag()."\n"?>
     * @return String Название таблицы
     */
    public function tableName()
    {
        return '<?php echo $tableName; ?>';
    }  

<?php endif;?>
<?=CodeGenHelper::wrapCodeEntities(4,$modelMethods)?> 
}