<?php

/**
 * Объект, который отвечает за проведение работ на веб сайте
 *
 * @package Hs
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class MaintenanceManager
{
    protected $data;
    
    protected $filePath = null;
    protected $templatePath = "root.lib-Yii.views.maintenance.simple";


    /**
     * @param String $templatePath Путь Yii  HTML шаблону
     */
    public function __construct($templatePath = null)
    {
        $this->templatePath = $templatePath ? $templatePath : $this->templatePath; 
        $alias = "application.runtime";
        $runTimePath = Yii::getPathOfAlias($alias);
        $path = $runTimePath."/maintenance";
        $this->filePath = $path;
        
        
        if(!file_exists($path))
        {
            $data = ['enabled'=> true, "ips"=>[]];
            $this->data = $data;
            $this->save();
        }
        else
        {
            $serialized = file_get_contents($path);
            $data = unserialize($serialized);
            $this->data = $data;
        }
        
    }
    
    /**
     * Доступен ли веб-сайт
     * 
     * @return Bool
     */
    public function isAvailable()
    {
        return $this->data['enabled'];
    }
    
    /**
     * Добавление IP адреса в белый список
     * 
     * @param String $ip
     * @return True, если Ip добавился в список
     */
    public function addIp($ip)
    {
        $this->data['ips'][] = trim($ip);
        return $this->save();
        
    }
    
    /**
     * Удаление IP из белого списка
     * @param String $ip Ip адрес
     * @return Bool True, если изменение прошло успешно
     */
    public function removeIp($ip)
    {
        unset($this->data['ips'][$ip]);
        $this->data['ips'] = array_values($this->data['ips']);
        return $this->save();
    }
    
    /**
     * Получение IP адресов белого списка
     * 
     * @return String[]
     */
    public function getIps()
    {
        return $this->data['ips'];
    }
    
    /**
     * Включение режима обслуживания
     * 
     * @return Boolean
     */
    public function turnOn()
    {
        $this->data['enabled'] = true;
        return $this->save();
    }
    
    /**
     * Выключение режима обслуживания
     * 
     * @return Boolean
     */
    public function turnOff()
    {
         $this->data['enabled'] = false;
        return $this->save();
    }
    
    /**
     * Обработка режима обслуживание. 
     * Если веб-сайт заблокирован администратором и пользователь не добавлен в белый список, то он увидет спец. страницу.
     * 
     * @param BaseController $ctrl Контроллер
     * @return Bool True, если веб-сайт не заблокирован для пользователя
     */
    public function process(BaseController $ctrl)
    {
        if($this->isAvailable())
            return true;
        if($this->isWhiteList())
            return true;
        
        $this->showHtml($ctrl);
        return false;
    }

    /**
     * Отображение HTML режима обслуживания
     * 
     * @param BaseController $ctrl Контроллер
     */
    protected function showHtml(BaseController $ctrl)
    {
        
        $content = $ctrl->renderPartial($this->templatePath,[],true);
        echo $content;
        $this->end();
    }
    
    /**
     * Остановка приложения
     */
    protected function end()
    {
        Bug::stop();
    }

    /**
     * Сохранение текущего состояния
     * 
     * @return boolean String
     */
    protected function save()
    {
        $str = serialize($this->data);
        file_put_contents($this->filePath,$str);
        return true;
    }

    /**
     * Проверка, является ли текущий пользователь в белом списке
     * 
     * @return boolean True, если пользователь в белом списке
     */
    public function isWhiteList()
    {
        $ip = EnvHelper::getClientIp();
        foreach($this->getIps() as $allowedIp)
        {
            if($allowedIp == $ip)
            {
                return true;
            }
        }
        return false;
    }

}
