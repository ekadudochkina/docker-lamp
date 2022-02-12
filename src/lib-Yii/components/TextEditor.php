<?php

/**
 * Текстовый редактор home-studio. Является виджетом.
 *
 * @see ExtendedCkEditor
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs
 */
class TextEditor extends CWidget
{

    public $uploadRoute;
    public $browseRoute;
    public $model;
    public $attribute;
    public $language;
    public $height;
    public $editorTemplate;

    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run()
    {
        Yii::import("lib-Yii.extensions.ckeditor.ECKEditor");
        //debug::drop(Yii::getPathOfAlias("lib-Yii"));
        $this->widget('lib-Yii.components.ExtendedCkEditor', array(
            'uploadRoute' => $this->uploadRoute,
            'browseRoute' => $this->browseRoute,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'language' => $this->language,
            'editorTemplate' => $this->editorTemplate,
            'height' => $this->height,
        ));
    }

    /**
     * Функция для обработки загрузки файлов в файловый менеджер
     * Вызывается на стороне контроллера
     */
    public function upload()
    {
        $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->baseUrl . "/upload/"; // URL for the uploads folder
        $_SESSION['KCFINDER']['uploadDir'] = $_SERVER['DOCUMENT_ROOT'] . "/upload/"; // path to the uploads folder

        $path = $this->getCKedtorPath();
        $dir = FileHelper::joinPaths($path, "kcfinder");

        //Добавляем загрузчик классов для аплодера
        include($dir . "/core/autoload.php");
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        spl_autoload_register("__autoload");

        //Меняем директорию, чтобы работали инклуды
        chdir($dir);
        //Необходимо закрыть (но не разрушить) текущую сессию, 
        //потому что uploader вызывает session_start() опысным образом
        session_write_close();
        $uploader = new uploader();
        $uploader->upload();

        spl_autoload_register(array('YiiBase', 'autoload'));
    }

    /**
     * Функция для отображения файлового менеджера
     * Вызывается на стороне вьюшки
     * @return String
     */
    public function browse()
    {

        $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->baseUrl . "/upload/"; // URL for the uploads folder
        $_SESSION['KCFINDER']['uploadDir'] = $_SERVER['DOCUMENT_ROOT'] . "/upload/"; // path to the uploads folder 

        $path = $this->getCKedtorPath();
        $dir = FileHelper::joinPaths($path, "kcfinder");


        /* @var $manager CAssetManager */
        $manager = Yii::app()->getAssetManager();
        if (YII_DEBUG)
        {
            $manager->linkAssets = false;
        }
        $extPath = $manager->publish($path . "/kcfinder", false, -1, YII_DEBUG);


        //Добавляем загрузчик классов для аплодера
        include($dir . "/core/autoload.php");
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        spl_autoload_register("__autoload");

        //Меняем директорию, чтобы работали инклуды
        chdir($dir);
        //Необходимо закрыть (но не разрушить) текущую сессию, 
        //потому что uploader вызывает session_start() опысным образом
        session_write_close();
        $ob = ob_start();
        $uploader = new browser();
        $uploader->action();
        $output = ob_get_contents();
        ob_end_clean();

        //Заменяем неправильные урлы
        $output = str_replace('src="js', 'src="' . $extPath . "/js", $output);
        $output = str_replace('themes/', $extPath . "/themes/", $output);
        $output = str_replace('css.php', $extPath . "/css.php", $output);
        echo $output;

        $ctrl = Yii::app()->controller;
        $route = $ctrl->getId() . "/" . $ctrl->getAction()->getId();
        $browseUrl = $ctrl->createUrl($route);

        //Урл, который отображает является хардкодным, нужно менять
        if (!$ctrl->getRequest()->getParam("act"))
            echo "<script>
      browser.baseGetData = function(act) {
        var data = '{$browseUrl}?type=' + encodeURIComponent(this.type) + '&lng=' + this.lang;
        if (act)
            data += '&act=' + act;
        if (this.cms)
            data += '&cms=' + this.cms;
        return data;
    };     </script>";
        spl_autoload_register(array('YiiBase', 'autoload'));
        return;
        //print_r($buffer);
    }

    /**
     * Получение пути до CKEditor
     * 
     * @return String
     */
    public function getCKedtorPath()
    {
        EnvHelper::enableComposer();
        $path = Yii::getPathOfAlias("root.lib-Yii.extensions.ckeditor");
        return $path;
    }

}
