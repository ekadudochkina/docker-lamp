<?php

/**
 * Редактирование статьи в блоге
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class UpdateAction extends ViewAction
{
    /**
     * Запуск экшена
     */
    public function run()
    {
        $id = $this->controller->getRequest()->getParam("id");
       /* @var $model BlogArticle */
        $model = BlogArticle::model()->findByPk($id);

        if($this->controller->setParamsToModels($model)){
             if (property_exists(get_class($model), "mainImage")) {
                $image = Image::createAndSaveInt();
                if ($image !== null) {
                    $model->mainImage = $image;
                }
            }
          if($this->controller->showActionMessage($model->save(), $model,"Object succesfuly updated"))
             $this->controller->redirectToRoute("index");
        }

        $this->addViewData($model,"model");
        $this->render('create');
    }
}
