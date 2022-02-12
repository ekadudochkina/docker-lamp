<?php

/**
 * Description of WebLogRoute
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class WebLogRoute extends CWebLogRoute
{

    /**
     * Displays the log messages.
     * @param array $logs list of log messages
     */
    public function processLogs($logs)
    {
        $filtered = $this->prepareLogs($logs);
//        bug::droP($filtered);
        $this->render('log', $filtered);
    }

    public function tryToFixFrom($log)
    {
        $splat = explode("SELECT", $log);
        if (count($splat) > 1) {
            $splat = explode("FROM", $splat[1]);
            $text = trim($splat[0]);
            if (!in_array($text, ["*", "COUNT(*)"])) {
                $parts = explode(", ", $text);
                $new = [];
                $i = 0;
                foreach ($parts as $pair) {
                    $i++;
                    $chunks = explode(" AS ", $pair);
                    if (count($chunks) < 2) {
                        return $log;
                    }
                    $name = $chunks[0];
//                    $exp = explode(".", $name);
//                    $meaning = str_replace("`","",$exp[1]);
                    $new[] = $name; //. " AS `".$meaning."_".$i."`";
                }
                $newstr = join(", ", $new);
                $newLog = str_replace($text, $newstr, $log);
                return $newLog;
            }
        }
        return $log;
    }

    public function prepareLogs(array $logs)
    {
        $lookFor = ["system.db.CDbCommand.execute", "system.db.CDbCommand.query"];
        $filtered = [];
        foreach ($logs as $key => $log) {

            //Putting params and new lines
            if (in_array($log[2], $lookFor)) {
                $commandType = $lookFor[array_search($log[2], $lookFor)];
//                bug::show($log[0]);
                //params
                $splat = explode(". Bound with ", $log[0]);
                if (count($splat) > 1) {
                    $newText = $splat[0];
                    $paramsPair = explode(", ", substr($splat[1], 0, strlen($splat[1]) - 1));
                    foreach ($paramsPair as $pair) {
                        $parts = explode("=", $pair);
                        $name = $parts[0];
                        $value = str_replace("$name=", "", $pair);
                        $newText = str_replace($name, $value, $newText);
                    }
                    $log[0] = $newText;
                } else {
                    $log[0] = substr($log[0], 0, strlen($log[0]) - 1);
                }

                //new lines
                $search = ["FROM", "WHERE", "ORDER BY", "GROUP BY", " LIMIT", "JOIN"];
                $replace = [];
                foreach ($search as $el) {
                    $replace[] = "\n" . trim($el);
                }
                $log[0] = str_ireplace($search, $replace, $log[0]);

                $log[0] = $this->tryToFixFrom($log[0]);


                //Removing excessive logs, and counting time
                if (substr($log[0], 0, 4) == "end:") {
                    $replaced = str_replace("end:", "begin:", $log[0]);
                    if (count($filtered) > 0 && $filtered[count($filtered) - 1][0] == $replaced) {
                        $begin = $filtered[count($filtered) - 1][3];
                        $end = $log[3];
                        $time = StringHelper::formatDecimal($end - $begin, 3);
                        $filtered[count($filtered) - 1][0] = str_replace("begin:$commandType(", "time: $time\n", $filtered[count($filtered) - 1][0]);
                    }

                    continue;
                }
            }
//
            $filtered[] = $log;
        }
        return $filtered;
    }

}
