<?php

/**
 * Команда для скачивания файлов проекта
 * 
 * @package Hs\Shell\Commands
 */
class DownloadCommand extends LibCommand
{

    protected $remoteProjectPath = null;

    /**
     * Запуск команды. Аргументом может принимать название дев сервера.
     * 
     * @param String[] $args Массив аргументов
     */
    public function run($args)
    {
        if (isset($args[0]))
        {
            $this->processDev($args[0]);
            return;
        }

        $host = $this->prompt("Host:");
        $port = $this->prompt("Port:");
        $user = $this->prompt("Username:");

        do
        {
            $password = $this->promptPassword("Password:");
            $ftp = $this->connect($host, $port, $user, $password);
        } while ($ftp == null);


        $this->commandCycle($ftp);
    }

    /**
     * Запрос у пользователя на ввод новой команды.
     * 
     * @param phpseclib\Net\SFTP $ftp Подключение по SSH
     */
    public function getCommand(phpseclib\Net\SFTP $ftp)
    {
        echo "\n";
        $answer = trim($this->prompt(">>:"));
        $result = explode(" ", $answer);
        $cmd = $result[0];
        $param = isset($result[1]) ? $result[1] : null;
        switch ($cmd)
        {
            case "ls" :
                $this->showDir($ftp, ".");
                break;
            case "cd" :
                $this->cd($ftp, $param);
                break;
            case "project" :
                $this->setProject($ftp);
                break;
            case "help" :
                $this->showHelp();
                break;
            case "get" :
                $this->get($ftp, $param);
                break;
            case "pwd" :
                $this->pwd($ftp);
                break;
            case "exit" :
                $this->done();
                break;
            default :
                $this->log("Команда '$cmd' не найдена", CLogger::LEVEL_WARNING);
        }
    }

    /**
     * Оотображает список файлов в директории
     * 
     * @param phpseclib\Net\SFTP $ftp Подключение по SSH
     * @param String $path Путь к директории
     */
    public function showDir(phpseclib\Net\SFTP $ftp, $path)
    {
        $raw = $ftp->nlist($path);
        //print_r($raw);
        $result = [];
        foreach ($raw as $line)
        {
            if ($line == ".." || $line == ".")
                continue;
            $result[] = $line;
        }

        $strings = join("\n", $result);
        $this->log("Директория: \n" . $strings);
    }

    /**
     * Установка директории проекта на удаленном сервере.
     * 
     * @param phpseclib\Net\SFTP $ftp Подключение по SSH
     */
    public function setProject(phpseclib\Net\SFTP $ftp)
    {
        $dir = $ftp->pwd();
        $this->remoteProjectPath = $dir;
        $this->log("Удаленный проект теперь: {$dir}", CLogger::LEVEL_WARNING);
    }

    /**
     * Завершение работы
     */
    public function done()
    {
        $this->log("Выходим");
        exit;
    }

    /**
     * Смена директории
     * 
     * @param phpseclib\Net\SFTP $ftp Подключение по SSH
     * @param String $param Название директории или путь к поддиректории
     */
    public function cd(phpseclib\Net\SFTP $ftp, $param)
    {
        $this->log("Меняем директорию на {$param}");
        $link = $ftp->readlink($param);

        $param = $link ? $link : $param;
        try
        {
            $ftp->chdir($param);
            $this->log("Текущая директория:");
            $this->log($ftp->pwd());
        } catch (Exception $e)
        {
            $msg = "Не удалось переместиться в '$param'.";
            $msg .= "Учтите, что перемещаться желательно по одной директории за раз";
            $this->log($msg, CLogger::LEVEL_ERROR);
        }
    }

    /**
     * Отображение подсказок
     */
    public function showHelp()
    {
        $strs = [];
        $strs[] = "Доступные команды:";
        $strs[] = "cd [path]    - Перемещается в директорию";
        $strs[] = "exit         - Завершает сеанс";
        $strs[] = "get [path]   - Скачивает папку в идентичную папку проекта";
        $strs[] = "help         - Отображает помощь по командам";
        $strs[] = "project      - Устанавливает текущую директорию в качестве директории проекта";
        $strs[] = "pwd          - Отображает путь к текущей папке";

        $str = join("\n", $strs);
        echo "\n";
        $this->log($str);
    }

    /**
     * Скачивание директории в локальную папку
     * 
     * @param \phpseclib\Net\SFTP $ftp Подключение по SSH
     * @param String $param Название папки в текущей директории
     */
    public function get(phpseclib\Net\SFTP $ftp, $param)
    {
        if ($this->remoteProjectPath == null)
        {
            $msg = "Директория проекта не установлена";
            return $this->log($msg, CLogger::LEVEL_ERROR);
        }
        $pwd = $ftp->pwd();
        $remotePath = FileHelper::joinPaths($pwd, $param);
        if (!$ftp->file_exists($remotePath))
        {
            return $this->log("Удаленный путь '$remotePath' не существует.", CLogger::LEVEL_ERROR);
        }

        $this->log("Абсолютный путь: '$remotePath'");
        $this->log("Путь проекта: '{$this->remoteProjectPath}'");

        $relativePath = str_replace($this->remoteProjectPath, "", $remotePath);
        if ($remotePath == $relativePath)
        {
            $msg = "Путь '$relativePath' не относится к проекту";
            $this->log($msg, CLogger::LEVEL_ERROR);
            return;
        }
        $this->log("Относительный путь: '$relativePath'");

        $rootPath = Yii::getPathOfAlias("root");
        $localPath = FileHelper::joinPaths($rootPath, $relativePath);
        if (!file_exists($localPath))
        {
            return $this->log("Локальный путь '$localPath' не существует.", CLogger::LEVEL_ERROR);
        }
        $this->log("Скачиваю '$remotePath'\n В '$localPath'", CLogger::LEVEL_WARNING);
        $this->download($ftp, $remotePath, $localPath);
    }

    /**
     * Скачивание директории в локальную папку
     * 
     * @param \phpseclib\Net\SFTP $ftp Подключение по SSH
     * @param String $remotePath Путь к удаленной папке
     * @param String $localPath Путь к локальной папки
     */
    protected function download(phpseclib\Net\SFTP $ftp, $remotePath, $localPath)
    {
        $list = $ftp->rawlist($remotePath);
        foreach ($list as $name => $file)
        {
            if (in_array($name, ["..", "."]))
            {
                continue;
            }
            $newRemotePath = FileHelper::joinPaths($remotePath, $name);
            $newLocalPath = FileHelper::joinPaths($localPath, $name);
            $msg = "\n" . $newRemotePath . "\n" . $newLocalPath . "\n";
            $this->log($msg);
            $type = $file['type'];
            if ($type == NET_SFTP_TYPE_DIRECTORY)
            {
                if (!file_exists($newLocalPath))
                {
                    mkdir($newLocalPath);
                }
                $this->download($ftp, $newRemotePath, $newLocalPath);
            } elseif ($type == NET_SFTP_TYPE_REGULAR)
            {
                if (file_exists($newLocalPath))
                {
                    unlink($newLocalPath);
                }
                $ftp->get($newRemotePath, $newLocalPath);
            } else
            {
                $this->log("Пропускаю '$newRemotePath'. Странный тип.", CLogger::LEVEL_WARNING);
            }
        }
    }

    /**
     * Подключение к дев серверу
     * 
     * @param String $name имя дев сервера, например dev1.
     */
    public function processDev($name)
    {
        $user = "freddis";
        $host = trim($name) . ".home-studio.pro";
        $number = str_replace("dev", "", strtolower(trim($name)));
        if ($number == "")
            $number = 0;

        $port = "223" . $number;

        $this->log("Host: $host");
        $this->log("Port: $port");
        $this->log("Username: $user");
        do
        {
            $password = $this->promptPassword("Password:");
            $ftp = $this->connect($host, $port, $user, $password);
        } while ($ftp == null);

        $this->cd($ftp, "www");
        $this->setProject($ftp);

        $this->commandCycle($ftp);
    }

    /**
     * Подключение к серверу по SSH.
     * 
     * @param String $host Хост
     * @param String $port Порт
     * @param String $user Пользоватль
     * @param String $password Пароль
     * @return \phpseclib\Net\SFTP Объект подключение к серверу по SSH
     */
    public function connect($host, $port, $user, $password)
    {
        EnvHelper::enableComposer();
        $ftp = new \phpseclib\Net\SFTP($host, $port);
        if (!$ftp->login($user, $password))
        {
            $this->log("Не удалось подключиться", CLogger::LEVEL_ERROR);
            return null;
        }
        return $ftp;
    }

    /**
     * Запуск цикла команд
     * 
     * @param \phpseclib\Net\SFTP $ftp Подключение по SSH
     */
    public function commandCycle($ftp)
    {
        $this->pwd($ftp);
        $this->log("Для помощи, введите команду help", CLogger::LEVEL_WARNING);
        while (true)
        {
            $this->getCommand($ftp);
        }
    }

    /**
     * Вывод текущей директории
     * 
     * @param \phpseclib\Net\SFTP $ftp Подключение по SSH
     */
    public function pwd($ftp)
    {
        $this->log("Текущая директрия:");
        $this->log($ftp->pwd());
    }

}
