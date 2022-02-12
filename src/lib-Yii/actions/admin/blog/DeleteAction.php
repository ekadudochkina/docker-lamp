<?php

/**
 * Удаляет статью
 *
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 */
class DeleteAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {

        $id = $this->controller->getRequest()->getParam("id");
        /* @var $model BlogArticle */
        $model = BlogArticle::model()->findByPk($id);

        if ($this->controller->showActionMessage($model->delete(), $model, "Object succesfuly delete"))
            $this->controller->redirectToRoute("index");
    }

}
