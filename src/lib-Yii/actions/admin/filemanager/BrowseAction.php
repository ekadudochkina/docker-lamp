<?php

/**
 * Выбор изображений
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class BrowseAction extends ViewAction
{

    /**
     * Запуск экшена
     */
    public function run()
    {
        $editor = new TextEditor();
        $editor->browse();
    }

}
