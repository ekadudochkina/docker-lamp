<?php

/**
 * Загружает содержимое базы данных в локальную
 *
 * @package Hs\Shell\Commands
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */

class DbCommand extends LibCommand implements \Hs\Logs\ILogger
{

    protected $filename = __DIR__ . "/dump.sql";
    protected $modes = ['production', "demo","local","production_local"];
    /**
     * @var ConsoleParamManager
     */
    protected $params;

    protected function beforeAction($action, $params)
    {
        $this->params  = new ConsoleParamManager($params[0]);
        if($this->params->hasFlag("-s"))
        {
            Debug::disableProfilingInConsole();
        }
//        bug::DroP($action,$params);
        $ret =  parent::beforeAction($action, $params);
        return $ret;
    }

    /**
     * Выводит помощь
     *
     * @param String[] $args Аргументы
     */
    public function actionIndex($args)
    {
        echo $this->getHelp();
    }

    /**
     * Создает код миграции для модели
     *
     * @param String[] $args Аргументы функции (имя модели)
     */
    public function actionGenerateMigrationFor($args)
    {
        $manager = new ConsoleParamManager($args);
        $modelName = $manager->getParamAt(0);
        $model = ActiveRecordHelper::createModelInstance($modelName);
        $models = $model->findAll();
        //bug::Drop($models);
        $result = "";
        $i =0;
        foreach($models as $model)
        {
            $code = $this->generateMigrationCodeForModel($model,++$i);
            $result .= $code."\n\n";
        }
        echo $result;
    }


    /**
     * Загрузка дампа базы данных в базу данных обозначенную в $args[0]
     *
     * @param String[] $args Аргументы команды
     * @throws Exception
     */
    public function actionUpload($args)
    {
        if(!file_exists($this->filename))
        {
            throw new Exception("Файл {$this->filename} не существует. Скачайте сначала базу данных");
        }
        $this->log("Используем скачанный файл '{$this->filename}'");

        if (isset($args[0]))
        {
            $toMode = $this->parseMode($args[0]);
        }
        else
        {
            $toMode = EnvHelper::getCurrentMode();
        }

        $this->log("Подключаемся к базе '$toMode'");
        $connectionTo = $this->getConnection($toMode);
        if ($connectionTo === null)
        {
            return $this->log("Не удалось установить соединение c '$toMode'", CLogger::LEVEL_ERROR);
        }
        $this->checkProductionForMode($toMode);

        $this->log("Загружаем базу данных");
        $this->uploadDb($connectionTo);
        $this->log("Сделано");
    }


    public function actionDownload($args)
    {
        $this->log("Удаляем временные файлы");
        $this->cleanUp();
        $params = new ConsoleParamManager($args);


        if (!isset($args[0]))
        {
            $this->log("Необходимо предоставить название режима", CLogger::LEVEL_ERROR);
            return;
        }


        $fromMode = $this->parseMode($args[0]);
        $this->log("Подключаемся к базе '$fromMode'");
        $connectionFrom = $this->getConnection($fromMode);
        if ($connectionFrom === null)
        {
            $this->log("Не удалось установить соединение c '$fromMode'", CLogger::LEVEL_ERROR);
            return;
        }

        $limits = null;
        $isLimited = $params->hasFlag("-l");
        if($isLimited)
        {
            $limits = $this->getLimits();
        }
        $this->log("Скачиваем базу в '{$this->filename}'");
        $this->downloadDb($connectionFrom,$limits);
        $this->log("Скачивание БД завершено");
    }

    /**
     * Загружает базу данных удаленного сервера в локальную БД проекта
     *
     * @param String[] $args
     */
    public function actionCopy($args)
    {
        if (!isset($args[0]))
        {
            $this->log("Необходимо предоставить название режима", CLogger::LEVEL_ERROR);
            return;
        }
        if (isset($args[1]))
        {
            $toMode = $this->parseMode($args[1]);
        } else
        {
            $toMode = EnvHelper::getCurrentMode();
        }
        $fromMode = $this->parseMode($args[0]);
        $this->log("Загружаем данные с '$fromMode' на '$toMode' ");
        $this->log("Запускаем скачиваение");
        $this->actionDownload([$fromMode]);
        $this->log("Запускаем загрузку БД");
        $this->actionUpload([$toMode]);
        $this->log("Выгрузка БД завершена");
    }

    /*
     * Запуск миграций команды
     * 
     * @param String[] $args Массив аргументов
     */
    public function actionMigrate($args)
    {
        $this->log("Migrating");
        
        if (EnvHelper::isProduction())
        {
            $result = $this->prompt("База данных в режиме продакшен. (Warning: Production Mode Detected) Continue? yes / no", false);
            if ($result !== "yes")
            {
                $this->log("Stopping task");
                return;
            }
        }

        $this->log("Applying migrations");
        $migrator = new Migrator(null,$this);
        $migrator->applyNewMigrations();

        $this->log("Migration applied");
    }

    /**
     * Загрузка БД в локальную и запуск миграции
     * @param String[] $args
     */
    public function actionCheckMigrate($args)
    {
        $this->actionUpload($args);
        $this->actionMigrate($args);
    }

    /**
     * Перезапуск миграций
     *
     * @param String[] $args Массив аргументов
     */
    public function actionRemigrate($args)
    {
        $this->log("Reaplying migrations:");
        if (EnvHelper::isProduction())
        {
            $result = $this->prompt("База данных в режиме продакшен. (Warning: Production Mode Detected) Continue? yes / no", false);
            if ($result !== "yes")
            {
                $this->log("Stopping task");
                return;
            }
        }
        $this->log("Checking connection");
        $dbConfig = $this->getDbConfig(EnvHelper::getCurrentMode());


        $this->log("Deleting Db");

        //bug::drop($dbName);
        try
        {
            $db = $this->openDbConnection($dbConfig);
            $dbName = $db->dbname;
            $db->createCommand("DROP database `$dbName`")->execute();
        } catch (Exception $e)
        {
            $this->log("Failed to drop DB");
        }


        $this->createDb($dbConfig["dbname"]);

        $this->actionMigrate($args);

        $this->log("Reapplying migrations done");
    }

    /**
     * Создает базу данных
     *
     * @param String $dbName Имя базы данных
     */
    public function createDb($dbName)
    {
        $dbConfig = $this->getDbConfig(EnvHelper::getCurrentMode());
        $dbConfig["dbname"] = null;
        $db = $this->openDbConnection($dbConfig);
        $this->log("Creating new db");
        $sql = "CREATE DATABASE `$dbName` DEFAULT CHARACTER SET utf8 ";
        $db->createCommand($sql)->execute();
        $sql = "USE `$dbName`";
        $db->createCommand($sql)->execute();
        $db->getSchema()->refresh();
    }

    /**
     * Скачивание удаленной базы данных
     *
     * @param DbConnection $db Конфигурация БД
     * @param Sting $limits[] Лимиты для таблиц
     * @throws CException
     */
    protected function downloadDb($db,$limits = null)
    {
        EnvHelper::enableComposer();
        $connectionString = $db->connectionString;
        $username = $db->username;
        $password = $db->password;
//        bug::drop($connectionString,$username,$password);
        $dumper = new Hs\Db\MysqlDumpWithLog($db,$connectionString, $username, $password,[ 'complete-insert' => true]);

        if($limits)
        {
            $wheres = [];
            foreach($limits as $table => $limit)
            {
                $this->log("Cooking limits for $table");
//                $sql = "select COUNT(*) from `$table`";
//                $count = $db->createCommand($sql)->queryScalar();
//                $offset = $count - $limit;
                $sql2 = "select id from `{$table}` ORDER BY id DESC LIMIT 1 OFFSET $limit";
                $id = $db->createCommand($sql2)->queryScalar();
                if(!$id) {
                    $this->log("No id skip");
                    continue;
                }
                $wheres[$table] = "id > $id";
//                bug::droP($count,$offset,$limit,$db->createCommand("select count(*) from $table where id > $id",$id)->queryScalar());
            }
            $dumper->setTableWheres($wheres);
            $dumper->setTableLimits($limits);
        }
        $dumper->start($this->filename);
    }

    /**
     * Парсит режим проекта из параметра консоли
     *
     * @param String $str название режима
     * @return String константа режима
     * @throws Exception
     */
    protected function parseMode($str)
    {
        if(!in_array($str,EnvHelper::getAllModes()))
        {
            throw new Exception("'$str' не является режимом работы приложения. Пример режима: 'production' ");
        }
        return $str;
    }

    /**
     * Очистка временных файлов
     */
    protected function cleanUp()
    {
        //Удаляем файл дампа
        if (file_exists($this->filename))
            unlink($this->filename);
    }

    /**
     * Загружает скачанную базу данных в другую базу данных
     *
     * @param CDbConnection $db Подключение к БД, в которую будет загружаться скачанная база
     */
    protected function uploadDb($db)
    {
        if($this->params->hasFlag("-f")) {
            $this->log("Увеличиваем размер пакетов MySQL и памяти PHP");
            ini_set("memory_limit", "-1");
            $db->createCommand("SET GLOBAL max_allowed_packet=1073741824")->execute();
            $db = $this->getConnection("local");
        }

        //bug::drop($mode);
        //Это уже не сработает с большими файлами
//        $sql = file_get_contents($this->filename);
//        $statements = $this->splitSql($sql);
        $dbName = $db->dbname;
        //bug::drop($dbName);
        $db->createCommand("DROP database `$dbName`")->execute();
        $sqlCreate = "CREATE DATABASE `$dbName` DEFAULT CHARACTER SET utf8 ";
        $db->createCommand($sqlCreate)->execute();
        $sqlUse = "USE `$dbName";
        $db->createCommand($sqlUse)->execute();


        //Считаем строки
        $handle = fopen($this->filename,"r");
        $count = 0;
        while($line = fgets($handle))
        {
            $count += strlen($line);
        }
        rewind($handle);

        $lines = [];
        $percentage = 0;
        while($line = fgets($handle))
        {
            if(strpos($line,"INSERT") === 0)
            {
                $percentage = $this->executeSqlAndShowPercentage($lines,$percentage,$count);
                $lines = [];
            }
            $lines[] = $line;
        }
        fclose($handle);
        $this->executeSqlAndShowPercentage($lines,$percentage,$count);
        //bug::drop($parts[3]);
    }

    public function executeSqlAndShowPercentage($lines,$prev,$allLetters)
    {
        $sql = join("\n",$lines);
        $now = $prev + strlen($sql);
        $percentage =$now*100 / $allLetters;
        $this->log(StringHelper::formatDecimal($percentage,2)."%");
        try {
            Yii::app()->getDb()->createCommand($sql)->query();
        }catch (Exception $ex)
        {
            //Место для брейкпоинта
            throw $ex;
        }
        return $now;
    }

    /**
     * Создает подключение к базе данных.
     *
     * @param String $mode Название режима (demo, production, local)
     * @return DbConnection Подключение к БД
     */
    public function getConnection($mode)
    {
        $conf = $this->getDbConfig($mode);
        if ($mode == Bootstraper::MODE_DEMO)
        {
            $standartDev = $conf['username'] == "root" && $conf['password'] == "";
            if ($standartDev)
            {
                $this->log("Обнаружены стандартные настройки для Demo сервера", CLogger::LEVEL_WARNING);
                $msg = "Введите имя сервера, например dev или dev1: ";
                $devServer = $this->prompt($msg);
                if (!$devServer)
                {
                    return null;
                }

                $conf = $this->fixDevServerConfig($devServer, $conf);
            }
        }

        $connection = $this->openDbConnection($conf);
        return $connection;
    }

    /**
     * Правка конфигов дев сервера для создания удаленного подключения.
     * Необходимо потому, что по-умолчанию подключение к дев серверу локальное.
     *
     * @param String $name Название дев сервера, например dev1
     * @param String[] $conf Конфигурация базы данных
     * @return String[] Обновленная конфигурация
     */
    protected function fixDevServerConfig($name, $conf)
    {
        $number = str_replace("dev", "", strtolower(trim($name)));
        if ($number == "")
            $number = 0;

        $host = "dev.home-studio.pro";
        $port = "3306" . $number;
        $conf['host'] = $host;
        $conf['port'] = $port;
        $this->log("Используем конфиг:");
        $this->log(print_r($conf, 1));
        return $conf;
    }

    /**
     * Создает код миграции для модели
     *
     * @param ActiveRecord $model Модель
     * @param String $suffix Суффикс после имени
     * @return string
     */
    public function generateMigrationCodeForModel($model,$suffix = "")
    {
        $strs = [];
        $strs[] = $this->generateVar($model,$suffix)." = new ".get_class($model)."();";
        $attributes = $model->getAttributes();
        //bug::drop($attributes);
        foreach($attributes as $attr => $value)
        {
            $value = CodeGenHelper::valueToString($model->$attr);
            $strs[] = $this->generateVar($model,$suffix)."->$attr = $value;";

        }
        $strs[] = '$this->saveModel('.$this->generateVar($model,$suffix).");";
        $result = join("\n",$strs);
        return $result;

    }

    /**
     * Создает имя переменной для модели
     *
     * @param ActiveRecord $model Модель
     * @param String $suffix Суффикс после имени
     * @return string
     */
    public function generateVar($model, $suffix)
    {
        $class = get_class($model);
        $result = "$".lcfirst($class).$suffix;
        return $result;

    }

    public function splitSql($sql)
    {
         $parts = explode("\n\n", $sql);
         if($this->params->hasFlag("-f"))
         {
             return $parts;
         }
         $result = [];
         foreach($parts as $part)
         {
             if(StringHelper::hasSubstring($part,"\nINSERT INTO"))
             {
                $smallerParts = $this->splitInsert($part);
                $result = array_merge($result,$smallerParts);
                continue;
             }
             $result[] = $part;
         }
         return $result;
    }

    public function splitInsert($text)
    {
        $parts = explode("\n",$text);
        $check = "INSERT INTO";
        $result = [];
        foreach($parts as $part)
        {
            //Если это не строка с INSERT
            if(strpos($part,$check) !== 0)
            {
                $result[] = $part;
                continue;
            }
            //Если это маленький INSERT то не делаем сплит
            if(!StringHelper::hasSubstring($part,"),("))
            {
                $result[] = $part;
                continue;
            }
            $chunks = explode("),(",$part);
            $this->log("Splitting insert into ".count($chunks)." parts");

            //Преобразуем первый элемент
            $temp = explode(" VALUES (",$chunks[0]);
            $start = $temp[0];
            $chunks[0] = $temp[1];

            //Удаляем точку с запятой
            $last = $chunks[count($chunks)-1];
            $chunks[count($chunks)-1] = substr($last,0, strlen($last)-2);

            //Создаем команды
            foreach($chunks as $chunk)
            {
                $sql = "$start VALUES ($chunk);";
                $result[] = $sql;
            }
//            bug::drop($result);
        }

        return $result;
    }

    public function checkProductionForMode($toMode)
    {
        if (EnvHelper::isProduction($toMode))
        {
            $msg = "Вы пытаетесь зались базу данных на 'production'. Это может быть опасно.";
            $this->log($msg, CLogger::LEVEL_ERROR);
            $result = $this->confirm("Вы уверены на 100%?");
            if (!$result)
            {
                return $this->log("Выходим");
            }
        }
    }

    public function actionUp($args)
    {
        foreach($args as $arg)
        {
            $params = new ConsoleParamManager([$arg]);
            $number = $params->getParamAt(0);

            $path = Yii::getPathOfAlias("application.migrations");
            $migrator = new Migrator($path,$this);
            $migration = $migrator->getMigrationWithNumber($number);
            if(!$migration)
            {
                throw new Exception("Can't find migartion with number '$number'");
            }
            $migration->execute();
        }
    }

    public function actionDown($args)
    {
        $params = new ConsoleParamManager($args);
        $number = $params->getParamAt(0);

        $path = Yii::getPathOfAlias("application.migrations");
        $migrator = new Migrator($path,$this);
        $migration = $migrator->getMigrationWithNumber($number);
        if(!$migration)
        {
            throw new Exception("Can't find migartion with number '$number'");
        }
        $name = get_class($migration);
        $row = Yii::app()->getDb()->createCommand("SELECT * from migrations WHERE name = '$name'")->queryRow();
        if(!$row && !$params->hasFlag("-f"))
        {
            throw new Exception("Couldn't find migration '$name' on migration table. Use -f flag to rollback anyway");
        }
        $migration->rollback();
        if($row)
        {
            $this->log("Deleting migration '$name''");
            Yii::app()->getDb()->createCommand("DELETE FROM migrations WHERE name = '$name'")->execute();
        }
        $this->log("Done");

    }

    protected function getLimits()
    {
        $more = 10000;
        $normal = 1000;
        $less = 100;
        $limits = [
            "categoryloadingdata" => $normal,
            "foundcategories" => $more,
            "categorysellersloaddirections" => $less,
            "loadcategorypagedata" => $more,
            "foundcategoryproducts" => $more,
            "loadproductsellersdata" => $more,
            "foundproductsellers" => $more,
            "foundsellers" => $more,

            "itemloadtaskdata" => $less,
            "infotaskdata" => $less,

            "buyboxdata" => 50000,
            "keyworddata" => $more,
            "positiondata" => $less,
            "amazonproductcategorypositions"=>$normal,
            "ratingreviewdata" => $less,
            "tasks" => $more,

            "notifications" => $normal,
            "proxies" => $more,
            "actionlogs" => $less,
        ];
        return $limits;
    }

}
