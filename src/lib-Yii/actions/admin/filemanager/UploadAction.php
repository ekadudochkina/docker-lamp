<?php

/**
 * Загрузка изображений
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class UploadAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {
        $editor = new TextEditor();
        $editor->upload();
    }

}
