<?php
namespace Hs\Test\Mocks;
\Yii::import("root.lib-Yii.extensions.mailer.EMailer");
/**
 * Менеджер писем, который не отправляет письма на самом деле
 *
 * @package Hs\Test\Mocks
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MockMailer extends \EMailer
{
    protected $count = 0;
    
    /**
     * Отправка письма.
     * @return boolean Всегда успешная отправка
     */
    public function Send()
    {
        $this->count++;
        return true;
    }
    
    /**
     * Получение количества отправленных писем
     * @return Number
     */
    public function getSentMailsCount()
    {
        return $this->count;
    }
}
