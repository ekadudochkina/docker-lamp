<?php

/**
 * Менеджер для создания писем с HTML.
 * Каждое письмо является вьюшкой, а также может содержать Layout.
 *
 * @package Hs
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class EmailManager
{

    /**
     * Контроллер
     * 
     * @var BaseController
     */
    protected $controller;

    /**
     * Путь к вьюшкам
     * 
     * @var String
     */
    protected $viewPath;

    /**
     * @param BaseController $controller
     * @param String $viewPath Алиас Yii для папки, содержащей вьюшки
     */
    public function __construct(BaseController $controller, $viewPath)
    {
        $this->controller = $controller;
        $this->viewPath = $viewPath;
    }

    /**
     * Отправляет письмо на адрес электронной почты.
     * 
     * @param String $address Адрес электронной почты
     * @param String $title Название письма
     * @param String $view Имя вьюшки в стиле Yii. Например "registration".
     * @param Mixed $data Данные, которые необходимо передать на вьюшку
     * @return Bool True, если сообщение отправлено успешно
     */
    public function sendEmail($address, $title, $view, $data = [])
    {
        $mailer = $this->controller->getMailer();
        $mailer->AddAddress($address);
        $mailer->Subject = $title;
        $mailer->Body = $this->getEmailBody($view, $data);
        return $mailer->Send();
    }

    /**
     * Получение HTML кода письма
     * 
     * @param String $view Имя вьюшки в стиле Yii. Например "registration".
     * @param Mixed $data Данные, которые необходимо передать на вьюшку
     * @return type
     */
    public function getEmailBody($view, $data = [])
    {
        $view = $this->viewPath . "." . $view;
        $layout = $this->viewPath . ".layout";
        $inner = $this->controller->renderPartial($view, $data, true);
        $viewData = ['content' => $inner];
        $body = $this->controller->renderPartial($layout, $viewData, true);
        return $body;
    }

    /**
     * Отправляет письмо на адрес электронной почты пользователя.
     * 
     * @param IUser $user Пользователь
     * @param String $title Название письма
     * @param String $view Имя вьюшки в стиле Yii. Например "registration".
     * @param Mixed $data Данные, которые необходимо передать на вьюшку
     * @return Bool True, если сообщение отправлено успешно
     */
    public function sendEmailToUser(IUser $user, $title, $view, $data = [])
    {
        return $this->sendEmail($user->getEmail(), $title, $view, $data);
    }

    /**
     * Получение пути к папке с письмами
     * 
     * @return String Алиас Yii для папки
     */
    public function getViewPathAlias()
    {
        return $this->viewPath;
    }

}
