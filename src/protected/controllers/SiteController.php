<?php

/**
 * Главная страница сайта
 */
class SiteController extends Controller
{

    /**
     * Главная страница сайта
     */


    public function actionIndex()
    {

        $this->setLayout('main');
	     $this->render('home');
    }

    public function actionWhy()
    {

        $this->setLayout('main');
        $this->render('why');
    }

    public function actionSolutions()
    {
        $this->setLayout('main');
        $this->render('solutions');
    }




    /**
     * Различная обработка ошибок для обычных и асинхронных запросов
     */
    public function actionError()
    {
        $this->addCssFile("style.css");     
        
	$error = Yii::app()->errorHandler->error;
	if ($error)
	{
	    if (Yii::app()->request->isAjaxRequest)
		echo $error['message'];
	    else
		$this->render('error', $error);
	}
    }

}
