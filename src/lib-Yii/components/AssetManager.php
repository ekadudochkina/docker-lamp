<?php

/**
 * Прослойка. Лучше версионирует ассеты в дебаге.
 *
 * @package Hs\Yii
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class AssetManager extends CAssetManager
{

    /**
     * Менеджер версий
     * @var GitBasedVersionManager 
     */
    protected $versionManager = null;

    /**
     * Initializes the application component.
     * This method is required by {@link IApplicationComponent} and is invoked by application.
     * If you override this method, make sure to call the parent implementation
     * so that the application component can be marked as initialized.
     */
    public function init()
    {
        $this->versionManager = new GitBasedVersionManager("assets");
        return parent::init();
    }

    /**
     * Generate a CRC32 hash for the directory path. Collisions are higher
     * than MD5 but generates a much smaller hash string.
     * @param string $path string to be hashed.
     * @return string hashed string.
     */
    protected function hash($path)
    {
        //Замешивает текущий коммит в хэш ассетов
        //$projectVersion = $this->versionManager->getCurrentVersion();
        //return sprintf('%x', crc32($path . Yii::getVersion() . $projectVersion));

        return "";
    }

}
