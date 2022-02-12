<?php
/**
 * Базовый контроллер проекта. Содержит уникальные для проекта функции.
 */
abstract class Controller extends BaseController
{
   
    protected $loginForm;


    public function beforeAction($action) {
        bug::useStderr();
        bug::export($_GET,$_POST);
        bug::useStdout();
        
        //$this->userProvider = new EmailUserProvider(new User(), "site/index");
        
        //основные библиотеки js
        //$this->addJavascriptFile("jquery.min.js", null, "jquery/dist");
        //$this->addJavascriptFile("hs.application.js");
        //$this->addJavascriptFile("jquery.js");


        //подключаем бутстрап
        //$this->addCssFile("/bootstrap/bootstrap.css");
        //$this->addCssFile("template/core.css");
        //$this->addCssFile("template/components.css");
       // $this->addJavascriptFile("/bootstrap/bootstrap.js");
        
        //библиотека иконок
        //$this->addCSSFile("/font-awesome/css/font-awesome.css");


        //$this->addJavascriptFile("main.js");

        //$this->addCssFile("main.css");

        $ret = parent::beforeAction($action);


        return $ret;
    }

}
