<?php

/**
 * Обновление пароля
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
        $user = $this->getController()->getCurrentUser();
        $model = new PasswordChangeForm($user);

        $formData = $this->getController()->getRequest()->getParam(get_class($model), null);
        if ($formData)
        {
            $model->setAttributes($formData);
            if ($model->save())
            {
                $this->getController()->showSuccessMessage("Password successfuly changed");
                $model = new PasswordChangeForm($user);
                $this->getController()->redirectToRoute("index");
            } else
            {
                $this->getController()->showErrorMessage($model->getFirstError());
            }
        }

        $this->getController()->addViewData($model, "model");
        $this->getController()->render($this->view);
    }

}
