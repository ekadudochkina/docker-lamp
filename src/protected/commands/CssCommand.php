<?php
//php ./protected/run.php css ./protected/assets/css/mybank2.css ./protected/assets/css/mybank2.less "body.mybank2 "

/**
 * Class CssCommand
 * Находит все переменные из less в css файле и создает css только с теми стилями где указаны переменные
 * В начало селектора подставляет третий аргумент
 */
class CssCommand extends ConsoleCommand
{
    public function run($args)
    {
        $path = realpath($args[0]);
        $prependWith = $args[2];

        $lesPath = realpath($args[1]);
        $lessContent = file_get_contents($lesPath);
        $lessLines = explode("\n",$lessContent);
//        bug::drop($lessLines);
        $exclude = ["@import"];
        $filters = [];
        foreach($lessLines as $line)
        {
            if(StringHelper::hasSubstrings($line,$exclude))
            {
                continue;
            }
            $first = ArrayHelper::getFirst(str_split(trim($line)));
            if($first != "@")
            {
                continue;
            }
            $parts = explode(":",trim($line));
            $value  = trim($parts[1]);
            $filters[] = $value;
        }
//        bug::drop($filters);

//        $realArgs = [$path,$prependWith];
//        $realArgsCount = count($realArgs);
//        $copy = array_values($args);
//        $filters = array_splice($copy,$realArgsCount,count($args)-$realArgsCount);
//        bug::Drop($filters);


        $dir = dirname($path);
        $file = str_replace($dir."/","",$path);
        $filename = str_replace(".css","",$file);
        $newPath = $dir."/".$filename.".processed.css";
//        bug::drop($path,$dir,$filename,$basename);


        $result = "";
        $content = file_get_contents($path);
        $lines = explode("\n",$content);
        foreach($lines as $line)
        {
            $result .= $line;
            $lastChar = ArrayHelper::getLast(str_split($line));
            if($lastChar != ';' && $lastChar != '{')
            {
                $result .= "\n";
            }

        }
        $filtered = [];
        $newLines = explode("\n",$result);
        foreach($newLines as $line) {
            $passedFilters = StringHelper::hasSubstrings($line, $filters, false, true);
            if (!$passedFilters) {
                continue;
            }
            $filtered[] = $prependWith.$line;
        }
//        bug::drop($filtered,$newLines);
        $result = join("\n",$filtered);

        file_put_contents($newPath,$result);



    }
}