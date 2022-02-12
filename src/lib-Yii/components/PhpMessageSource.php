<?php

/**
 * Прослойка для организации интернационализации.
 * Данная прослойка берет файлы с переводами из библиотеки, если в категория является категорией "lib".
 * 
 * @package Hs\Yii
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class PhpMessageSource extends CPhpMessageSource
{

    /**
     * Determines the message file name based on the given category and language.
     * If the category name contains a dot, it will be split into the module class name and the category name.
     * In this case, the message file will be assumed to be located within the 'messages' subdirectory of
     * the directory containing the module class file.
     * Otherwise, the message file is assumed to be under the {@link basePath}.
     * @param string $category category name
     * @param string $language language ID
     * @return string the message file path
     */
    protected function getMessageFile($category, $language)
    {
        //Ну тут все просто. Данный код позволяет хранить сообщения в либе.
        if ($category == "lib")
        {
            $filepath = Yii::getPathOfAlias("root.lib-Yii.messages.$language") . "/$category.php";
            //bug::Drop($filepath);
            $result = $filepath;
        } else
        {
            $result = parent::getMessageFile($category, $language);
        }
        //bug::show($result);
        return $result;
    }

}
