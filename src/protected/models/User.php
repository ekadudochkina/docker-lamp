<?php

/**
 * Модель пользователя проекта. Содержит уникальные для проекта функции.
 */
class User extends BaseUser
{

    /**
     * Лимит отслеживаемых товаров
     *
     * @var Integer
     * @autogenerated 24-05-2016
     */
    public $trackingItemLimit;

    /**
     * Лимит отслеживаемых магазинов
     *
     * @var Integer
     * @autogenerated 24-05-2016
     */
    public $trackingShopLimit;

    /**
     * Аккаун активен до этой даты
     *
     * @sqltype DATE
     * @var String
     */
    public $activeTime;

    /**
     * Активен ли акаунт или нет
     *
     * @var Boolean
     * @autogenerated 24-05-2016
     */
    public $status;

    /**
     * Роль
     *
     * @var Integer
     * @autogenerated 24-05-2016
     */
    public $role;

    /**
     * Seller в амазон
     *
     * @var String
     * @autogenerated 29-08-2017
     */
    public $seller;

    /**
     * Доступ к MWS Api
     *
     * @var String
     */
    public $mwsAuthToken;

    /**
     * Дата добавления токена MWS
     *
     * @sqltype DATETIME
     * @var String
     * @autogenerated 24-05-2016
     */
    public $mwsAuthTokenDate;

    /**
     * Первая введеная ссылка пользователем для поиска магазинов
     *
     * @var String
     * @autogenerated 29-08-2017
     */
    public $firstsellerurl;

    /**
     * Офицальное наименование компании (не амазон)
     *
     * @var String
     * @autogenerated 29-08-2017
     */
    public $companyName;

    /**
     * Дополнительный email
     *
     * @var String
     * @autogenerated 29-08-2017
     */
    public $additionalEmail;

    /**
     * Код страны для библиотеки вывода флогов
     *
     * @var String
     * @autogenerated 29-08-2017
     */
    public $locationCode;

    /**
     * Главная картинка
     * <b>Внешний ключ.</b>
     * <b>Внешний ключ.</b>
     * @var Integer
     * @update CASCADE
     * @delete RESTRICT
     * @autogenerated 09-06-2017
     */
    public $mainImageId = null;


    /**
     * Timezone
     *
     * @var Integer
     * @autogenerated 29-08-2017
     */
    public $timezoneId;

    /**
     * Формат дат
     *
     * @var String
     * @autogenerated 29-08-2017
     */
    public $dateFormat;

    /**
     * Подвердил ли пользователь свой платежный метод
     *
     * @var Integer
     * @autogenerated 02-05-2019
     */
    public $paymentMethodVerified = 2;

    const PAYMENT_METHOD_NOT_VERIFIED = 0;
    const PAYMENT_METHOD_VERIFIED = 1;
    const PAYMENT_METHOD_NEEDS_VERIFICATION = 2;
    const PAYMENT_METHOD_UNSUBSCRIBED = 3;
    const PAYMENT_METHOD_FAILED = 4;

    const DATEFORMAT_DD_MM_YYYY = "dd.mm.yyyy";
    const DATEFORMAT_MM_DD_YYYY = "mm.dd.yyyy";
    const DATEFORMAT_YYYY_MM_DD = "yyyy.mm.dd";

    public function __construct($scenario = 'insert')
    {
        $this->isAdmin = 0;
        $this->status = 1;
        parent::__construct($scenario);
    }

    /**
     *  Возвращает правила валидации.
     * <b>Внимание: для полей у которых в БД тип VARCHAR необходимо создать валидатор "length".</b>
     *
     * @autogenerated 29-08-2017
     * @return Array[] Массив правил валидации
     */
    public function rules()
    {
        $arr = parent::rules();
        $arr[] = array('activeTime,trackingShopLimit,trackingItemLimit,address,fullname,email,status,seller,additionalEmail,companyName,locationCode,mainImageId,timezoneId,dateFormat', 'safe');
        $arr[] = array('activeTime', 'type', 'type' => 'date', 'dateFormat' => 'yyyy-MM-dd');
        $arr[] = array('mwsAuthTokenDate', 'type', 'type' => 'datetime', 'datetimeFormat' => 'yyyy-MM-dd hh:mm:ss');
        $arr[] = array('trackingShopLimit', 'numerical', 'max' => 9);
        $arr[] = array('email', 'required');
        return $arr;
    }

    /**
     * Возвращает массив связей моделей.
     * <b>Внимание: связи BELONGS_TO являются внешними ключами.</b> Для них можно указать поведение при удалений родительской сущности.
     *
     * @return Array[] Массив связей
     * @autogenerated 23-07-2017 Merged
     */
    public function relations()
    {
        $arr = parent::relations();
        $arr["image"] = array(self::BELONGS_TO, 'UserImage', 'mainImageId');
        $arr["tasks"] = array(self::HAS_MANY, 'Task', 'userId');
        $arr["timezone"] = array(self::BELONGS_TO, 'Timezone', 'timezoneId');
        $arr["plans"] = array(self::HAS_MANY, 'Plan', 'userId');
        $arr["alerts"] = array(self::HAS_MANY,"Alert","userId");
        return $arr;
    }

    /**
     * Проверяет есть ли у пользователя хотя бы один добавленный магазин в приложение
     * @return bool
     */
    public function hasAnyShop()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array("userId" => $this->id));
        $userShop = OriginUserShop::model()->find($criteria);

        if ($userShop !== null)
        {
            return true;
        }

        return false;
    }

    /**
     * Возвращает строчку для dasboard о buybox
     * @return string
     */
    public function howShopHaveBuyBox()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array("userId" => $this->id));
        $items = Item::model()->findAll($criteria);

        $itemCount = count($items);
        $buyboxCount = 0;

        foreach ($items as $item)
        {
            $originItems = $item->originItems;
            foreach ($originItems as $originItem)
            {
                $criteria = new CDbCriteria();
                $criteria->order = "creationDate desc";
                $criteria->addColumnCondition(array("originItemId" => $originItem->getPk()));
                $buyBox = BuyBoxData::model()->find($criteria);

                if ($buyBox)
                {
                    if ($buyBox->haveBuyBox == true)
                    {
                        $buyboxCount = $buyboxCount + 1;
                        break;
                    }
                }
            }
        }


        $text = $buyboxCount . " of " . $itemCount;

        return $text;
    }

    /*
     * Возвращает сколько отслеживаемых магазинов у пользователя
     */

    public function howTrackingShop()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array("userId" => $this->id));
        $userShop = UserShop::model()->findAll($criteria);

        $count = count($userShop);

        return $count;
    }

    /*
     * Возвращает лимит на количество отслеживаемых магазинов
     */

    public function getTrackingShopLimit()
    {
        return $this->trackingShopLimit;
    }

    /*
     * Возвращает сколько отслеживаемых товаров
     */

    public function howTrackingItem()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array("userId" => $this->id));
        $items = Item::model()->findAll($criteria);

        $itemCount = count($items);

        return $itemCount;
    }

    /*
     * Возвращает лимит на количество отслеживаемых товаров
     */

    public function getTrackingItemLimit()
    {
        return $this->trackingItemLimit;
    }

    /**
     * Возвращает наименованеи магазина пользователя
     * @return []
     */
    public function getNameShopForUser($shopCode)
    {
        $attrs = ["code" => $shopCode];
        $shop = Shop::model()->findByAttributes($attrs);

        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array("userId" => $this->id, "shopId" => $shop->getPk()));
        $userShop = OriginUserShop::model()->find($criteria);

        if ($userShop !== null)
        {
            return $userShop->title;
        }

        return "";
    }

    /*
     * Активен ли аккаунт пользователя
     */

    public function isActive()
    {

        date_default_timezone_set("UTC");

        $time = time();
        $untiltimest = strtotime($this->activeTime);

        //$today = date("Y-m-d H:i:s", $time);
        //$today2 = gmdate("M d Y H:i:s", $time);
        //bug::drop($today, $today2);

        if ($this->status == true && $untiltimest >= $time)
        {
            return true;
        }

        if ($this->isAdmin() == true)
        {
            return true;
        }

        return false;
    }

    /**
     * Указывает пользователей администраторов
     * @return Boolean, true - если админ
     */
    public function isAdmin()
    {
        if ($this->isAdmin == true)
        {
            return true;
        }

        return false;
    }

    public function getImageUrl($controller)
    {
        if ($this->image == null)
        {
            return $controller->getAssetsUrl() . "/images/users/avatar-1.jpg";
        } else
        {
            $url = $this->image->getUrl();
            return $url;
        }
    }

    /**
     * Предоставил ли пользовател доступ к Amazon API
     *
     * @return boolean
     */
    public function hasAmazonAPI()
    {
//        return false;

        if ($this->mwsAuthToken)
        {
            return true;
        }
        return false;
    }

    public function generateMwsFindSellerTask($sellerId, $authKey)
    {
        $findItems = new Task();
        $findItems->type = Task::TYPE_FIND_SELLER_MWS;
        $findItems->setData(["user" => $this->getPk(),"token" => $authKey, "seller" => $sellerId]);
        $findItems->userId = $this->getPk();
        return $findItems;
    }

    public function generateFindSellerTask($sellerId)
    {
//        if(!$this->readyToFindItems())
//        {
//            throw new Exception("Can't call generateProductSearchTasks() on a new model");
//        }

//        if(!$this->hasAmazonAPI())
//        {
            $newTask = new Task();
            $newTask->userId = $this->getPk();
            $newTask->type = Task::TYPE_FIND_SELLER;
            $data = ["seller" => $sellerId];
            $newTask->setData($data);
            return $newTask;
//        }

//        $findItems = new Task();
//        $findItems->type = Task::TYPE_FIND_SELLER_MWS;
//        $findItems->setData(["user" => $this->getPk()]);
//        $findItems->userId = $this->getPk();
//        return $findItems;
    }

    public function getSellerId()
    {
        return $this->seller;
    }

    public function getLatestFindShopItemsTaskData()
    {
        $model = $this->hasAmazonAPI() ? MwsFindShopItemsTaskData::model() : FindShopItemsTaskData::model();
        $criteria = new CDbCriteria();
        $criteria->with = ["task" => ["alias" => "t"]];
        $criteria->alias = "fsi";
        $criteria->addColumnCondition(['t.userId'=>$this->getPk()]);
        $subquery = "select td.shopId, max(td.creationDate) as creationDate "
                . "from {$model->tableName()} as td "
                . "join tasks as tt on td.taskId = tt.id "
                . "where tt.userId = {$this->getPk()} "
                . "group by td.shopId";
        $criteria->join = "join ($subquery) as temp on fsi.shopId = temp.shopId and fsi.creationDate = temp.creationDate";
        $data = $model::model()->findAll($criteria);
//            bug::drop($data);
        return $data;
    }

    public function getFoundShopItemByPk($id)
    {
        if($this->hasAmazonAPI())
        {
            return MwsGetItemTaskData::model()->findByPk($id);
        }
        return GetItemTaskData::model()->findByPk($id);
    }

    /**
     *
     * @param Integer $id
     * @return FindShopItemsTaskData
     */
    public function getFindShopItemsTaskDataByPk($id)
    {
        if($this->hasAmazonAPI())
        {
            return MwsFindShopItemsTaskData::model()->findByPk($id);
        }
        return FindShopItemsTaskData::model()->findByPk($id);
    }

    public function generateProductSearchTaskForShop(Shop $shop)
    {
        if(!$this->readyToFindItems())
        {
            throw new Exception("Can't call generateProductSearchTasks() on a new model");
        }

        $newTask = new Task();
        $newTask->userId = $this->getPk();
        $newTask->type = $this->hasAmazonAPI() ? "mwsfindshopitems" : "findshopitems";
        $newTask->setData(["shop" => $shop->getPk(), "user" => $this->getPk(), "seller" => $this->getSellerId()]);
        $taskData = $this->hasAmazonAPI() ?  new MwsFindShopItemsTaskData() : new FindShopItemsTaskData();
        $taskData->setRelated($newTask);
        $taskData->setRelated($shop);
        $taskData->save();
        return $newTask;
    }

    /**
     * Предоставил ли пользователь все необходимые данные для поиска его товаров в API или скраппингом
     *
     * @return Boolean
     */
    public function readyToFindItems()
    {
        return $this->seller != null;
    }

    public function canStartSellerSearch()
    {
        $criteria = new CDbCriteria();
        $criteria->addInCondition("status",[Task::STATUS_IN_PROGRESS]);
        $criteria->addColumnCondition(["status"=>Task::STATUS_NEW],"AND","OR");
        $types = [
            Task::TYPE_FIND_SELLER,
            Task::TYPE_FIND_SELLER_MWS,
            Task::TYPE_FIND_SHOP_ITEMS,
            Task::TYPE_FIND_SHOP_ITEMS_MWS,
            Task::TYPE_FIND_SHOP,
            Task::TYPE_GET_ITEM,
        ];
        $criteria->addInCondition("type",$types);
        $criteria->addColumnCondition(["userId"=>$this->getPk()]);
        $start = DateTimeHelper::timestampToMysqlDateTime(time()-(3*60*60));
        $end = DateTimeHelper::timestampToMysqlDateTime(time()+(3*60*60));
        $criteria->addBetweenCondition("creationDate",$start,$end);
        $tasks = Task::model()->findAll($criteria);
        if($tasks)
        {
            return false;
        }
        return true;
    }

    public function canStartProductSearchForShop(Shop $shop)
    {
        if(!$this->canStartSellerSearch())
        {
            return false;
        }
        return true;
    }

    public function createTask($type,$data = [])
    {
    }

    public function getLatestFindShopTaskData()
    {
        $criteria = Task::model()->createCriteriaForRelation($this);
        $criteria->order = "id desc";
        $criteria->addColumnCondition([Task::f("status")=>Task::STATUS_COMPLETE]);
        $criteria->addInCondition(Task::f("type"),[Task::TYPE_FIND_SELLER, Task::TYPE_FIND_SELLER_MWS]);
        $task = Task::model()->find($criteria);
        if(!$task)
        {
            return [];
        }
        $criteria2 = $task->createCriteriaForRelation($task,"parent");
        $criteria2->addColumnCondition(["status"=>Task::STATUS_COMPLETE]);
        $criteria2->addInCondition("type",[Task::TYPE_FIND_SHOP]);
        $tasks = Task::model()->findAll($criteria2);
        $ids = ArrayHelper::removeDimention(ActiveRecordHelper::modelsToArray($tasks,["id"]));
        if(!$ids)
        {
            return [];
        }
        $criteria3 = new CDbCriteria();
        $criteria3->addInCondition(FindShopTaskData::f("taskId"),$ids);
        $criteria3->addColumnCondition([FindShopTaskData::f("status","t")=> 1]);
        $shops = FindShopTaskData::model()->with(["task" => ["alias" =>"tt"],"task.user"])->findAll($criteria3);
        return $shops;
    }


    public function getDateFormates()
    {
        $dateFormates = array();
        $dateFormates[User::DATEFORMAT_DD_MM_YYYY] = User::DATEFORMAT_DD_MM_YYYY;
        $dateFormates[User::DATEFORMAT_MM_DD_YYYY] = User::DATEFORMAT_MM_DD_YYYY;
        $dateFormates[User::DATEFORMAT_YYYY_MM_DD] = User::DATEFORMAT_YYYY_MM_DD;

        return $dateFormates;
    }


    /**
     * @return FindSellerTaskData[]
     */
    public function getShopSearches()
    {

        $types = [
            Task::TYPE_FIND_SELLER,
            Task::TYPE_FIND_SELLER_MWS
        ];
        $dataTypes = [
            FindSellerTaskData::model(),
            MwsFindSellerItemData::model(),
        ];

        $criteria = new CDbCriteria();
        $criteria->addInCondition("type",$types);
        $criteria->addColumnCondition(["userId" => $this->getPk(), "hidden" => false]);
        $criteria->order = "id desc";
        $tasks = Task::model()->findAll($criteria);

//        bug::drop($tasks);
        $taskIds = ArrayHelper::removeDimention(ActiveRecordHelper::modelsToArray($tasks,["id"],false,false,false));
        $data = [];
        foreach($dataTypes as $obj)
        {
            $criteria = new CDbCriteria();
            $criteria->addInCondition("d.taskId",$taskIds);
            $criteria->addInCondition("d.status",[AmazonHelper::STATUS_SUCCESS, AmazonHelper::ERROR_UNKNOWN_FROM_PHP]);
            $criteria->alias = "d";
            $models = $obj->with("task")->findAll($criteria);
            foreach($models as $model)
            {
                if($model->taskId && (!isset($data[$model->taskId]) || $data[$model->taskId]->status != AmazonHelper::STATUS_SUCCESS ))
                {
                    $data[$model->taskId] = $model;
                }
            }
        }

        foreach($tasks as $task)
        {
            if(!isset($data[$task->getPk()]))
            {
                $pos = array_search($task->type,$types);
                $type = $dataTypes[$pos];
                $class = get_class($type);
                $model = new $class;
                $model->setRelated($task);

                $data[$task->getPk()] = $model;
//                bug::show($task,$model);
            }
        }

        $values = array_values($data);
        usort($values,function($a,$b){
           return $a->taskId < $b->taskId ? 1 : -1;
        });

        return $values;

//        $model = FindShopTaskData::model();
//
//        $criteria = new CDbCriteria();
//        $criteria->with = ["task" => ["alias" => "t"]];
//        $criteria->alias = "fsi";
//        $criteria->addColumnCondition(['t.userId'=>$this->getPk(),"deleted"=>false]);
//        $subquery = "select td.sellerId, td.shopId, max(td.creationDate) as creationDate "
//            . "from {$model->tableName()} as td "
//            . "join tasks as tt on td.taskId = tt.id "
//            . "where tt.userId = {$this->getPk()} "
//            . "group by td.shopId,td.sellerId";
//        $criteria->join = "join ($subquery) as temp on fsi.sellerId = temp.sellerId and fsi.shopId = temp.shopId and fsi.creationDate = temp.creationDate";
//        $criteria->order = "fsi.creationDate desc";
//        $data = $model::model()->with("shop")->findAll($criteria);
//        $filtered = [];
//        foreach($data as $search)
//        {
//            if(!$search->hasShop && $search->getTask()->parentId)
//            {
//                continue;
//            }
//            $filtered[] = $search;
//        }
//        return $filtered;
    }

    public function isPaymentExpired()
    {
        if ($this->onTrial()) {
            return false;
        }

        if ($this->hasDebt()) {
            return true;
        }

        return false;
    }

    protected function onTrial()
    {
        $trial = 60 * 60 * 24 * 14;
        $regStamp = DateTimeHelper::MysqlDateTimeToTimestamp($this->creationDate);
        $trialEnds = $regStamp + $trial;
        $now = time();
        $onTrial = $now - $trialEnds < 0;
//        bug::Drop(
//            DateTimeHelper::timestampToMysqlDateTime($regStamp),
//            DateTimeHelper::timestampToMysqlDateTime($trialEnds),
//            DateTimeHelper::timestampToMysqlDateTime($now),
//            $now - $trialEnds
//        );
        return $onTrial;
    }

    /**
     * @return Payment[]
     */
    public function getPaymentsForThisMonth()
    {
        $now = EnvHelper::now();
        $thisMonth = date("Y-m",$now) . "-01";

        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(["userId" => $this->getPk(), "status" => Payment::STATUS_SUCCESS]);
        $criteria->addCondition("`date` >= \"$thisMonth\"");
//        $criteria->addInCondition("service",[Payment::SERVICE_MONTHLY_PLAN,Payment::SERVICE_DEBT,Payment::SERVICE_DEBT_LATE,Payment::SERVICE_TEST]);
        $criteria->order = "id desc";
        $payments = Payment::model()->findAll($criteria);
//        bug::drop($payments);
        return $payments;
    }

    public function getMonthlyPaymentSum()
    {
        $plan = $this->getCurrentPlan();
        $sum = $plan->products* AmazonHelper::getProductPrice();
        return $sum;
    }

    public function getNumberOfUniqueItems($includingVariations = true)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(["listingAttachments.userId" => $this->getPk(),"superListing.status"=>Listing::STATUS_ENABLED,"superListing.deleted" => false,"t.deleted" => false]);
        $criteria->with = [
            "listingAttachments",
            "listingAttachments.listing" => ["on" => "listing.mainProductId = t.id"],
            "listingAttachments.listing.listing" => [ "alias"=> "superListing"]
        ];
        $criteria->group = "asin";
        $count = OriginItem::model()->count($criteria);
        $count = intval($count);
        if($includingVariations)
        {
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(["listingAttachments.userId" => $this->getPk(),"superListing.status"=>Listing::STATUS_ENABLED,"superListing.variationsStatus"=>Listing::STATUS_ENABLED,"superListing.deleted" => false,"t.deleted" => false]);
            $criteria->with = [
                "listingAttachments",
                "listingAttachments.listing" => ["on" => "listing.mainProductId != t.id"],
                "listingAttachments.listing.listing" => [ "alias"=> "superListing"]
            ];
            $count2 = OriginItem::model()->count($criteria);
            $count2 = intval($count2);
            $count +=$count2;
        }

        return $count;
    }

    private function getPaidAmountForThisMonth()
    {
        $payments = $this->getPaymentsForThisMonth();
//        bug::drop($payments);
        $payed = 0;
        array_map(function (Payment $a) use (&$payed) {
            if ($a->status != Payment::STATUS_SUCCESS) {
                return;
            }
            $payed += $a->sum;
        }, $payments);
        return $payed;
    }

    public function hasDebt()
    {
        if($this->isInTrial())
        {
            return false;
        }
        $debt = $this->getDebt();
        return $debt > 0;
    }

    public function getDebt()
    {
        if($this->isInTrial())
        {
            return 0;
        }
        $now = EnvHelper::now();
        $debtIsActive = $this->isDebtActive($now);
        $price =  $this->calculatePartialMonthlyPayment($now,$debtIsActive);
        $paid = $this->getPaidAmountForThisMonth();

//        bug::drop($paid,$price);
        $debt = $price - $paid;
        $debt = $debt < 0 ? 0 : $debt;
        $debt = round($debt,2);

        return $debt;
    }

    protected function calculatePartialMonthlyPayment($stamp = null,$includeDebtDays = false,$monthlyPaymentSum = null)
    {
        if(!$stamp)
        {
            $stamp = EnvHelper::now();
        }
        $year = date("Y",$stamp);
        $month = date("m",$stamp);
        $day = intval(date("d",$stamp));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$month,$year);


        $noServiceDays = $day;
        if($includeDebtDays)
        {
            $noServiceDays = max($day - AmazonHelper::getDebtDaysNumber(),1);
        }

        $daysLeft = $daysInMonth - $noServiceDays + 1;


//        bug::drop("days",$daysInMonth,"left",$daysLeft,"no service",$noServiceDays);

        $monthlyPayment = $monthlyPaymentSum !== null ? $monthlyPaymentSum : $this->getMonthlyPaymentSum();
        $dailyPayment = $monthlyPayment / $daysInMonth;

        $sum = $dailyPayment*$daysLeft;
//        bug::drop($monthlyPayment,$dailyPayment,$sum);
        return $sum;
    }

    /**
     * @return Plan[]
     */
    public function getPlans()
    {
        $criteria = new CDbCriteria();
        $criteria->with = ["payment"];
        $criteria->addColumnCondition(["t.userId" => $this->getPk()]);
        $criteria->order= 't.creationDate desc';
        $criteria->alias = "t";
        $plans = Plan::model()->findAll($criteria);
//        $plan = new Plan();
//        $plan->userId = $this->getPk();
//        $plan->creationDate = $this->creationDate;
//        $plan->products = AmazonHelper::getMinimumProductNumberForPlan();
//        $plans[] = $plan;
        return $plans;
    }

    /**
     * @return Plan
     */
    public function getCurrentPlan()
    {
        $plans = $this->getPlans();

        $plan = null;
        while($el =  array_shift($plans))
        {
            if($el->paymentId || $el->isDowngrade || $el->isTrial)
            {
                $plan = $el;
                break;
            }
        }
        return $plan;
    }

    /**
     * @return Payment[]
     */
    public function getPayments($all = false)
    {
        $criteria = new CDbCriteria();
        if(!$all)
        {
            $criteria->addInCondition("status",[Payment::STATUS_SUCCESS,Payment::STATUS_FAILED]);
        }
        $criteria->addColumnCondition(["userId"=>$this->getPk()]);
        $models = Payment::model()->findAll($criteria);
        return $models;
    }

    /**
     * @param $service
     * @param $type
     * @param $sum
     * @return Payment
     */
    public function createPayment($service, $type, $sum,$date = null)
    {
        $payment = new Payment();
        $payment->sum = $sum;
        $payment->status = Payment::STATUS_NEW;
        $payment->type = $type;
        $payment->service = $service;
        $payment->userId = $this->getPk();
        if($type == Payment::TYPE_PERIODIC && $date == null)
        {
            throw new Exception("Date should be set for pereodic payments");
        }
        $date = $date ? $date : date("Y-m-d");
        $payment->date = $date;
        return $payment;
    }

    /**
     * @return Payment
     */
    public function createDebtPayment()
    {
        $debt = $this->getDebt();
        $planPrice = $this->getCurrentPlan()->getPrice();
        $service = $planPrice > $debt ? Payment::SERVICE_DEBT_LATE : Payment::SERVICE_DEBT;
        $nowMonth = date("Y-m")."-01";
        $payment = $this->createPayment(Payment::SERVICE_DEBT,$service,$debt,$nowMonth);
        return $payment;
    }

    /**
     * @param Plan $plan
     * @return Payment
     */
    public function createPlanPayment(Plan $plan)
    {
        if($plan->isDowngrade)
        {
            return null;
        }
        $planPrice = $plan->products * AmazonHelper::getProductPrice();
        $current = $this->getCurrentPlan();
        $currentPlanPrice = $current->products * AmazonHelper::getProductPrice();
        if($current->isTrial)
        {
            //Триальный базовый план не считается
            $currentPlanPrice = 0;
        }

        $diff = $planPrice - $currentPlanPrice;
        $calculatedDiff = $this->calculatePartialMonthlyPayment(null,null,$diff);

        $nowMonth = date("Y-m",EnvHelper::now())."-01";
        $payment = $this->createPayment(Payment::SERVICE_CHANGE_PLAN,Payment::TYPE_INSTANT,$calculatedDiff,$nowMonth);
        $plan->setRelated($payment);
        return $payment;
    }

    public function isInTrial()
    {
        $now = EnvHelper::now();
        $days = AmazonHelper::getTrialDaysNumber();
        $registered = DateTimeHelper::mysqlDateToTimestamp($this->creationDate);
        $trialEnds = $registered + 60*60*24*$days;
//        bug::drop(DateTimeHelper::timestampToMysqlDateTime($now),DateTimeHelper::timestampToMysqlDateTime($trialEnds));
        $diff = $now - $trialEnds;
        $result = $diff < 0;
        return $result;
    }

    /**
     * @param null $now
     * @return Payment
     * @throws Exception
     */
    public function createTrialPayment()
    {
        $days = AmazonHelper::getTrialDaysNumber();
        $registered = DateTimeHelper::mysqlDateToTimestamp($this->creationDate);
        $trialEnds = $registered + 60*60*24*($days);
        $sum = $this->calculatePartialMonthlyPayment($trialEnds);
        $date = date("Y-m",$trialEnds)."-01";
        $payment = $this->createPayment(Payment::SERVICE_MONTHLY_PLAN_START,Payment::TYPE_PERIODIC,$sum,$date);
        return $payment;
    }



    /**
     * @param null $now
     * @return Payment
     * @throws Exception
     */
    public function createMonthlyPayment($now = null)
    {
        if(!$now)
        {
            $now = time();
        }
        $currentMonthDate = date("Y-m-",$now)."01";
        $sum = $this->getMonthlyPaymentSum();
        $payment = $this->createPayment(Payment::SERVICE_MONTHLY_PLAN,Payment::TYPE_PERIODIC,$sum,$currentMonthDate);
        return $payment;
    }

    private function isDebtActive($now)
    {
//        return true;
        $debtIsActive = false;

        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(["userId"=>$this->getPk(), "status" => Payment::STATUS_SUCCESS]);
        $criteria->addInCondition("service",[Payment::SERVICE_DEBT,Payment::SERVICE_MONTHLY_PLAN,Payment::SERVICE_MONTHLY_PLAN_START]);
        $lastMonthlyPayment = Payment::model()->find($criteria);
        if($lastMonthlyPayment)
        {
            $lastPaymentStamp = DateTimeHelper::mysqlDateToTimestamp($lastMonthlyPayment->date);
            $nextMonth = strtotime("+1 months",$lastPaymentStamp);
            $nextMonthDate = date("Y-m",$nextMonth);
            $nowDate = date("Y-m",$now);
            $debtIsActive = $nextMonthDate == $nowDate;
        }
        return $debtIsActive;
    }

    /**
     * @return integer[]
     * @throws CException
     */
    public function findEnabledIds()
    {
        $now = EnvHelper::now();

        //It's still on trial
        $lastTrialDay = DateTimeHelper::timestampToMysqlDate($now - 60*60*24*(AmazonHelper::getTrialDaysNumber()+1));

        //Payment was for a correct service
        $goodPaymentServices = [
            Payment::SERVICE_MONTHLY_PLAN,
            Payment::SERVICE_MONTHLY_PLAN_START,
            Payment::SERVICE_DEBT,
            Payment::SERVICE_DEBT_LATE,
            Payment::SERVICE_CHANGE_PLAN,
        ];
        $goodPaymentServicesIds = join(",",$goodPaymentServices);

        //Has payment for this month or still can work in debt

        $firstDayDate = date("Y-m",$now)."-01";
        $prevFirstDayDate = date("Y-m",DateTimeHelper::mysqlDateToTimestamp($firstDayDate)- 60*60*24*3)."-01";
        //Work in debt for X days
        $day = intval(date("d",$now));
        $allowedDays = AmazonHelper::getDebtDaysNumber();
        $canWorkInDebt = $day <= $allowedDays ? "1" : "0";
//        bug::drop($day,$allowedDays,$canWorkInDebt);


        $unsubStatus = User::PAYMENT_METHOD_UNSUBSCRIBED;
        $sql ="select u.id from users as u
        left join payments as p on p.status = 1 and (p.date = '$firstDayDate' or p.date = '$prevFirstDayDate') and p.userId = u.id 
        where  u.creationDate >= '$lastTrialDay' or (p.id is not null and p.service in ($goodPaymentServicesIds) 
        and (p.date = '$firstDayDate' or (p.date = '$prevFirstDayDate' and $canWorkInDebt = 1 and u.paymentMethodVerified != $unsubStatus)))
        group by u.id";

        $ids = Yii::app()->getDb()->createCommand($sql)->queryColumn();
//        bug::drop($ids);
        return $ids;
    }

    public function canAddNewAsins()
    {
        $added = $this->getNumberOfUniqueItems();
        $limit = $this->getCurrentPlan()->products;
        $result = $added < $limit;
        return $result;
    }

    public function hasEnabledAsin($asin)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(["listingAttachments.userId" => $this->getPk(),"t.asin"=>$asin, "l.status" => Listing::STATUS_ENABLED]);
        $criteria->with = [
            "listingAttachments",
            "listingAttachments.listing",
            "listingAttachments.listing.listing" => ["alias" => "l"]
        ];
        $count =  OriginItem::model()->count($criteria);
        $result = $count > 0;
        return $result;
    }

    /**
     * Магазины которые есть у пользователя
     * @return array Shop
     */
    public function getUserShops()
    {
        $searches = $this->getShopSearches();
        $shops = [];

        foreach($searches as $row) {
            $subsearches =  $row->getSubsSearches();
            foreach ($subsearches as $task) {
                $shop = $task->shop;
                if (!in_array($shop->title, $shops)) {
                    $shops[] = $shop;
                }
            }
        }

        $shops = ActiveRecordHelper::modelsToArray($shops,null,null,null,false);
        return $shops;
    }

    /**
     * @param $productNumber
     * @return Plan
     */
    public function createPlan($productNumber)
    {
        $plan = new Plan();
        $plan->products = $productNumber;
        $plan->setRelated($this);
        return $plan;
    }

    /**
     * @param $productNumber
     * @return Plan
     */
    public function changePlan($productNumber)
    {
        $current = $this->getCurrentPlan();
        $plan = $this->createPlan($productNumber);
        if($current->products > $plan->products && !$this->hasDebt())
        {
            $plan->isDowngrade = true;
        }
        return $plan;
    }

    public function getUnsubscribeUrl($controller,$return)
    {
        $id = $this->getPk();
        $date = $this->creationDate;
        $hash = md5($id . '_' . md5('2asin4') . '_' . md5($date));
        $params = ["RETURN_URL" => urlencode($return)];
        $json = json_encode($params);
        $base64 = base64_encode($json);
//        /cancelsubscription/15/55d21e48ce7f6da40611f8fb30762980/eyJSRVRVUk5fVVJMIjoiaHR0cDpcL1wvYXNpbjI0LmNvbVwvY2FuY2VscmVnIn0=
//        /cancelsubscription/ид пользывателя/хеш/base64(  json(RETURN_URL = куда вернутса)  )
        $url = AmazonHelper::getPaymentServereUrl() . "/cancelsubscription/{$this->getPk()}/$hash/$base64";

        if (!EnvHelper::isProduction()) {
            $url  = $controller->createAbsoluteUrl("payment/mockUnsubscription",["id"=>$id,"return"=>$return]);
        }
        return $url;
    }

    public function getPlanExpirationStamp()
    {
        if($this->paymentMethodVerified != User::PAYMENT_METHOD_UNSUBSCRIBED)
        {
            return null;
        }
        $payment = $this->getLastMonthlyPayment();
        if(!$payment)
        {
            return null;
        }
        $stamp = $payment->getExpirationStamp();
        return $stamp;
    }

    /**
     * @return Payment
     */
    public function getLastMonthlyPayment()
    {
        $criteria = new CDbCriteria();
        $criteria->order = "date desc";
        $criteria->addInCondition("service", Payment::model()->getMonthlyPlanServices());
        $criteria->addColumnCondition(["status" => Payment::STATUS_SUCCESS,"userId" => $this->getPk()]);
        $payment = Payment::model()->find($criteria);
        return $payment;
    }

    public function hasActivePlan()
    {
        $hasDebt =  $this->hasDebt();
        $subscribed = $this->paymentMethodVerified != User::PAYMENT_METHOD_UNSUBSCRIBED;
        $result = !$hasDebt && $subscribed;
        return $result;
    }


}