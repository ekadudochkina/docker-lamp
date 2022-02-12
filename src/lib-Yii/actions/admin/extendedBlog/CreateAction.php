<?php

/**
 * Создает статью в блоге
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class CreateAction extends \Hs\Actions\BlogAction
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
            if ($this->controller->showActionMessage($model->save(), $model, $this->controller->t("Object succesfuly created", "lib")))
                ;
            $this->controller->redirectToRoute("index");
        }

        $this->addViewData($model, "model");
        $this->render();
    }

}
