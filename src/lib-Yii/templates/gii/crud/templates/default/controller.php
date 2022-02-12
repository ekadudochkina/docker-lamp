<?php
/**
 * This is the template for generating a controller class file for CRUD feature.
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
/* @var $this CrudCode */
?>
<?php echo "<?php\n"; ?>

/**
* КОММЕНТАРИЙ
*/
class <?php echo $this->controllerClass; ?> extends <?php echo $this->baseControllerClass."\n"; ?>
{

    /**
     * Отображение списка моделей
     */
    public function actionIndex()
    {
        $models = <?=$this->modelClass?>::model()->findAll();
        
        $this->addViewData($models,"models");
        $this->render('index');
    }
    
    /**
     * Отображение модели.
     *
     * @param Integer $id Идентификатор модели
     */
    public function actionView($id)
    {
        /* @var $model <?=$this->modelClass;?> */
        $model = <?=$this->modelClass;?>::model()->findByPk($id);
        
        $this->addViewData($model,"model");
        $this->render('view');
    }

    /**
     * Создание новой модели.
     */
    public function actionCreate()
    {
        $model=new <?=$this->modelClass; ?>();

        if($this->setParamsToModels($model))
          if($this->showActionMessage($model->save(), $model,"Object succesfuly created"))
             $this->redirectToRoute("index");

        $this->addViewData($model,"model");
        $this->render('create');
    }

    /**
     * Изменение модели.
     *
     * @param Integer $id Идентификатор модели
     */
    public function actionUpdate($id)
    {
        /* @var $model <?=$this->modelClass;?> */
        $model = <?=$this->modelClass;?>::model()->findByPk($id);

        if($this->setParamsToModels($model))
          if($this->showActionMessage($model->save(), $model,"Object succesfuly updated"))
             $this->redirectToRoute("index");

        $this->addViewData($model,"model");
        $this->render('create');
    }

    /**
     * Удаляет модель и отправляет пользователя на главную страницу контроллера.
     *
     * @param Integer $id Идентификатор модели
     */
    public function actionDelete($id)
    {
        /* @var $model <?=$this->modelClass;?> */
        $model = <?=$this->modelClass;?>::model()->findByPk($id);
        
        if($model)
            $this->showActionError($model->delete(),$model,"Object successfuly deleted");
            
        $this->redirectToRoute("index");
    }

    /**
     * Выполняет асинхронную валидацию для клиента AJAX.
     
     * @param <?=$this->modelClass; ?> $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='<?php echo $this->class2id($this->modelClass); ?>-form')
        {
                echo CActiveForm::validate($model);
                Yii::app()->end();
        }
    }
}
