<?php

/**
 * Фильтр для списка моделей.
 * Применяется для создания постраничной навигации, фильтрации списка и сортировки по полям модели.
 * Дополнительной обработки параметров не требуется - фильтр сам создаст нужные url'ы и обработает входящие параметры.
 * 
 * <b>example:</b>
 * <pre>
 *   //Код представления
 *   <!-- получение моделей на текущей странице -->
 *   <? $models = $filter->getModels(); ?>
 *      <? foreach($models as $item): ?>
 *         <.div><?=$item->title?> <.div> 
 *      <? endforeach; ?>
 *  <!-- постраничная навигация -->
 * 	<?=$this->renderPartial("/layouts/shopfast/pagination",array("filter"=>$filter))?>
 * </pre>
 * 
 * <b>example:</b>
 * <pre>
 *      //Код контроллера
 *  $model = new Item();
 *  $model->with("Partner");
 *  $filter = new ModelFilter($model,$this);
 *  $filter->modelsPerPage = 3;       
 *  $this->render('ItemList',array('filter'=>$filter));
 * </pre>
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs
 */
class ModelFilter extends CComponent
{

    /**
     * @var String Пространство имен, чтобы параметры на перепутались. 
     * Например параметр name будет храниться в $_GET как filter.name
     */
    protected $namespace = "filter";

    /**
     * @var String Направление сортировки по-умолчанию
     */
    protected $defaultOrderDirection = "desc";

    /**
     * @var Controller Объект контроллера
     */
    protected $controller;

    /**
     * @var ActiveRecord Пустой объект модели, для которой нужно сделать список.
     * Используется как ссылка на класс моделей списка.
     */
    protected $model;

    /**
     * @var ActiveRecord[] Массив моделей, которые есть на текущей странице.
     * Заполняется после выполнения запроса к БД.
     */
    protected $models;

    /**
     * @var Integer Количество моделей на странице
     * @see CComponent Является публичным свойством
     */
    protected $modelsPerPage = 5;

    /**
     * @var Integer Общее количество моделей
     * Заполняется после выполнения запроса к БД.
     */
    protected $modelCount = 0;

    /**
     * @var Integer Количество страниц
     * Заполняется после выполнения запроса к БД.
     */
    protected $pageCount = 0;

    /**
     * @var Boolean Был ли исполнен запрос к БД
     * Заполняется в execute(), используется в методе ready() 
     */
    protected $isExecuted = false;

    /**
     * @var СDbCriteria Объект запроса к БД
     */
    protected $criteria;

    /**
     * @var String[] Ассоциативный массив параметров фильтра
     * Изначально получается из $_GET
     */
    protected $params = array();
    
   /**
     * @var String[] Ассоциативный массив параметров, не предназначенных для фильтра
     * Изначально получается из $_GET
     */
    protected $nonFilterParams = array();

    /**
     * Конструктор
     * @param Model $model Пустой объект модели, для которой необходимо выводить таблицу
     * @param CController $controller Контроллер, желательно текущий
     */
    public function __construct($model, $controller)
    {
	$this->controller = $controller;
	$this->model = $model;
	$this->extractParams($_GET);
    }

    /**
     * Назначение параметра для фильтра
     * 
     * @param String $name Имя параметра
     * @param Strng $value Значение параметра
     */
    public function setParam($name, $value)
    {

	$this->params[$name] = $value;
    }

    /**
     * Сеттер для свойства "количество моделей на странице"
     * @param Integer $value Значение
     * @throws Exception Исключение, если значение не  <= 0
     */
    public function setModelsPerPage($value)
    {
	if (!is_numeric($value) || $value <= 0)
	    throw new Exception("Количество моделей на станице должно быть положительным числом");
	$this->modelsPerPage = $value;
    }

    /**
     * Получение текущего объекта запроса для базы данных, с целью его дальнейшего изменения
     * @return CDbCriteria Объект запроса к базе данных
     */
    public function getCriteria()
    {
	$this->criteria = $this->criteria != null ? $this->criteria : new CDbCriteria();
	return $this->criteria;
    }

    /**
     * Получение текущей страницы списка, то есть моделей отображаемых на текущей странице.
     * @return ActiveRecord[] Массив моделей
     */
    public function getModels()
    {
	if (!$this->ready())
	    $this->execute();
	return $this->models;
    }

    /**
     * Получение общего количества страниц
     * @return Integer Количество страниц
     */
    public function getPageCount()
    {
	return $this->pageCount;
    }

    /**
     * Генерация ссылки на страницу списка.
     * @example 
     * <br>   <? for ($i = 1; $i <= $filter->getPageCount(); $i++): ?>
     * <br>       <.div class="<?= $filter->ifPageActive($i, "active") ?>">
     * <br>           <a href="<?= $filter->page($i) ?>"><?= $i ?></a>
     * <br>       <.div>
     * <br>    <? endfor; ?>
     * @param Integer $number Номер страницы
     * @return String Ссылка на страницу
     */
    public function page($number)
    {
	$params = $this->getParams();
	$params['filter.page'] = $number;
	$link = $this->createUrl($params);
	return $link;
    }

    /**
     * Генерация ссылки на первую страницу списка.
     * @return String Ссылка на страницу
     */
    public function firstPage()
    {
	return $this->page(1);
    }

    /**
     * Генерация ссылки на последнюю страницу списка.
     * @return String Ссылка на страницу
     */
    public function lastPage()
    {
	return $this->page($this->pageCount);
    }

    /**
     * Проверяет, является ли страница текущей
     * @param Integer $number Номер страницы
     * @return Boolean True, если страница является текущей
     */
    public function isPageActive($number)
    {
	return $number == $this->params['page'];
    }

    
    /**
     * Получение текущей страницы
     * 
     * @return Integer номер текущей страницы
     */
    public function getActivePage()
    {
        if(isset($this->params['page']))
        {
            return $this->params['page'];
        }
        return 1;
    }
    /**
     * Проверяет, является ли страница текущей и выводит $text, если ответ положительный.
     * @example 
     * <br>   <? for ($i = 1; $i <= $filter->getPageCount(); $i++): ?>
     * <br>       <.div class="<?= $filter->ifPageActive($i, "active") ?>">
     * <br>           <a href="<?= $filter->page($i) ?>"><?= $i ?></a>
     * <br>       <.div>
     * <br>    <? endfor; ?>
     * @param Integer $number Номер страницы
     * @param String $text Текст, который должен быть выведен
     * @return String $text Текст, который должен быть выведен или пустая строка
     */
    public function ifPageActive($number, $text)
    {
	return $this->isPageActive($number) ? $text : "";
    }

    /**
     * Генерирует ссылку для сортировки списка по полю модели.
     * @example
     *  <.thead>
     * <br> <.tr>
     * <br> <.th><a href='<?= $filter->orderBy("id") ?>'><?=$filter->orderDirection("id","↑","↓")?> #</a></.th>
     * <br> <.th><a href='<?= $filter->orderBy("Fullname") ?>'><?=$filter->orderDirection("Fullname","↑","↓")?> Имя пользователя</a></.th>
     * <br> </.tr>
     * <br> </.thead>
     * @param String $field Имя поля, которое должно быть использовано для сортировки
     * @return String Ссылка на страницу
     */
    public function orderBy($field)
    {
	$params = $this->getParams();
	$oldOrderBy = $params["filter.orderBy"];
	$oldDirection = $params['filter.orderByDirection'];
	$params['filter.orderBy'] = $field;
	if ($oldOrderBy == $field)
	    $params['filter.orderByDirection'] = $oldDirection == "asc" ? "desc" : "asc";

	$link = $this->createUrl($params);
	return $link;
    }

    /**
     * Проверяет сортируется ли поле модели и отображает строку в случае, если сортируется.
     * @example
     *  <.thead>
     * <br> <.tr>
     * <br> <.th><a href='<?= $filter->orderBy("id") ?>'><?=$filter->orderDirection("id","↑","↓")?> #</a></.th>
     * <br> <.th><a href='<?= $filter->orderBy("Fullname") ?>'><?=$filter->orderDirection("Fullname","↑","↓")?> Имя пользователя</a></.th>
     * <br> </.tr>
     * <br> </.thead>
     * @param String $field Имя поля, которое могло быть использовано для сортировки
     * @param String $asc $desc Строка, отображаемая в случае сортировки по возрастанию
     * @param String $desc Строка, отображаемая в случае сортировки по убыванию
     * @return String Строка отображаемая в случае сортировки по заданному полю или пустая строка, в случае ее отсутствия
     */
    public function orderDirection($field, $asc = "↑", $desc = "↓")
    {
	$flag = $this->params['orderByDirection'] == "asc";
	if ($this->params['orderBy'] != $field)
	    return "";
	$ret = $flag ? $asc : $desc;
	return $ret;
    }

    /**
     * Создает ссылке на текущую страницу списка.
     * @param String[] $params Ассоциативный массив параметров и их значений
     * @return  String Ссылка на страницу 
     */
    public function createUrl($params)
    {
        $mergedParams = array_merge($params,$this->nonFilterParams);
	$controllerId = $this->controller->id;
	$actionId = $this->controller->action->id;
	$link = $this->controller->createAbsoluteUrl($controllerId . '/' . $actionId, $mergedParams);
	return $link;
    }

    /**
     * Выполняет запрос к базе данных. Этот метод необязательно вызывать явно, так как попытка получить модели вызовет его неявно.
     */
    public function execute()
    {
	$this->criteria = $this->prepareCriteria();
	$this->isExecuted = true;
	$condition = $this->criteria;
	$this->modelCount = $this->model->count($condition);
	$this->models = $this->model->findAll($condition);

	$this->pageCount = ceil($this->modelCount / $this->modelsPerPage);
    }

    /**
     * Проверяет, выполнен ли запрос к БД и получены ли модели.
     * @return Boolean True, если запрос к БД был выполнен
     */
    protected function ready()
    {
	$ready = $this->isExecuted;
	return $ready;
    }

    /**
     * Получение параметров фильтра.
     * В параметры подставляется префикс, чтобы они не были перепутаны с другими параметрами на странице.
     * @return String[] $params Ассоциативный массив параметров и их значений
     */
    protected function getParams()
    {
	$namespace = $this->namespace;
	$array = array();
	foreach ($this->params as $key => $value)
	    $array[$namespace . "." . $key] = $value;
	return $array;
    }

    /**
     * Получение параметров фильтра из ассоциативного массиива (Предположительно $_GET.
     * Из параметров убирается префикс для удобства использования внутри класса.
     */
    protected function extractParams($array)
    {
        $nonFilter = [];
	$result = array();
	$result['page'] = 1;
	$result['orderBy'] = "id";
	$result['orderByDirection'] = $this->defaultOrderDirection;
	$len = strlen($this->namespace) + 1;
	foreach ($array as $key => $value)
        {
            $pos = strpos($key, $this->namespace);
            //bug::dump($key,$pos);
            if($pos === false)
            {
                $nonFilter[$key] = $value;
            }
            else if ($pos >= 0)
	    {
		$name = substr($key, $len);
		$result[$name] = $value;
	    }
        }
	$this->params =  $result;
        $this->nonFilterParams = $nonFilter;
    }

    /**
     * Подготовка запроса к базе данных к его выполнению. Заполнение параметров запроса
     * @return CDbCriteria Объект запроса к БД
     */
    protected function prepareCriteria()
    {
	$criteria = $this->getCriteria();
	$offset = ceil(($this->params['page'] - 1) * $this->modelsPerPage);

        $criteria->alias = $criteria->alias ? $criteria->alias : "t";
	$criteria->offset = $offset;
	$criteria->limit = $criteria->limit != -1 ? $criteria->limit : $this->modelsPerPage;
	$criteria->order = $criteria->alias.".".$this->params["orderBy"] . " " . $this->params["orderByDirection"];
        //bug::drop($this->params);
	return $criteria;
    }
    
    
    /**
     * Конвертирует результат фильтрации моделей в объект Excel талицы
     * 
     * @param String[] $fields Пути к полям колонок в моделях
     * @param String[] $columnTitles Заголовки колонок
     * @param String $title Заголовок таблицы
     * @return PHPExcel Объект таблицы Excel
     */
    public function toExcel($fields,$columnTitles=null,$title=null)
    {
    	if(!$this->isExecuted)
	   $this->execute ();
	
	$models = $this->getModels();
	$array = ActiveRecordHelper::modelsToArray($models,$fields,null,true);
	
	$table = ArrayHelper::arrayToExcel($array,$columnTitles,$title);
	return $table;
    }
    
    /**
     * Возвращает общее число моделей
     * @return Number
     */
    public function getModelCount()
    {
        return $this->modelCount;
    }

    /**
     * Возвращает количество моделей на странице
     * @return Integer
     */
    public function getModelsPerPage()
    {
        return $this->modelsPerPage;
    }
}