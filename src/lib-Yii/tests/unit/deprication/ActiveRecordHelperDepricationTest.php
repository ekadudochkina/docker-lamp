<?php

/**
 * Тест предназначен для остлеживания неверного использования класса ActiveRecordHelper
 *
 * @see ActiveRecordHelper
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ActiveRecordHelperDepricationTest extends \Hs\Test\NoDbTestCase
{

    protected $excludeFiles = [
        __FILE__
    ];

    /**
     * Проверяем, что в проекте нет устаревших методов ActiveRecordHelper
     */
    public function testDepricated()
    {
        $depricated = [
            "ActiveRecordHelper::getCommentForModel",
            "ActiveRecordHelper::getCommentForField",
            "ActiveRecordHelper::getCommentForMethod",
            "ActiveRecordHelper::getTagForField",
            "ActiveRecordHelper::getTagForMethod",
            "ActiveRecordHelper::getTagForModel",
            "ActiveRecordHelper::getTagsForModel",
            "ActiveRecordHelper::getDescriptionForModel",
            "ActiveRecordHelper::getDescriptionForField",
            "ActiveRecordHelper::getDescriptionForMethod",
            "ActiveRecordHelper::parseCommentForTags",
            "ActiveRecordHelper::parseCommentForTag",
            "ActiveRecordHelper::parseCommentForDescription",
            "ActiveRecordHelper::getTagValue",
            "ActiveRecordHelper::getTagName",
        ];
        $files = Hs\Helpers\HomeStudioHelper::getAllFilesInProject();
        //bug::reveal($files);
        $this->log(print_r($this->excludeFiles,1));
        foreach ($files as $file)
        {
            $this->log($file);
            if(in_array($file,$this->excludeFiles))
            {
                continue;
            }
            $content = file_get_contents($file);
            foreach ($depricated as $string)
            {
                //Для статических методов еще проверяем файл
                $parts = explode("::",$string);
                if($parts > 1 && StringHelper::hasSubstring($file,$parts[0]))
                {
                    $string = "static::".$parts[1];
                    $hasString  = StringHelper::hasSubstring($content,$string);
                    if(!$hasString)
                    {
                        $string = "self::".$parts[1];
                        $hasString  = StringHelper::hasSubstring($content,$string);
                    }
                }
                else
                {
                    $hasString = StringHelper::hasSubstring($content, $string);
                }
                $this->assertFalse($hasString, "Найдена запрещанная строка '$string' в файле '$file'.");
            }
        }
    }
}
