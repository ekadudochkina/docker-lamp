<?php
namespace Hs\Controllers;

/**
 * Контроллер для тестирование имейлов. Предназначен для разработки.
 * @package Hs\Controllers
 */
abstract class EmailTestController extends \BaseController {

    public function beforeAction($action) {
        $this->setLayout(null);
        if (\EnvHelper::isProduction()) {
            return false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Отображает страницу со списком имейлов
     * Позволяет отобразить имейл или отослать его на почту
     */
    public function actionIndex() {
        $actions = $this->getPossibleActions();
        $this->addViewData($actions, "actions");
        $this->render("root.lib-Yii.views.dev.email.list");
    }

    /**
     * Отправляет имейл на почту
     * 
     * @param String $id Название вьюшки имейла
     */
    public function actionSend($id) {
        if ($id) {
            $result = $this->getEmailManager()->sendEmail("test@home-studio.pro","Test '$id' email",$id,$this->getViewData($id));
        }
        $this->redirectToRoute("index");
    }
    
    /**
     * Отображает имейл на экран
     * 
     * @param String $id Название вьюшки имейла
     */
    public function actionView($id) {
        \bug::disableWebLog();
        echo $this->getEmailManager()->getEmailBody($id,$this->getViewData($id));
        die();
    }

    
    /**
     * Получение возможных имейлов для отображения
     * 
     * @return String[] Список названий вьюшек
     */
    protected function getPossibleActions()
    {
        $exclude = ["layout","index"];
        $alias = $this->getEmailManager()->getViewPathAlias();
        $path = \Yii::getPathOfAlias($alias);
        $files = \FileHelper::getFilesInDirectory($path);
        $result = [];
        foreach($files as $file)
        {
            $view = str_replace(".php","",$file);
            if(in_array($view,$exclude))
            {
                continue;
            }
            $result[] = $view;
        }
        return $result;
    }

    /**
     * Получение объекта Имеел менеджера
     * 
     * @return EmailMangaer
     */
    protected abstract function getEmailManager();
    
    /**
     * Получение данных для вьюшек
     * 
     * @return String[];
     */
    protected abstract function getViewData($id);
}
