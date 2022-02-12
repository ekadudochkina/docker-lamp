<?php

Yii::import("root.lib-Yii.extensions.ckeditor.ECKEditor");

/**
 * Текстовый редактор, с защищенной загрузкой файлов.
 * Класс расширяет ECKEditor, позволяя преопределять пути к файлам, которые
 * позволяют загружать и просматривать изображения.
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs
 */
class ExtendedCkEditor extends ECKEditor
{

    /**
     * Путь к контролеру, который принимает загруженные файлы
     * 
     * @var String
     */
    public $uploadRoute;

    /**
     * Путь к контроллеру, отображающему файлы
     * @var Stgring
     */
    public $browseRoute;

    /**
     * Constructor.
     * @param CBaseController $owner owner/creator of this widget. It could be either a widget or a controller.
     */
    public function __construct($owner = null)
    {
        parent::__construct($owner);
        $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->baseUrl . "/upload/"; // URL for the uploads folder
        $_SESSION['KCFINDER']['uploadDir'] = $_SERVER['DOCUMENT_ROOT'] . "/upload/"; // path to the uploads folder
    }

    /**
     * Создает опсии для плагина
     * 
     * @return String[] Json массив опций
     */
    protected function makeOptions()
    {
        $json = parent::makeOptions();
        $options = CJavaScript::jsonDecode($json);
        $controller = Yii::app()->getController();

        //меняем путь для загрузки фалйов
        $url = $controller->createAbsoluteUrl($this->uploadRoute);
        $options['filebrowserUploadUrl'] = UrlHelper::createUrl($url, ['type' => 'files']);
        $options['filebrowserImageUploadUrl'] = UrlHelper::createUrl($url, ['type' => 'images']);
        $options['filebrowserFlashUploadUrl'] = UrlHelper::createUrl($url, ['type' => 'flash']);

        //Меняем пути для просмотра файлов
        $browseUrl = $controller->createAbsoluteUrl($this->browseRoute);
        $options['filebrowserBrowseUrl'] = UrlHelper::createUrl($browseUrl, ['type' => 'files']);
        $options['filebrowserImageBrowseUrl'] = UrlHelper::createUrl($browseUrl, ['type' => 'images']);
        $options['filebrowserFlashBrowseUrl'] = UrlHelper::createUrl($browseUrl, ['type' => 'flash']);

        //bug::drop($options);
        $newJson = CJavaScript::encode($options);
        return $newJson;
    }

    /**
     * Получение пути к плагину CKEditor
     * 
     * @return String
     */
    public function getCKedtorPath()
    {
        EnvHelper::enableComposer();
        $path = Yii::getPathOfAlias("lib-Yii.extensions.ckeditor");
        return $path;
    }

}
