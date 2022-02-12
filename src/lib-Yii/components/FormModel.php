<?php
/**
 * Абстрактный класс форм. Некая прослойка 
 * 
 * @method String[] getActionErrors() Возвращает все ошибки действия (или коды, если сайт мультиязычный).
 * @method String getActionError() Возвращает первую ошибку метода или ошибку по-умолчанию (или код, если сайт мультиязычный).
 * @method String addActionError(String $text) Добавение ошибки в модель (или кода, если сайт мультиязычный).
 * @method String getFirstError() Возвращает первую ошибку валидации или ошибку по-умолчанию.
 * @method Boolean hasActionErrors() Проверка, есть ли ошибки действий
 * @method Boolean mergeErrors(String $model) Забирает ошибки действий у модели и добавляет их себе
 * 
 * @see ActionErrorsBehaviour
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
abstract class FormModel extends CFormModel
{
   
   /**
    * Возвращает массив поведений класса
    * @see ActionErrorsBehaviour
    * @return Array[]
    */
    public function behaviors()
    {
	$arr = parent::behaviors();
	
	$arr['ActionErrors'] = array("class"=>"ActionErrorsBehaviour");
	return $arr;
    }
    
    /**
     * Создает url, который содержит данные формы. 
     * 
     * Данная функция необходимо, если данные формы на одно странице 
     * нужно передать форме на другой.
     * 
     * @param CController $controller Контроллер
     * @param String $route Роут контроллера
     * @return String Url на который можно редиректить
     */
    public function createRedirectUrl(CController $controller,$route,$paramName = "id")
    {
        //Чтобы Yii не сыпал ошибки, да и чтобы данные были целее, мы завертываем только safe атрибуты
        $data = $this->getAttributes($this->getSafeAttributeNames());
        $params = UrlHelper::createParams($data);
        $encodedParams = base64_encode($params);
        $url = $controller->createAbsoluteUrl($route,array($paramName => $encodedParams));
        return $url;
    }
    
    
    /**
     * Получает данные переданные от другой формы
     * 
     * @param BaseController $controller Объект контроллера
     * @param String $paramName Имя параметра, из которого нужно получать данные
     * @return boolean True, если данные были получены и False если их не было
     */
    public function collectRedirectedData(BaseController $controller,$paramName = "id")
    {
        $encodedParams = $controller->getRequest()->getParam($paramName);
        if(!$encodedParams)
            return false;
        
        $paramsString = base64_decode($encodedParams);
        $attributes = UrlHelper::getParamsFromUrl("/?".$paramsString);
        $this->setAttributes($attributes);
        return true;
    }
}