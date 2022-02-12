<?php

/**
 * Базовый Контроллер для Dashboard
 *
 */
abstract class AdminDashboardController extends BaseDashboardController {

    public function beforeAction($action) {
        $ret = parent::beforeAction($action);
        
        $this->checkAdmin();
        
        return $ret;
    }

    public function checkAdmin() {
        $curentUser = $this->getCurrentUser();
        if ($curentUser !== null) {
            if ($curentUser->isAdmin == false) {
                $this->redirectToRoute("dashboard/index");
            }
        }
    }

}
