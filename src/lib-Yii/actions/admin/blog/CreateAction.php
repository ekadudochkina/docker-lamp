<?php

/**
 * Создает статью в блоге
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class CreateAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {

        $model = new BlogArticle();

        if ($this->controller->setParamsToModels($model))
        {
            if (property_exists(get_class($model), "mainImage"))
            {
                $image = Image::createAndSaveInt();
                if ($image !== null)
                {
                    $model->mainImage = $image;
                }
            }
            if ($this->controller->showActionMessage($model->save(), $model, "Object succesfuly created"))
                $this->controller->redirectToRoute("index");
        }

        $this->addViewData($model, "model");
        $this->render();
    }

}
