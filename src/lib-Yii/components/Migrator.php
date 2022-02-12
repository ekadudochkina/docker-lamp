<?php
/**
 * Объект применяющий миграции к базе данных.
 *
 * Проверяет наличие новых классов миграций в папке миграций и соотносит их с данными в БД. В таблице migrations.
 * Так как объем работы большой, миграции проверяются не всегда. Когда проверка завершена,
 * создается файл в котором хранится информация о текущей ревизии Git и последнем времени выполнения миграции.
 * Если текущая ревизия Git совпадает с данными в этом файле, то проверки миграций не происходит.
 * 
 * Если гита нету, то миграции будут проверяться всегда!
 * 
 * @see DbMigration
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Db\Migrations
 */
class Migrator {
    
    /**
     * Путь к директории миграций
     * @var String
     */
    protected $pathToMigrationDirectory;
    /**
     * Путь к файлу, куда заносится информация о текущей миграции
     * @var String
     */
    protected $pathToCurrentMigrationInfo;
    
    /**
     * Путь к дирректории Git
     * @var String
     */
    protected $pathToGit;

    /**
     * Лог
     * @var \Hs\Logs\ILogger
     */
    protected $logger;
    
    /**
     * @param String $migrationsPath Путь к папке с миграциями
     */
    public function __construct($migrationsPath = null, \Hs\Logs\ILogger $logger = null){
	$this->pathToMigrationDirectory =  $migrationsPath ? $migrationsPath : Yii::app()->basePath.'/migrations';
	$this->pathToCurrentMigrationInfo = Yii::app()->basePath.'/runtime/migration';
	$this->pathToGit = Yii::getPathOfAlias('webroot').'/.git';
        $this->logger = $logger ? $logger : new \Hs\Logs\EmptyLogger();
    }
    
    /**
     * Удаление информации о проверках миграций. После этого миграции будут проверены заново.
     */
    public function removeCache()
    {
	if(file_exists($this->pathToCurrentMigrationInfo)){
	    unlink($this->pathToCurrentMigrationInfo);
	}
    }
    
    /**
     * Проверка, необходимо ли искать новые миграции.
     * Проверка осуществляется фиксацией информации о последнем коммите в папке runtime.
     * 
     * @return boolean
     */
    protected function isNeedToCheckMigrations(){

	if(!$this->hasMigrationTable())
        {
	   $this->createMigrationTable();
            return true;
        }
        
	if(!$this->hasGit())
	    return true;
	if(!file_exists($this->pathToCurrentMigrationInfo))
	    return true;
	$last_revision = $this->getLastRevision();
	$current_revision = $this->getCurrentRevision();
	if($last_revision === $current_revision)
	    return false;
	return true;
    }
    
    /**
     * Проверяет наличие таблицы миграфии в базе данных.
     * Если ее нет, то миграции проверять нет смысла
     */
    protected function hasMigrationTable(){
	 //$tables = Yii::app()->getDb()->createCommand("SHOW tables;")->queryColumn();
	 $tables = Yii::app()->getDb()->getSchema()->tableNames;
	 $result = in_array("migrations", $tables);
	 return $result;
    }
    
    /**
     * Создание таблицы для миграций
     */
    protected function createMigrationTable(){
        
        $db = Yii::app()->getDb();
        $cmd = $db->createCommand();
        if(EnvHelper::isSQLite())
        {
            $cols = array();
            $cols['id'] = "INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL";
            $cols['name'] = "varchar(100) NOT NULL";
            $cols['creationDate'] = "datetime NOT NULL";
            $cmd->createTable("migrations", $cols,"");
        }
        else
        {
            $sql = "CREATE TABLE `migrations` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL DEFAULT '',
            `creationDate` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            Yii::app()->getDb()->createCommand($sql)->execute();
        }

	//Yii::app()->getDb()->createCommand($sql)->execute();
    }
    /**
     * Проверяет наличие Git репозитория
     * @return boolean 
     */
    protected function hasGit(){
	if(file_exists($this->pathToGit))
	    return true;
	return false;
    }
     /**
     * Получение кода предыдущей ревизии мигратора
     * 
     * @return String Хэш код ревизии
     */    
    protected function getLastRevision(){
	$content = file_get_contents($this->pathToCurrentMigrationInfo);
	$parts = explode("|",$content);
	$revision = $parts[0];
	return $revision;
    }
    
    /**
     * Установка(сохранение) текущей ревизии мигратора
     */   
    protected function setLastRevision(){
	$hash = $this->getCurrentRevision();
	$time = DateTimeHelper::timestampToMysqlDateTime();
	$content = join("|",array($hash, $time));
	file_put_contents($this->pathToCurrentMigrationInfo, $content);
    }
    
    /**
     * Очищает данные о текущей ревизии мигратора
     */
    public function clearCurrentRevision()
    {   
        file_put_contents($this->pathToCurrentMigrationInfo, "");
    }
    
    /**
     * Получение кода текущей ревизии мигратора
     * 
     * @return String Хэш код ревизии
     */
    protected function getCurrentRevision(){
	$pathToGit = $this->pathToGit;
	$content = file_get_contents($pathToGit.'/HEAD');
	$content = trim($content);
	$path_to_branch = explode(" ",$content)[1];
	$combinedPath = $pathToGit."/".$path_to_branch;
	$file = fopen($combinedPath,"r");
	$revision_hash = file_get_contents($combinedPath);
	$revision_hash = trim($revision_hash);
	return $revision_hash;
    }
    
    /**
     * Получение списка всех миграций
     * 
     * @return String[] Список имен классов миграций
     */
    protected function getMigrations(){
	//Раньше я использовал подгрузку классов, но сейчас перешел на имена файлов
	//Так можно убедиться, что имя класса соответсвует имени файла
	$files = array();
	$directory = opendir($this->pathToMigrationDirectory);
	while($file = readdir($directory)){
	    if($file == ".." || $file == "." || $file == ".gitkeep")
		continue;
	    $file = basename($file,".php");
	    array_push ($files, $file);
	}
	closedir($directory);
	return $files;
    }
    
    /**
     * Получение списка примененных миграций
     * 
     * @return String[] Список имен классов миграций
     */
    public function getAppliedMigrations(){
        $db = Yii::app()->getDb();
        //bug::dump($db);
	$applied = $db->createCommand("SELECT name FROM migrations")->queryColumn();
	return $applied;
    }
    
    /**
     * Получение списка новых добавленных миграций
     * 
     * @return String[] Список имен классов миграций
     */
    protected function getNewMigrations(){
	$migrations = $this->getMigrations();
	$appliedMigrations = $this->getAppliedMigrations();
	$newMigrations = array_diff($migrations, $appliedMigrations);
	return $newMigrations;
    }
    
    /**
     * Применение новых миграций, если это необходима.
     * Функция ленивая, поэтому можно вызывать всегда.
     * @throws Exception
     */
    public function applyNewMigrations(){
        Yii::app()->getDb()->getSchema()->refresh();
	if($this->isNeedToCheckMigrations()){
            EnvHelper::enableHs();
            
            //подмена ДБ, чтобы считать запросы
            $oldDb = Yii::app()->getDb();
            $newDb = new Hs\Db\MigratorDbConnection($oldDb);
            $newDb->init();
            $newDb->enableParamLogging = true;
            $newDb->enableProfiling = true;
            $newDb->setActive(true);
            Yii::app()->setComponent("db",$newDb,false);
            //bug::drop(Yii::app()->getDb());
            
            $this->clearCurrentRevision();
            if(!EnvHelper::isSQLite())
                Yii::app()->getDb()->createCommand("SET NAMES utf8;")->execute();
	    
	    $migrationNames = $this->getNewMigrations();
	    $newMigrations = $this->createMigrationObjects($migrationNames);  
	    $this->checkMigrations($newMigrations);
	    
            
            try{
                foreach($newMigrations as $migration)
                    $this->applyMigration($migration);
	    
            } catch (Exception $ex) {
                
                if(EnvHelper::isDemo() || EnvHelper::isProduction())
                {
                    $this->sendFailEmail();
                }
                //Подмена дб обратно
                Yii::app()->setComponent("db",$oldDb,false);
                $newDb->resetCounters();
                $newDb->setActive(false);
                throw $ex;
            }
            
                //Подмена дб обратно
                Yii::app()->setComponent("db",$oldDb,false);
                $newDb->resetCounters();
                $newDb->setActive(false);
            
            
            
            
	    if($this->hasGit())
		$this->setLastRevision();
	}
    }
    
    /**
     * Создание объектов миграций и сортировка
     * @param String[] $migrationNames Список имен миграции
     */
    protected function createMigrationObjects($migrationNames){
	$arr = array();
	foreach($migrationNames as $name ){
	    if(!class_exists($name))
		throw new Exception("Класс $name не существует. Вероятно имя файла миграции не соответсвует названию класса.");
	 
	    $obj = new $name();
	    array_push($arr,$obj);
	}
	usort($arr,array($this,"compareMigrations"));
	return $arr;
    }
    
    /**
     * Функция обратного вызова для сортировки миграций по номеру
     * @param DBMigration $a Миграция А
     * @param DBMigration $b Миграция Б
     * @return Number Меньше нуля если номер А меньше номера Б
     */
    public function compareMigrations($a, $b){
	$x = $a->getNumber();
	$y = $b->getNumber();
	return $x-$y;
    }
    
    /**
     * Проверка миграций на правильность
     * @param DBMigration[] $migrations Список миграции
     */
    protected function checkMigrations($migrations){
	$numbers  = [];
	foreach($migrations as $migration){
	    
	    //Проверяем правильность имени класса
	    //Правильность имени файла можно не проверять, так как это вызвало бы исключениие
	    //при создании класса
	    $class = get_class($migration);
	    $parts  = explode("_", $class);
	    if(count($parts) != 3)
		throw new Exception ("Миграция должна иметь имя '_{номермиграции}_{имямиграции}{номерзадачи}', например _01_test23 в классе $class");
	    
	     //Проверяем номер - они не должны повторяться
	    $number = $migration->getNumber();
	    if(!is_integer($number))
		throw new Exception("Номер миграции должен быть целым числом в классе $class");
	    
	    //Да и номер в классе должен соответствовать номеру файла
	    $fileNumber = explode("_",$class)[1];
	    $fileNumber = intval($fileNumber);
	    if($fileNumber !== $number)
		throw new Exception("Номер файла ($fileNumber) не соответствует номеру, который вернул класс ($number) в классе $class");
	    
	    //Проверяем дубли
	    if(in_array($number,$numbers))
		throw new Exception("Обнаружен дублирующий номер миграции: $number в миграции в классе $class");
	    $numbers[] = $number;
	}
    }
    
    /**
     * Применение миграции
     * @param DBMigration $migration Миграция
     */
    protected function applyMigration($migration){
        $this->logger->log("Applying ".get_class($migration));
        Yii::app()->getDb()->resetCounters();
        $migration->execute();
        $name = get_class($migration);
        $date = DateTimeHelper::timestampToMysqlDateTime();
        Yii::app()->getDb()->resetCounters();
        $sql = "INSERT INTO migrations VALUES (NULL,'$name','$date')";
        $qb = Yii::app()->getDb()->createCommand($sql)->execute();
    }
    
    /**
     * Отсылка уведомления, если миграции не удлось применить
     */
    protected function sendFailEmail()
    {
        $mailer = $this->getMailer();
        $mailer->AddAddress("system@home-stuido.pro");
        $mailer->Subject = "Не удалось применить миграции в проекте '".Yii::app()->name."'";
        $mailer->Body = DateTimeHelper::timestampToMysqlDateTime();
        $mailer->Send();
    }
    
    /**
     * Интерфейс получения почтового клиента
     * 
     * @return PHPMailer Объект для работы с почтой
     */
    protected function getMailer()
    {
        $mailer = BaseController::getBasicMailer();
        return $mailer;
    }

    /**
     * Получить миграцию под номером
     *
     * @param $number Номер миграции
     * @return DbMigration|null
     */
    public function getMigrationWithNumber($number)
    {
        ReflectionHelper::includeAllClasses($this->pathToMigrationDirectory,true);
        $migrations = ReflectionHelper::getSubclassesOf(DbMigration::class);
//        bug::drop($migrationSubs);
        /** @var DbMigration $migration */
        foreach($migrations as $class)
        {
            $obj = new $class;
            if($obj->getNumber() == $number)
            {
                return $obj;
            }
        }
        return null;
    }
}