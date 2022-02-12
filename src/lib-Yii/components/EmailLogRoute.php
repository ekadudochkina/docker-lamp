<?php
/**
 * Отправляет сообщения об ошибках на имейл.
 * Данный класс был необходим, так как Yii использует mail(), 
 * а он работает с локальным почтовым сервером, в то время как мы используем внешний.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
class EmailLogRoute extends CEmailLogRoute
{

    /**
     * Sends an email.
     * @param string $email single email address
     * @param string $subject email subject
     * @param string $message email content
     */
    protected function sendEmail($email, $subject, $message)
    {
        //Отсылаем на демо стенде или в продакшене     
        if(!EnvHelper::isDemo() && !EnvHelper::isProduction())
        {
            return;
        }
        
        //Мы должны же как-то отличать продакшн от демо
        if(EnvHelper::isDemo())
        {
            $email = "test@home-studio.pro";
        }
        //Стандартная тема письма идиотская
        $subject = "Ошибка в проекте ".Yii::app()->name;
        
        $message .= print_r($_SERVER,true);
        $message .= print_r($_REQUEST,true);
        $message = nl2br($message);
        $mailer = BaseController::getBasicMailer();
        $mailer->AddAddress($email);
        $mailer->Subject = $subject;
        $mailer->Body = $message;
        $mailer->Send();
    }

}
