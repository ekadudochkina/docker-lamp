<?php

/**
 * Тесты для вьюшек
 * 
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class ViewTest extends \Hs\Test\NoDbTestCase
{
    /**
     * Тестируем имена контроллеров указаны верно
     */
    public function testCaseSensitiveUrls()
    {
        $path = Yii::getPathOfAlias("application.views");
        //bug::reveal($path);
        $views = $this->getViewFiles($path);
        $routes = [];
        foreach($views as $view)
        {
            $values = $this->getRoutesFromView($view);
            $routes = array_merge($routes,$values);
        }
        
        //Чистим роуты от тех, что не соответствуют роутам с контроллерами
        $controllerRoutes = [];
        foreach(array_keys($routes) as $route)
        {
            
            if(strlen($route) > 1 && StringHelper::hasSubstring($route,"/"))
            {
                $controllerRoutes[] = $route;
            }
        }
        
        $controllers = [];
        foreach($controllerRoutes as $route)
        {
            $parts = explode("/",$route);
            $controllerPart =  $parts[0];
            $actionPart = $parts[1];
            
            $controller = ucfirst($controllerPart)."Controller";
            $action = "action".ucfirst($actionPart);
            ArrayHelper::addKeyIfNotExists($controllers,$controller);
            ArrayHelper::addKeyIfNotExists($controllers[$controller],$action);
            $controllers[$controller][$action] = $route;
        }
        
        //Теперь проверим существуют ли такие контролеры
        $controllerPath = Yii::getPathOfAlias("application.controllers");
        foreach($controllers as $controller => $actions)
        {
            $route = ArrayHelper::getFirst($actions);
            $filename = $controller.".php";
            $path = FileHelper::joinPaths($controllerPath, $filename);
            $this->assertTrue(file_exists($path),"Файл '$path' не существует для контроллера '$controller'");
            
            //require вызовет ошибку, поэтому сразу проверяем
            foreach(get_included_files() as $file)
            {
                $twice = strtolower($file) == strtolower($path);
                $this->assertFalse($twice,"Похоже, что в роут '$route' не является регистрозависимым. ".$routes[$route]);
            }

            require_once($path);
           
            $reflector = new ReflectionClass($controller);
            $this->assertTrue($reflector->getName() == $controller,"Не найден класс '$controller' для роута '$route' ".$routes[$route]);
            
            foreach($actions as $action => $route)
            {
                $this->assertTrue($reflector->hasMethod($action),"В контроллере '$controller' отсутствует метод '$action' для роута '$route' ".$routes[$route]);
            }
        }
        
        print_r($controllers);
    } 

    /**
     * Рекурсивная функция получения файлов вьюшек
     * 
     * @param String $path Путь к папке
     * @param String $result Предыдущий результат
     * @return String Список файлов
     */
    public function getViewFiles($path,&$result = [])
    {
        $files = FileHelper::getFilesInDirectory($path,true);
        foreach($files as $file)
        {
            //print_r($file);
            $filepath = FileHelper::joinPaths($path,$file);
            if(is_dir($filepath))
            {
                $this->getViewFiles($filepath, $result);
            }
            elseif(StringHelper::hasSubstring($filepath,".php")) {
                $result[] = $filepath;
            }
        }
        return $result;
    }

    /**
     * Получение всех роутов использованных во вьюшке
     * 
     * @param String $view Путь к вьюшке
     * @return String[] Ассоциативный список роутов
     */
    public function getRoutesFromView($view)
    {
        $routes = [];
        $text = file_get_contents($view);
        
        preg_match_all("/createAbsoluteUrl\((.*?)(,|\))/", $text,$matches);
        if($matches[1])
        {
            foreach($matches[1] as $match)
            {
                $strippedRoute = str_replace(["\"","'"],"",$match);
                $routes[$strippedRoute] = $view;
            }
        }
        return $routes;
    }

}
