<?php

/**
 * Коннектор файл менеджера
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ConnectorAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {
//Эль файндер подключается как расширение для Yii, я не стал заморачиваться просто вызвал тот экшен
//        function actions()
//        {
//            return array(
//                'connector' => array(
//                    'class' => 'ext.elFinder.ElFinderConnectorAction',
//                    'settings' => array(
//                        'root' => Yii::getPathOfAlias('webroot') . '/upload/',
//                        'URL' => Yii::app()->baseUrl . '/upload/',
//                        'rootAlias' => 'Home',
//                        'mimeDetect' => 'none'
//                    )
//                ),
//            );
//        }

        $settings = array(
            'root' => Yii::getPathOfAlias('root') . '/upload/',
            'URL' => Yii::app()->baseUrl . '/upload/',
            'rootAlias' => 'Home',
            'mimeDetect' => 'none',
//                        'imgLib' => "gd",
//                        'debug' => true,
        );
        Yii::import("root.lib-Yii.extensions.elfinder.ElFinderConnectorAction");
        $action = new ElFinderConnectorAction($this->controller, "connector");
        $action->settings = $settings;
        return $action->run();
    }

}
