<?php

/**
 * Данный менеджер должен размещать новую версию вебсайта в папке runtime.
 * 
 * За версию в данном классе принимается текущий коммит
 * 
 * Другие компоненты могут обращаться к нему по интерфейсу, получая текущую версию вебсайта и проверяя есть ли новая версия.
 * После того, как новая версия была обработана, необходимо вызвать метод saveVersion(), чтобы предыдущая и текущая версия стали идентичными.
 * 
 * <b>
 * Никогда не используйте данный объект одновременно несколькими классами, иначе могут возникнуть сбои в обработки версий.
 * Один класс - один VersionManager.
 * </b>
 * 
 * @package Hs
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class GitBasedVersionManager
{

    /**
     * Путь к дирректории Git
     * @var String
     */
    protected $pathToGit;

    /**
     * Путь к файлу текущей версии
     * @var String
     */
    protected $pathToCurrentVersion;

    /**
     * Кеш текущей версии
     * @var String
     */
    protected $_currentVersion = null;

    /**
     * Имя файла для хранения версии
     * 
     * @param String $filename
     */
    public function __construct($filename)
    {
        $path = Yii::getPathOfAlias("application.runtime");
        $versionPath = $path . "/$filename";

        $this->pathToCurrentVersion = $versionPath;
        $this->pathToGit = Yii::getPathOfAlias('webroot') . '/.git';
        if (!file_exists($this->pathToCurrentVersion))
        {
            $this->saveVersion();
        }
    }

    /**
     * Проверяет обновилась ли версия проекта
     * 
     * @return Bool True, если появилась новая версия
     */
    public function hasNewVesion()
    {
        $last = $this->getLastVersion();
        $new = $this->getCurrentVersion();

        $result = $last === $new;
        return $result;
    }

    /**
     * Получение кода предыдущей ревизии мигратора
     * 
     * @return String Хэш код ревизии
     */
    public function getLastVersion()
    {
        $content = file_get_contents($this->pathToCurrentVersion);
        $parts = explode("|", $content);
        $revision = $parts[0];
        return $revision;
    }

    /**
     * Получение кода текущей версии сайта
     * 
     * @return String 
     */
    public function getCurrentVersion()
    {
        if ($this->_currentVersion != null)
        {
            return $this->_currentVersion;
        }

        $pathToGit = $this->pathToGit;
        $content = file_get_contents($pathToGit . '/HEAD');
        $content = trim($content);
        $path_to_branch = explode(" ", $content)[1];
        $combinedPath = $pathToGit . "/" . $path_to_branch;
        $file = fopen($combinedPath, "r");
        $revision_hash = file_get_contents($combinedPath);
        $revision_hash = trim($revision_hash);

        $this->_currentVersion = $revision_hash;
        return $revision_hash;
    }

    /**
     * Сохранение текущей версии
     */
    public function saveVersion()
    {
        $hash = $this->getCurrentVersion();
        $time = DateTimeHelper::timestampToMysqlDateTime();
        $content = join("|", array($hash, $time));
        file_put_contents($this->pathToCurrentVersion, $content);
    }

}
