 <?php

/**
 * Контроллер админки по умолчанию. 
 * По сути он нужен только для того, чтобы роут /admin/login существовал
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class AdminController extends BaseAdminController
{
    /**
     * Действие по-умолчанию
     */
    public function actionIndex()
    {
        $this->forward("adminUser/index");
    }
}
