<?php

/**
 * Базовый пользовательский класс контроллера.
 * Все контроллера наследуют от этого класса.
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
abstract class BaseController extends CController
{

    /**
     * Url до места, где лежат материалы (папки js, css, images)
     * @var String
     */
    protected $assetFolderUrl = '';

    /**
     * Ассоциативный массив подключенных пакетов материалов
     *
     * @var String[]
     */
    protected $includedAssetPackages = array();

    /**
     * Пусть к шаблону страницы
     * (обычно в этом шаблоне верстка футера и хедера)
     * @var String
     */
    public $layout = '/layouts/main';

    /**
     * Переменная $SeoMeta
     */
    public $SeoMeta = null;

    /**
     * Путь к дополнительному шаблону страницы
     * (обычно используется для профиля, чтобы меню отображать)
     *
     * @var String
     */
    public $subLayout = null;

    /**
     * Текущий пользователь
     *
     * @var User
     */
    protected $currentUser = null;

    /**
     * Класс пользователя. Должен быть перегружен в случае наследования среди пользователей.
     * @var String
     */
    protected $userClass = "User";

    /**
     * Поставщик пользователей
     * @var IUserProvider
     */
    protected $userProvider = null;

    /**
     * Флаг обновления материалов.
     * Если True, то материалы будут обновляться для каждого запроса.
     *
     * @var Boolean
     */
    protected $refreshAssetsOnEveryRequest = YII_DEBUG;

    /**
     * Данные для View
     * @var String
     */
    protected $viewData = array();

    /**
     * Ассоциативный массив параметров Javascript.
     * Эти параметры будут переданы клиентской части сайта
     *
     * @var String[]
     */
    protected $javascriptParams = array();

    /**
     * Пути к less файлам
     * @var String[]
     */
    protected $lessUrls = [];

    /**
     * Набор экшенов для делегации
     *
     * @var Mixed[]
     */
    protected $delegatedActions = null;

    /**
     * @param string $id id of this controller
     * @param CWebModule $module the module that this controller belongs to.
     */
    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);

        Yii::setPathOfAlias("lib-Yii", __DIR__ . "/..");

        if (!$this->processMaintance())
            return;

        //@todo создать нормальный метод для публикации материалов
        //Публикуем материалы
        $basePath = Yii::app()->getBasePath();
        /* @var $manager CAssetManager */
        $manager = Yii::app()->getAssetManager();
        //Для демо мы делаем симлинки, так чтобы не тормозило, когда есть Neon
        $manager->linkAssets = (EnvHelper::isDemo() || EnvHelper::isMac()) && YII_DEBUG ? true : false;
        //$manager->linkAssets = false;
        $alwaysCopy = $manager->linkAssets ? false : $this->refreshAssetsOnEveryRequest;
        $assets = $manager->publish($basePath . '/assets', false, -1, $alwaysCopy);
        $this->assetFolderUrl = $assets;

        //На локальной машине обращаемся напрямую к файлу. Прописано в .htaccess
        if (YII_DEBUG && EnvHelper::isLocal())
            $this->assetFolderUrl = $this->createAbsoluteUrl("/") . "/protected/assets";

        $this->addJavascriptParam("absoluteUrl", $this->createAbsoluteUrl("/"));

        if (!EnvHelper::hasDatabase())
            return;

        if (EnvHelper::isLocal())
        {
            //Проверяем наличие миграций
            $migrator = $this->getMigrator();
            $migrator->applyNewMigrations();
        }

        //Устанавливаем средство поиска пользователей в БД
        $this->userProvider = new SimpleUserProvider(new $this->userClass);

        $seoMeta = new SeoMeta(null, null);
        $this->setSeoMeta($seoMeta);
    }

    /**
     * Получение объекта миграции.
     * Выделено в отдельный интерфейс для перегрузки.
     *
     * @return Migrator
     */
    protected function getMigrator()
    {
        return new Migrator();
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    public function beforeAction($action)
    {
        $ret = parent::beforeAction($action);

        $this->currentUser = $this->findCurrentUser();

        return true;
    }

    /**
     * This method is invoked at the beginning of {@link render()}.
     * You may override this method to do some preprocessing when rendering a view.
     * @param string $view the view to be rendered
     * @return boolean whether the view should be rendered.
     * @since 1.1.5
     */
    public function beforeRender($view)
    {
        //Передаем параметры на сторону клиента

        /* @var $script CClientScript */
        $script = Yii::app()->getClientScript();
        $params = CJSON::encode($this->javascriptParams);
        $text = "var clientParams = $params;";
        $script->registerScript("clientParams", $text, CClientScript::POS_HEAD);

        return parent::beforeRender($view);
    }

    /**
     * Функция поиска текущего пользователя.
     *
     * @return User
     */
    public function findCurrentUser()
    {
        if ($this->getWebUser()->isGuest)
            return null;

        $user = $this->getUserProvider()->findByLogin($this->getWebUser()->name);
        return $user;
    }

    /**
     * Возвращает текущего пользователя
     *
     * @return User
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Получение текущего поставщика пользователей
     * @return IUserProvider
     */
    public function getUserProvider()
    {
        return $this->userProvider;
    }

    /**
     * Получение URL к папке где лежат папки материалов к сайту (js,css,images)
     *
     * @param Bool $absolute Должен ли Url иметь абсолютное значение
     *
     * @return String URL
     */
    public function getAssetsUrl($absolute = false)
    {

        if ($absolute)
        {
            $result = Yii::app()->getBaseUrl(true) . $this->assetFolderUrl;
            return $result;
        }
        return $this->assetFolderUrl;
    }

    /**
     * Подключение файла javascript.
     *
     * @param String $path Имя файла или путь к нему относительно папки js (например "auth/registration.js")
     * @param Int $position Позиция страницы в которой должен быть включен скрипт. Используй коснтанты <b>CClientScript::POS_END</b>.
     * @param String $package Имя пакета из которого необходимо подключить файл
     */
    public function addJavascriptFile($path, $position = null, $package = null)
    {

        if ($position == null)
            $position = CClientScript::POS_END;

        $url = $path;
        if ($package)
        {
            $url = $this->getPackageFileUrl($package, $path);
        } elseif (!StringHelper::hasSubstring($path, "http") && strpos($path, "//") !== 0)
        {
            $assetPath = StringHelper::joinPaths("/js/", $path);
            $url = $this->findSharedAssetUrl($assetPath);
        }
        Yii::app()->clientScript->registerScriptFile($url, $position);
    }

    /**
     * Осуществляет поиск материала в проекте и библиотеке.
     * Если материал не найден, кидает ошибку.
     *
     * @param String $path Путь к материалу от папки /assets
     * @return String Url по которому можно подключить материал
     * @throws Exception
     */
    protected function findSharedAssetUrl($path, $refreshOnEveryRequest = null)
    {
        $projectPath = StringHelper::joinPaths(Yii::app()->getBasePath(), "/assets/", $path);

        $root = Yii::getPathOfAlias("webroot");
        $libPath = StringHelper::joinPaths($root, "/lib-Yii/assets/home-studio", $path);

        //Ничего особенного, просто ищем сначала в проекте потом в либе, если в проекте нет
        $finalPath = file_exists($projectPath) ? $projectPath : null;
        $finalPath = $finalPath == null && file_exists($libPath) ? $libPath : $finalPath;

        if (!$finalPath)
            throw new Exception("Не удалось найти файлы по путям '$projectPath' и '$libPath'.");

        //Собственно предущий код был просто проверкой на полоумие
        //Теперь сделаем то, для чего функция нужна - опубликуем файлы и получим Url
        $url = $this->getAssetsUrl() . $path;
        //Если это , то надо ее публиковать
        if ($finalPath == $libPath)
        {
            $refresh = $refreshOnEveryRequest !== null && YII_DEBUG ? $refreshOnEveryRequest : $this->refreshAssetsOnEveryRequest;

            /* @var $manager CAssetManager */
            $manager = Yii::app()->getAssetManager();
            if ($manager->linkAssets)
                $refresh = false;
            $base = ""; //Yii::app()->createAbsoluteUrl("/");
            $relative = $manager->publish($finalPath, false, -1, $refresh);
            $url = StringHelper::joinPaths($base, $relative);
        }

        return $url;
    }

    /**
     * Подключение файла less.
     *
     * @param String $path Имя файла или путь к нему относительно папки css (например "registration/form.css")
     * @param String $package Имя пакета из которого необходимо подключить файл
     */
    public function addLessFile($path, $package = null)
    {
        if ($package)
            return $this->addCSSFile($this->getPackageFileUrl($package, $path));

        $url = $path;
        if (!StringHelper::hasSubstring($path, "http"))
        {
            $assetPath = StringHelper::joinPaths("/css/", $path);
            $url = $this->findSharedAssetUrl($assetPath);
        }

        /* @var $script CClientScript */
        $script = Yii::app()->clientScript;

        $cssUrl = str_replace(".less", ".css", $url);
        $script->registerLinkTag("stylesheet", "text/css", $cssUrl);
        $this->lessUrls[] = $url;
    }

    /**
     * Добавляет CSS на страницу в теге <style>
     *
     * @param String $path путь к CSS файлу
     * @throws Exception
     */
    public function addCss($path)
    {
        $projectPath = StringHelper::joinPaths(Yii::app()->getBasePath(), "/assets/css", $path);
        if (!file_exists($projectPath))
            throw new Exception("Не удается найти файл по пути '$projectPath'.");

        $content = file_get_contents($projectPath);
        /* @var $script CClientScript */
        $script = Yii::app()->clientScript;
        $script->registerCss("customCss", $content);
    }

    /**
     * Подключение файла css.
     *
     * @param String $path Имя файла или путь к нему относительно папки css (например "registration/form.css")
     * @param String $package Имя пакета из которого необходимо подключить файл
     */
    public function addCSSFile($path, $package = null)
    {

        $url = $path;
        if ($package)
            $url = $this->getPackageFileUrl($package, $path);

        elseif (!StringHelper::hasSubstring($path, "http"))
        {
            $assetPath = StringHelper::joinPaths("/css/", $path);
            $url = $this->findSharedAssetUrl($assetPath);
        }

        /* @var $script CClientScript */
        $script = Yii::app()->clientScript;


        $script->registerLinkTag("stylesheet", "text/css", $url);
    }

    /**
     * Подключает пакет файлов js, css и шрифтов. Актуально для библиотек, типа font-awesome.
     *
     * @param String $name Имя пакета. То есть название папки в /lib-Yii/assets/bower_components или /lib-Yii/assets/hom-studio
     * @return String Url для доступа к ассетам
     */
    private function addAssetPackage($name)
    {
        if (isset($this->includedAssetPackages[$name]))
            return $this->includedAssetPackages[$name];

        $root = Yii::getPathOfAlias("webroot");
        $libPath = StringHelper::joinPaths($root, "/lib-Yii/assets/home-studio", $name);
        $libVendorPath = StringHelper::joinPaths($root, "/lib-Yii/assets/bower_components", $name);

        $foundLib = file_exists($libPath);
        $foundVendor = file_exists($libVendorPath);
        if (!$foundLib && !$foundVendor)
        {
            throw new Exception("Не удалось найти пакет: '$name'");
        }

        if ($foundLib)
        {
            $assetPath = StringHelper::joinPaths("/", $name);
        } else
        {
            $assetPath = StringHelper::joinPaths("/../bower_components/", $name);
        }

        $url = $this->findSharedAssetUrl($assetPath, false);

        $this->includedAssetPackages[$name] = $url;
        return $this->includedAssetPackages[$name];
    }

    /**
     *  Получает Url файла из пакета
     *
     * @param String $package Название пакета
     * @param String $pathToFile Путь к файлу
     *
     * @return String Абсолютный Url файла пакета
     */
    private function getPackageFileUrl($package, $pathToFile, $absolute = true)
    {

        $packageUrl = $this->addAssetPackage($package);
        $url = StringHelper::joinPaths($packageUrl, $pathToFile);


        $root = Yii::getPathOfAlias("webroot");
        $libPath = StringHelper::joinPaths($root, "/lib-Yii/assets/home-studio", $package, $pathToFile);
        $libVendorPath = StringHelper::joinPaths($root, "/lib-Yii/assets/bower_components", $package, $pathToFile);
        if (!file_exists($libPath) && !file_exists($libVendorPath))
            throw new Exception("Не удалось найти файл пакета по путям: '$libPath' и '$libVendorPath'");

        if ($absolute)
            ; //$url = StringHelper::joinPaths (Yii::app()->createAbsoluteUrl (), $url );
        return $url;
    }

    /**
     * Отображать ли постраничную навигацию
     *
     * @var Boolean
     */
    public function needToShowBreadcrumbs()
    {
        $flag = $this->breadcrumbs != null;
        return $flag;
    }

    /**
     * Создает гиперссылку на другой экшн текущего контроллера
     *
     * @param String $action Имя экшена
     * @param String[] $params Параметры для экшена
     * @return String Адрес экшена
     */
    public function actionUrl($action, $params = array())
    {
        $route = $this->getId() . "/" . $action;
        return $this->createAbsoluteUrl($route, $params);
    }

    /**
     * Получение объекта пользовтаеля WebUser
     * @deprecated Не использовать, так как создает путаницу
     * @return CWebUser
     */
    public function getUser()
    {
        return Yii::app()->user;
    }

    /**
     * Получение объекта пользователя WebUser
     * <b>Для получения пользователя из БД, необходимо вызвать метод getCurrentUser()</b>
     *
     * @return CWebUser Посетитель сайта
     */
    public function getWebUser()
    {
        return Yii::app()->user;
    }

    /**
     * Получение объекта HTTP запроса
     * @return CHttpRequest Объект http запроса
     */
    public function getRequest()
    {
        $ret = Yii::app()->getRequest();
        return $ret;
    }

    /**
     * Возвращает маршрут настроящего контроллера, даже после вызова forward()
     *
     * @return string Маршрут 'контроллер/действие'
     */
    public function getRealRoute()
    {
        return Yii::app()->getUrlManager()->parseUrl(Yii::app()->getRequest());
    }

    /**
     * Отвечает клиенту в формате JSON.
     *
     * @param {Mixed} $data Данные для view
     * @param {Boolean} $noConvert Если true, то данные не конвертируются в JSON
     */
    public function jrender($data, $noConvert = false)
    {
        $shouldPrettyPrint = true;
        $pretty = $shouldPrettyPrint ? JSON_PRETTY_PRINT : 0;
        if (!$noConvert)
            $response = json_encode($data,$pretty);
        else
            $response = $data;

        $this->layout = false;
        Debug::disableWebLog();
        header('Content-type: application/json');
        echo $response;
        Yii::app()->end();
    }

    /**
     * Установка основного шаблона для контроллера
     *
     * @param {String} $path Название layout файла или null
     */
    public function setLayout($path)
    {

        //ну не пиздец ли? Yii, нормально же общались
        if ($path === null)
            $this->layout = false;
        else
            $this->layout = $path;
    }

    /**
     * Установка дополнительного шаблона для контроллера
     * (например разные меню в различных личных кабинетах)
     *
     * @param {String} $path Название layout файла или null
     */
    public function setSubLayout($path)
    {

        $this->subLayout = $path;
    }

    /**
     * Возвращает расширение, управляющее почтой
     *
     * @return PHPMailer Обработчик почты
     */
    public function getMailer()
    {
        return self::getBasicMailer();
    }

    /**
     * Отображает сообщение пользователю или ошибку действия.
     * Функция имеет отношение к механизму ActionErrors.
     *
     * <b>example:</b>
     * <pre>
     *  $result = $user->upgradeToPremium();
     * 	$this->setShowActionMessage($result,$user,"Account has been succesfuly upgraded");
     * </pre>
     * @see ActionErrorsBehaviour
     *
     * @param Bool $result Результат действия
     * @param ActiveRecord $model Модель, которая выполняла действие
     * @param String $succesText Текст сообщение об успешном выполнении операции
     * @return Bool Результат действия. То есть то, что было в параметре $result. Удобно для if().
     */
    public function showActionMessage($result, $model, $succesText = "Complete")
    {
        $err = $model->getActionError();
        return $this->showErrorOrSuccessMessage($result, $err, $succesText);
    }

    /**
     * Отображает сообщение об успехе пользователю <br>
     * <b>Для корректной работы функции необходимо убедиться, что в верстке данные сообщения отображаются</b>
     *
     * @param String $message Текст сообщения
     */
    public function showSuccessMessage($message)
    {
        $this->setFlash("success", $message);
    }

    /**
     * Отображает сообщение об ошибке пользователю <br>
     * <b>Для корректной работы функции необходимо убедиться, что в верстке данные сообщения отображаются</b>
     *
     * @param String $message Текст сообщения
     */
    public function showErrorMessage($message)
    {
        $this->setFlash("danger", $message);
    }


    /**
     * Добавляет пользователю флеш уведомление
     *
     * @param $type Тип сообщения
     * @param $message Сообщение
     */
    public function setFlash($type, $message)
    {
        Yii::log("$message");
        $log = new ActionLog();
        $log->type = $type;
        $log->text = $message;
        //password protection
        $copy = $_REQUEST;
        array_walk_recursive($copy,function(&$value,$key) {
            if(StringHelper::hasSubstrings($key,["passw"],false,true))
            {
                $value = "****";
            }
        });

        $log->requestData = print_r($copy, 1);
        $log->serverData = print_r($_SERVER, 1) . "\n" . print_r($_COOKIE, 1) . "\n" . print_r($_SESSION, 1);
        $log->backtrace = var_export(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),1);
        if(isset($_SERVER["REQUEST_URI"]))
        {
            $log->url = $_SERVER["REQUEST_URI"];
        }

        //Adding user
        if ($this->getCurrentUser()) {
            $isAdmin = is_subclass_of($this->getCurrentUser(), get_class(new SimpleAdmin())) || get_class($this->getCurrentUser()) == get_class(new SimpleAdmin());
//            bug::drop(get_class($this->getCurrentUser()),$isAdmin);
            if ($isAdmin)
            {
                $log->adminId = $this->getCurrentUser()->getPk();
            }
            else {
                $log->userId = $this->getCurrentUser()->getPk();
            }
        }

        //Trying to save
        try {
            $saved = $log->save();
            if (!$saved && EnvHelper::isLocal()) {
                bug::DroP($log);
            }
        } catch (Exception $e) {

        }
        $this->getWebUser()->setFlash($type, $message);
    }



    /**
     * Принимает неограниченное количество моделей и назначает им параметры стандартным образом.
     *
     * @param ActiveRecord $model Модель
     * @return boolean True, если хотя бы одной модели были назначены параметры
     */
    public function setParamsToModels()
    {
        $args = func_get_args();
        $result = false;
        /* @var ActiveRecord $model */
        foreach ($args as $model)
        {
            $params = $this->getRequest()->getParam(get_class($model), null);
            if ($params !== null)
            {
                $model->setAttributes($params);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Принимает параметр и неограниченное количество моделей и
     * возвращает ошибку действия или неизвестную ошибку
     *
     * @param boolean $useFieldErrors Если true, то в случае отсутствия ошибок действий, вернет ошибку поля
     * @param ActiveRecord $model... Модель
     * @return String Текст ошибки. Ошибка возвращается всегда.
     */
    protected function getActionErrorFromModels($useFieldErrors = true)
    {
        $args = func_get_args();
        $useFieldErrors = array_shift($args);

        foreach ($args as $model)
        {

            /* @var $model ActiveRecord */
            if ($model->hasActionErrors())
            {
                //bug::drop(get_class($model),$model->getActionErrors());
                return $model->getActionError();
            }
        }

        if ($useFieldErrors)
            foreach ($args as $model)
                if ($model->hasErrors())
                    return $model->getFirstError();

        Yii::log("No error was found in models");
        $error = $args[0]->getActionError();
        return $error;
    }

    /**
     * Принимает неограниченное количество моделей, назначает им параметры стандартным образом
     * и сохраняет их в базе данных.
     * Сохранение транзакционное - либо все сохраняются, либо никого.
     *
     * @param ActiveRecord $model Модель
     * @return boolean True, если все модели сохранились
     */
    protected function setParamsToModelsAndSave()
    {
        $args = func_get_args();
        $setParamsCallable = array($this, "setParamsToModels");
        call_user_func_array($setParamsCallable, $args);
        $saveCallable = array($this, "saveModels");
        $ret = call_user_func_array($saveCallable, $args);
        return $ret;
    }

    /**
     * Принимает неограниченное количество моделей и сохраняет их в базе данных
     * Сохранение транзакционное - либо все сохраняются, либо никого.
     *
     * @param ActiveRecord $model Модель
     * @return boolean True, Если все модели сохранились
     */
    protected function saveModels()
    {
        $args = func_get_args();
        $setParamsCallable = array("ActiveRecordHelper", "saveModels");
        $result = call_user_func_array($setParamsCallable, $args);
        return $result;
    }

    /**
     * Принимает неограниченное количество моделей и узнает отправлял ли для них пользователь параметры
     *
     * @param ActiveRecord $model Модель
     * @return boolean True, Если есть параметры
     */
    protected function hasParamsForModels()
    {
        $args = func_get_args();

        foreach ($args as $model)
        {
            $params = $this->getRequest()->getParam(get_class($model), null);
            if ($params !== null)
                return true;
        }
        return false;
    }

    /**
     * Отображает сообщение пользователю или ошибку.
     * Функция является сокращением для упрощения кода контроллера.
     *
     * @param Bool $result Результат действия, идентифицирующий что отображать
     * @param String $errorText Модель, которая выполняла действие
     * @param String $succesText Текст сообщение об успешном выполнении операции
     * @return Bool Результат действия. То есть то, что было в параметре $result. Удобно для if().
     */
    public function showErrorOrSuccessMessage($result, $errorText, $succesText = "Complete")
    {

        if ($result)
            $this->showSuccessMessage($succesText);
        else
        {
            $this->showErrorMessage($errorText);
        }
        return $result;
    }

    /**
     * Перенаправляет пользователя по url для роута
     *
     * @param String $route Роут Yii
     * @param String[] $params Массив параметров GET
     * @param Bool $terminate Если True, то останавливает дальнейшее выполнение приложения
     * @param Int $statusCode Статус, который будет выслан браузеру
     * @return void
     */
    public function redirectToRoute($route, $params = [],$terminate = true, $statusCode = 302)
    {
        $url = $this->createAbsoluteUrl($route,$params);
        return $this->redirect($url, $terminate, $statusCode);
    }

    /**
     * Добавляет переменную для шаблона экшена (view)
     *
     * @param Mixed $value Значение
     * @param String $name Имя переменной во вью
     */
    public function addViewData($value, $name)
    {
        $this->viewData[$name] = $value;
    }

    /**
     * Отображает шаблон на экран или возвращает его строковое представление
     *
     * @param String $view Название view (необзательно)
     * @param Array $data Данные для view
     * @param Bool $return Если True, то шаблон будет возвращен в виде строки
     * @return String Шаблон с подставленными переменными
     */
    public function render($view = null, $data = null, $return = false)
    {
        if ($view == null)
            $view = $this->getAction()->getId();

        if ($data == null)
            $data = $this->viewData;

        if ($this->subLayout == null)
            return parent::render($view, $data, $return);

        $result = parent::renderPartial($view, $data, true);
        return parent::render($this->subLayout, array("content" => $result));
    }

    /**
     * Получение объекта помошника для форм
     *
     * @return ActiveForm Помошник для создания форм с моделями
     */
    public function getFormHelper()
    {
        $helper = new ActiveForm();
        return $helper;
    }

    /**
     * Возвращает расширение, управляющее почтой
     *
     * @return PHPMailer Обработчик почты
     */
    public static function getBasicMailer()
    {
        $params = array("mailHost", "mailSMTPAuth", "mailPort", "mailUsername", "mailPassword", "mailFrom", "mailFromTitle");
        $yiiParams = Yii::app()->getParams();
        foreach ($params as $name)
        {
            if (!isset($yiiParams[$name]))
                throw new Exception("Не удалось найти параметр конфигурации Yii '$name'. Вероятно вы забыли его внести.");
        }
        /* @var $mailer PHPMailer */
        $mailer = Yii::app()->mailer;
        $mailer->ClearAddresses();
        $mailer->IsSMTP();
        $mailer->isHtml();
        $mailer->CharSet = 'utf8';
        $mailer->Host = Yii::app()->getParams()['mailHost'];
        $mailer->SMTPAuth = Yii::app()->getParams()['mailSMTPAuth'];
        $mailer->SMTPSecure = Yii::app()->getParams()['mailSMTPSecure'];
        $mailer->Port = Yii::app()->getParams()['mailPort'];
        $mailer->Username = Yii::app()->getParams()['mailUsername'];
        $mailer->Password = Yii::app()->getParams()['mailPassword'];
        $mailer->From = Yii::app()->getParams()['mailFrom'];
        $mailer->FromName = Yii::app()->getParams()['mailFromTitle'];

        return $mailer;
    }

    /**
     * Привязка объекта социальной сети к странице.
     * Данная страница будет красиво отображаться при шаринге в соц сетях.
     * <b>Привязать можно только один объект</b>
     *
     * @param IOpenGraphObject $object Объект реализующий интерфейс социальных сетей.
     * @param String $url Url страницы на которую будет переведен пользователь при клике на контент в соц. сети.
     */
    public function addOpenGraphMeta(IOpenGraphObject $object, $url = null)
    {
        Yii::app()->clientScript->registerMetaTag($object->getTitle(), 'og:title', null, array("property" => 'og:title'));
        Yii::app()->clientScript->registerMetaTag($object->getShortDescription(), 'og:description', null, array("property" => 'og:description'));
        if ($object->getImageUrl() != null)
            Yii::app()->clientScript->registerMetaTag($object->getImageUrl($this), 'og:image', null, array("property" => 'og:image'));
        if ($url == null)
            $url = $this->createAbsoluteUrl($this->getRequest()->getUrl());
        Yii::app()->clientScript->registerMetaTag($url, 'og:url', null, array("property" => 'og:url'));
    }

    /**
     * Добавляет параметр для передачи его на сторону клиента.
     *
     * @param String $name Имя параметра
     * @param Mixed $value Значение параметра
     */
    public function addJavascriptParam($name, $value)
    {
        $this->javascriptParams[$name] = $value;
    }

    /**
     * Получает параметр добавленный для передачи его на сторону клиента.
     *
     * @param String $name Имя параметра
     * @return Mixed Значение параметра
     */
    public function getJavascriptParam($name)
    {
        if (isset($this->javascriptParams[$name]))
        {
            return $this->javascriptParams[$name];
        }
        return null;
    }

    /**
     * Выводит блок с текстом о разработке сайта нашей студией для пользователей русского языка
     * или же оставляет копирайт клиента
     * Класс выводимого блока для прописания стилей -  copyright
     * @param String $clientCopyright Копирайт клиента
     * @return String Блок copyright
     */
    public function getCopyright($clientCopyright)
    {

        $copyright = $clientCopyright !== null ? "<div class=\"copyright\"><span>" . $clientCopyright . "</span></div>" : "";

        if (YII_DEBUG)
        {
            return $copyright;
        }
        try
        {
            if (($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])))
            {
                if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list))
                {
                    $languages = array_combine($list[1], $list[2]);
                    foreach ($languages as $n => $v)
                        $languages[$n] = $v ? $v : "1";
                    arsort($languages, SORT_NUMERIC);
                }
            };
            $language = max(array_keys($languages));
        } catch (Exception $e)
        {
            $language = null;
        }
        $russian = $language == "ru-ru" || $language == "ru";
        if ($russian)
        {
            $copyright = "<div class=\"copyright\"><a target=\"blank\" href= \"http://www.home-studio.pro/\">Developed by Home Studio | 2017</a></div>";
        };

        return $copyright;
    }

    /**
     * Замещает экшены контроллера, списком экшенов, состоящий из экшенов библиотеки
     *
     * @param String $path Путь к папке с экшенами в формате  например "admin/users". Путь отсчитывается от папки lib-Yii/actions
     * @param Mixed $params Массив параметров, передаваемых компоненту
     * @param String $index Название экшена, которое будет экшеном по-умолчанию
     */
    public function delegateActions($path, $params = [], $index = null)
    {

        EnvHelper::enableHs();

        $part = str_replace("/", ".", $path);
        $yiiPath = "root.lib-Yii.actions." . $part;
        $dir = Yii::getPathOfAlias($yiiPath);
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file)
        {
            if (StringHelper::hasSubstring($file, ".php"))
            {
                $className = str_replace([".php"], "", $file);
                $actionName = str_replace(['action'], "", strtolower($className));

                $arr = [];
                $arr['class'] = $yiiPath . "." . $className;
                $arr['layout'] = $this->layout;
                $arr['view'] = $yiiPath . ".views." . strtolower($actionName);

                foreach ($params as $key => $value)
                {
                    $arr[$key] = $value;
                }

                $result[$actionName] = $arr;
            }
        }
        if ($index != null)
        {
            $result["index"] = $result[$index];
        }
        $this->delegatedActions = $result;
        return $result;
    }

    /**
     * Получение параметра Yii
     *
     * @param String $name Имя параметра
     * @return String Значение параметра
     * @throws Exception
     */
    public function getYiiParam($name)
    {
        $params = Yii::app()->params;

        if (!isset($params[$name]))
            throw new Exception("Не обнаружен параметр Yii '$name'");
        return $params[$name];
    }

    /**
     * Проверяет является ли вебсайт общедоступным.
     * Если веб-сайт недоступен, то зайти могут только админы
     */
    public function processMaintance()
    {
        $manager = new MaintenanceManager();
        $result = $manager->process($this);
        return $result;
    }

    /**
     * Перевод фразы на текущий язык сайта
     *
     * @param String $name Фраза в словаре.
     * @param String $category Словарь из которого брать фразу
     * @return String Переведенная фраза
     */
    public function t($name, $category = "main")
    {
        //$com = Yii::app()->getMessages();
        //bug::Drop($com,$com->getLanguage(),$com->translate($type,$name),$com->translate("main","test2","ru_ru"));
        //bug::drop(Yii::app()->getCoreMessages());
        $result = Yii::t($category, $name);
        return $result;
    }

    /**
     * Для заполнения и получения тегов
     *
     * @param передается объект типа ISeoMeta
     */
    public function setSeoMeta($SeoMeta = null)
    {
        $this->SeoMeta = $SeoMeta;
        $meta = $this->SeoMeta;

        if ($SeoMeta !== null)
        {

            $meta->description;
            $meta->keywords;
            return $meta;
        }
        return $meta;
    }

    /**
     * Получение данных о SEO оптимизации для страницы.
     *
     * @return ISeoMeta|null Объект возвращающий информацию для поисковиков
     */
    public function getSeoMeta()
    {
        return $this->SeoMeta;
    }

    /**
     * Перегрузка метода для использования контроллеров из либы.
     * Если не перегрузить данный метод, то приоритет имеют эшены объявляенные на классе
     */
    public function createAction($actionID)
    {
        if ($this->delegatedActions != null)
        {
            $actionID = $actionID == '' ? "index" : $actionID;

            $action = $this->createActionFromMap($this->actions(), $actionID, $actionID);
            if ($action !== null && !method_exists($action, 'run'))
            {
                throw new CException(Yii::t('yii', 'Action class {class} must implement the "run" method.', array('{class}' => get_class($action))));
            }

            return $action;
        }

        return parent::createAction($actionID);
    }

    /**
     * Получение текущих экшенов контроллера.
     * Данный метод расширен, чтобы позволять расширять экшены за счет функции delegateActions()
     *
     * @return Mixed[]
     */
    public function actions()
    {
        if ($this->delegatedActions != null)
        {
            return $this->delegatedActions;
        }
        return parent::actions();
    }

    /**
     * Ассоциативный массив данных для view
     *
     * @return Mixed
     */
    public function getViewData()
    {
        return $this->viewData;
    }

    /**
     * Создает массив на основе $_GET, затем добавляет и удаляет из него параметры.
     * Функция полезна для передачи параметров в URL, например при постраничной навигации
     *
     * @param String[] $toAdd Ассоциативный массив параметров, которые необходимо добавить
     * @param String[] $toRemove массив параметров, которые необходимо удалить
     * @return String[] Ассоциативный массив параметров
     */
    public function getGet($toAdd = [],$toRemove = [])
    {
        $params = $_GET;
        foreach($toRemove as $key)
        {
            unset($params[$key]);
        }
        foreach($toAdd as $key=>$value)
        {
            $params[$key] = $value;
        }
        return $params;
    }
}
