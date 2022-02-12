<?php

Yii::import("root.protected.controllers.*");

/**
 * Class HtmlCompileCommand
 * Создает для всех контроллеров и экшенов html файлы
 *
 * php ./protected/run.php htmlCompile
 */
class HtmlCompileCommand extends ConsoleCommand
{
    public function run($args)
    {
        $bootstraper = new Bootstraper();
        $bootstraper->disableDatabase();;
        $this->switchToWebApplication($bootstraper);

//            $controller = new SiteController("site",null);
//            $controller->actionPage();
//            die();

        //Проверяем папку куда будем все складывать, если ее нет то создаем
        //Если папка есть то чистим ее и заново создаем
        $dir = Yii::app()->basePath . "/../htmls";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }else{
            FileHelper::removeDirectory($dir);
            mkdir($dir, 0777, true);
        }

        $controllersArray = $this->getControllers();
        foreach ($controllersArray as $key => $value){

            $id = mb_strtolower(str_replace("Controller","",$key));

            $controller = new $key($id,null);

            foreach ($value as $action) {
                $actionName = "action".$action;

                ob_start();
                $controller->$actionName();

                $contents = ob_get_contents();
                $contents = str_replace("http://127.0.0.1:8000/protected",".",$contents);

                ob_end_clean();


                $file = Yii::app()->basePath . "/../htmls/".mb_strtolower($action).".html";
                file_put_contents($file, $contents, FILE_APPEND | LOCK_EX);
            }
        }

        //Сохраняем папку assets
        $src = Yii::app()->basePath."/assets";
        $dst = Yii::app()->basePath."/../htmls/assets";
        $model = FileHelper::copyDirectory($src, $dst);


        echo  "Success create htmls files";
    }




    public function getControllers() {
        $path = Yii::app()->controllerPath;
        $data = array();

        $files = CFileHelper::findFiles($path, array("fileTypes" => array("php")));

        foreach ($files as $file) {
            include_once $file;
            $filename = basename($file, '.php');

            //Исключаем AdminController
            if($filename !== "AdminController") {
                if (($pos = strpos($filename, 'Controller')) > 0) {
                    $class_name = $controllers[] = substr($filename, 0, $pos);
                    $f = new ReflectionClass($filename);
                    $methods = array();
                    foreach ($f->getMethods() as $m) {
                        if ($m->class == $class_name . "Controller" && preg_match('/^action+\w{2,}/', $m->name)) {
                            $actionName = str_replace("action", "", $m->name);
                            //Исключаем ErrorAction
                            if($actionName !== "Error"){
                                $methods[] = $actionName;
                            }
                        }
                    }

                    $data[$filename] = $methods;
                }
            }
        }

        return $data;
    }




    /**
     * Подменяет текущее приложение Веб Приложением.
     * Это необходимо, так как изначально тесты запускаются в консольном приложении, которое не имеет многих функций.
     *
     * @param \Bootstraper $bootstrapper
     */
    public function switchToWebApplication($bootstrapper = null)
    {
        $newScriptName = \FileHelper::joinPaths(\Yii::getPathOfAlias("root"), "index.php");
        $bs = $bootstrapper ? $bootstrapper : $this->getBootstrapper();
        $_SERVER["SERVER_NAME"] = "127.0.0.1:8000";
        $_SERVER['SCRIPT_FILENAME'] = $newScriptName;
        $_SERVER['SCRIPT_NAME'] = "/index.php";
        session_start();
        \Yii::setApplication(null);
        $bs->createWebApplication();
        error_reporting(E_ALL);
    }
}