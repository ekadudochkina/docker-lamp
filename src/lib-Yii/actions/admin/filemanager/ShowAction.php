<?php

/**
 * Отображает файл менеджер
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ShowAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {

        Yii::app()->getClientScript()->defaultScriptPosition = CClientScript::POS_END;

        $extPath = Yii::getPathOfAlias("root.lib-Yii.extensions");
        $path = $extPath . '/elfinder/assets/js/elfinder.min.js';
        $url = Yii::app()->getAssetManager()->publish($path);
        Yii::app()->clientScript->registerScriptFile($url, CClientScript::POS_END);
        $this->render();
    }

}
