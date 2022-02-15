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
        //$homepage = file_get_contents('http://127.0.0.1:3000/');
        //var_dump($homepage);
        //phpinfo();
        $this->setLayout('main');
	     $this->render('home');
    }

    public function actionWhy()
    {

        //$homepage = file_get_contents('http://127.0.0.1:3000/');
        //var_dump($homepage);
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
