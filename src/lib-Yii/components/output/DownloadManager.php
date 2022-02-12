<?php
namespace Hs\Output;

/**
 * Помошник для скачивания файлов
 *
 * @package Hs\Output
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class DownloadManager
{

    /**
     * Запуск скачивания текстового файла. Отдает файл пользователю
     * 
     * @return null
     */
    public function startDownload($content, $filename, $contentType = "text")
    {
        \Debug::disableWebLog();
        header("Content-type: text");
        header("Cache-Control: no-store, no-cache");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $content;
        \Yii::app()->end();
    }

}
