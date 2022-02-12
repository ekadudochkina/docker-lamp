<?php

/**
 * Класс для автономных экшенов. Такие экшены могут быть переиспользованы в других контроллерах.
 * В основном используется в панели управления сайтом
 * 
 * @package Hs\Actions
 * @property BaseController $controller
 */
class ViewAction extends CViewAction
{

    protected $viewData = [];

    /**
     * Отображает шаблон на экран или возвращает его строковое представление
     * 
     * @param String $view Название view (необзательно)
     * @param Array $data Данные для view
     * @param Bool $return Если True, то шаблон будет возвращен в виде строки
     * @return String Шаблон с подставленными переменными
     */
    protected function render()
    {
        $this->controller->render($this->view, $this->viewData);
    }

    /**
     * Добавляет переменную для шаблона экшена (view)
     * 
     * @param Mixed $value Значение
     * @param String $name Имя переменной во вью
     */
    public function addViewData($value, $key)
    {
        $this->viewData[$key] = $value;
    }

    /**
     * Получение объекта HTTP запроса
     * @return CHttpRequest Объект http запроса
     */
    public function getRequest()
    {
        return $this->controller->getRequest();
    }

    /**
     * Отображает сообщение об успехе пользователю <br>
     * <b>Для корректной работы функции необходимо убедиться, что в верстке данные сообщения отображаются</b>
     * 
     * @param String $message Текст сообщения
     */
    public function showSuccessMessage($msg)
    {
        return $this->controller->showSuccessMessage($msg);
    }

    /**
     * Перенаправляет пользователя по url для роута
     * 
     * @param String $route Роут Yii
     * @param Bool $terminate Если True, то останавливает дальнейшее выполнение приложения
     * @param Int $statusCode Статус, который будет выслан браузеру
     */
    public function redirectToRoute($route)
    {
        return $this->controller->redirectToRoute($route);
    }

}
