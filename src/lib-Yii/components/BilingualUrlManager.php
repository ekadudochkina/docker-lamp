<?php

/**
 * Менеджер URL, который подставляет языковые переменные в ссылки.
 * 
 * Есть один главный язык. Он никак не отмечается в URL. 
 * Остальные языки, подставляются в ссылку до контроллера, например en/controller/action/12
 * Языки хранятся в массиве $languages, причем ключом является красивое название языка, а значением его настроящее название Yii.
 * 
 * Перед всеми действиями необходимо вызывать  updateYiiLanguage
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
class BilingualUrlManager extends CUrlManager
{

    /**
     * Язык по-молчанию
     * @var String 
     */
    protected $defaultLanguageCode = "ru";

    /**
     * Список всех языков 
     * @var String[] 
     */
    protected $languages = array("en" => "en_us", "ru" => "ru_ru");

    /**
     * Установка текущего языка
     * 
     * @param $code Код текущего языка bp двух букв
     */
    public function setDefaultLanguage($code)
    {
        $this->defaultLanguageCode = $code;
    }

    /**
     * Установка текущих языков для Yii.
     * 
     * @param String[] $arr Ассоциативный массив типа array("ru" =>"ru_ru", "en"=>"en_us")
     */
    public function setYiiLanguages($arr)
    {
        $this->languages = $arr;
    }

    /**
     * Получение текущего языка
     */
    public function getCurrentLanguageCode()
    {
        $lang = Yii::app()->getRequest()->getParam("language", $this->defaultLanguageCode);
        return $lang;
    }

    /**
     * Устанавливает язык YII
     */
    public function updateYiiLanguage()
    {
        $lang = $this->languages[$this->getCurrentLanguageCode()];
        Yii::app()->setLanguage($lang);
    }

    /**
     * Constructs a URL.
     * @param string $route the controller and the action (e.g. article/read)
     * @param array $params list of GET parameters (name=>value). Both the name and value will be URL-encoded.
     * If the name is '#', the corresponding value will be treated as an anchor
     * and will be appended at the end of the URL.
     * @param string $ampersand the token separating name-value pairs in the URL. Defaults to '&'.
     * @return string the constructed URL
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        $this->addLanguageToRoute($route, $params);
        return parent::createUrl($route, $params, $ampersand);
    }

    /**
     * Переделывание URL с учетом мультиязычности
     * @param String $str Роут
     * @param String[] $params Параметры
     * @return String Роут с языковой переменной
     */
    protected function addLanguageToRoute(&$str, &$params = array())
    {
        //Получаем язык
        $language = isset($params['language']) ? $params['language'] : $this->getCurrentLanguageCode();

        //Убираем язык из параметров, на случай, если он совпадет с главным языком
        unset($params['language']);
        if ($language == $this->defaultLanguageCode)
            return;

        if (!in_array($language, array_keys($this->languages)))
            throw new Exception("'$language' is unknown langauge");

        //Если все впорядке, то ставим язык в ссылку
        $params['language'] = explode("_", $language)[0];
    }

    /**
     * Получение списка доступных языков
     * 
     * @param Bool $excludeCurrent Исключить текущий язык
     * @return String[]
     */
    public function getLanguages($excludeCurrent = true)
    {
        $languages = $this->languages;
        $key = array_search($this->getCurrentLanguageCode(), $languages);
        if ($excludeCurrent == true && $key !== false)
            unset($languages[$key]);
        return $languages;
    }

}
