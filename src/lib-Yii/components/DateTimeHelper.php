<?php
/**
 * Функции для работы с датами
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class DateTimeHelper {
    
    const TIMEZONE_MOSCOW = "Europe/Moscow";
    const TIMEZONE_LONDON = "Europe/London";
    /**
    * Преобразование времени к формату MySQL.
    * 
    * @param Number|null $time Время, которое необходимо преобразовать в формате unix timestamp
    * @return String Дата и время в формате MySQL
    */
   public static function timestampToMysqlDateTime($time=null){
       if(!$time)
	   $time = time();
       $format = "Y-m-d H:i:s";
       $result = date($format,$time);
       return $result;
   }

   /**
    * Преобразование времени к формату MySQL.
    * 
    * @param Number|null $time Время, которое необходимо преобразовать в формате unix timestamp
    * @param Boolean $isEndDay Если данный параметр TRUE, то к результату прибавляем строку конца дня '23:59:59'
    * @return String Дата и время в формате MySQL
    */
   public static function timestampToMysqlDate($time=null,$isEndDay=FALSE)
    {
       $datetime = static::timestampToMysqlDateTime($time);
       $splat = explode(" ",$datetime);
       $result = $splat[0];
       if ($isEndDay)
        {
           $result=$result." 23:59:59";
        }
           
       return $result;
   }
   
    /**
    * Преобразование Даты и времемни к такому формату:Y-m-01. То есть получение даты указывающей на первый день месяца.
    * 
    * @param Number|null $time Время, которое необходимо преобразовать в формате unix timestamp    
    * @return String Дата в формате Y-m-01
    */
   public static function timestampToMysqlMonth($time=null){
       $date=self::timestampToMysqlDateTime($time);
       $tmp=explode(' ', $date);
       $tmp=substr($tmp[0], 0, -2);
       $res=trim($tmp.'01');       
       return $res;
   }
   
    /**
    * Преобразование Даты и времемни к такому формату iso:Y-m-dTH:i:sZ. 
    * @param Number|null $time Время, которое необходимо преобразовать в формате unix timestamp    
    * @return String Дата в формате Y-m-dTH:i:sZ
    */
   public static function timestampToIco($time=null){
       $date=self::timestampToMysqlDateTime($time);
       $tmp=explode(' ', $date);
       $res=$tmp[0].'T'.$tmp[1].'Z';       
       return $res;
   }
   
    /**
    * Преобразование Даты и времемни из формата iso:Y-m-dTH:i:sZ к формату MySQL. 
    * @param String $date Время, которое необходимо преобразовать, в формате iso:Y-m-dTH:i:sZ    
    * @return String Дата и время в формате MySQL
    */
   public static function icoToMysqlDateTime($date){
       $tmp=explode('T', $date);
       $res=$tmp[0].' '.mb_strimwidth($tmp[1], 0, mb_strwidth($tmp[1])-1);       
       return $res;
   }
   
    /**
    * Преобразование Даты и времемни из формата MySQL к формату unix timestamp. 
    * @param String $date Время, которое необходимо преобразовать, в формате MySQL
    * @return Integer количество секунд прощедших с эпохи Unix
    */
   public static function MysqlDateTimeToTimestamp($date){
       $res=strtotime($date);
       return $res;
   }
   
   /**
    * Получение той же даты в формате Mysql с предыдущим месяцем
    * 
    * @param String $date Дата в формате Mysql
    * @return String Дата в формате Mysql
    */
   public static function getPreviousMysqlMonth($date)
   {
       $timestamp = static::MysqlDateTimeToTimestamp($date);
       
       $month = date("n",$timestamp);
       $prevMonth = static::getPreviousMonth($month);
       $newDate = static::changeMonthInMysqlDate($date,$prevMonth);
       if($prevMonth == 12)
       {
           $year = date("Y",$timestamp);
           $prevYear = $year-1;
           $newDate = static::changeYearInMysqlDate($newDate,$prevYear);
       }
       return $newDate;
   }
   
   
   /**
    * Установка нового года в дате Mysql
    * 
    * @param String $date Дата в формате Mysql
    * @param Integer $year Год
    * @return String Дата в формате Mysql
    */
   protected  static function changeYearInMysqlDate($date,$year)
   {
       $parts = explode(" ",$date);
       $yearPart = $parts[0];
       
       $splat = explode("-",$yearPart);
       $splat[0] = $year;
       
       $parts[0] = join("-",$splat);
       
       $newDate = join(" ",$parts);
       return $newDate;
   }
   
   /**
    * Установка нового месяца в дате Mysql
    * 
    * @param String $date Дата в формате Mysql
    * @param Integer $month Номер месяца, начиная с единицы
    * @return String Дата в формате Mysql
    */
   protected  static function changeMonthInMysqlDate($date,$month)
   {
       $formattedMonth = sprintf('%02d',$month);
       $parts = explode(" ",$date);
       $yearPart = $parts[0];
       
       $splat = explode("-",$yearPart);
       $splat[1] = $formattedMonth;
       
       $parts[0] = join("-",$splat);
       
       $newDate = join(" ",$parts);
       return $newDate;
   }
   
   /**
    * Получение следующего месяца
    * 
    * @param Integer $month Номер месяца, начиная с единицы
    * @return Integer Номер следующего месяца
    */
   public static function getNextMonth($month)
   {
       if($month >= 12)
	   return 1;
       else
	   return $month+1;
   }
   
   /**
    * Получение предыдущего месяца
    * 
    * @param Integer $month Номер месяца, начиная с единицы
    * @return Integer Номер предыдущего месяца
    */
   public static function getPreviousMonth($month)
   {
       if($month <= 1)
	   return 12;
       else
	   return $month-1;
   }
   
   /**
    * Прибавляет или отнимает несколько месяцев от заданного
    * 
    * @param Int $month Месяц, к которому необходимо прибавлять месяца
    * @param Int $number Число месяцев для прибавления. Если будет негативное, то будет отниматься.
    * @return Int Месяц
    */
   public static function addMonths($month,$number)
   {
       if($number > 0)
       {
           for($i=0; $i < $number;$i++)
           {
               $month = static::getNextMonth($month);
           }
       }
       if($number < 0)
       {
           for($i=0; $i > $number;$i--)
           {
               $month = static::getPreviousMonth($month);
           }
       }
       return $month;
   }

   /**
    * Преобразование даты из формата MySQL к формату unix timestamp. 
    * 
    * @param String $date Время, которое необходимо преобразовать, в формате MySQL
    * @return Integer количество секунд прощедших с эпохи Unix
    */
    public static function mysqlDateToTimestamp($date)
    {
        return strtotime($date);
    }
    
    /**
     * Создает штамп времени
     * 
     * @param String $year Год в формате из четырех цифр
     * @param String $month Месяц в любом формате
     * @param String $day День в любом формате
     * @return Integer Количество секунд прощедших с эпохи Unix
     */
    public static function createStamp($year,$month,$day)
    {
        $month = str_pad($month,2,"0",STR_PAD_LEFT);
        $day = str_pad($day,2,"0",STR_PAD_LEFT);
        
        $date = "$year-$month-$day";
        $stamp = DateTimeHelper::mysqlDateToTimestamp($date);
        return $stamp;
    }
    
    /**
     * Прибавляет или отнимает несколько месяцев от заданного. Данная функция учитывает переход через года.
     * 
     * @param Integer $stamp Количество секунд прощедших с эпохи Unix
     * @param Int $number Число месяцев для прибавления. Если будет негативное, то будет отниматься.
     * @return Integer Количество секунд прощедших с эпохи Unix
     */
    public static function addMonthToStamp($stamp,$number)
    {
//        $month = date("m",$stamp);
//        $year = date("Y",$stamp);
//        $day = date("d",$stamp);
//        
//        $number = 15;
//        $newMonth = DateTimeHelper::addMonths($month,$number);
//        
//        $years = ceil($number/12);
//        if($month > 0)
//        {
//            $newYear = $newMonth == 1 ? $year+$years : $year;
//        }
//        elseif($month < 0) 
//        {
//            $newYear = $newMonth == 1 ? $year-$years : $year;
//        }
//        else 
//        {
//            $newYear = $year;
//        }
//        
//        $newMonthStr = str_pad($newMonth,2,"0",STR_PAD_LEFT);
//        $newDate = "$newYear-$newMonthStr-$day";
//        $newStamp = DateTimeHelper::mysqlDateToTimestamp($newDate);
//        return $newStamp;
    }
    
    
     /**
    * Функция прибавляет месяца к определенной дате
    * 
    * @param Integer $monthNumber Количество месяцев (при передаче числа с минусом - функция отнимет данное число месяцев)
    * @param Number|null $timestamp Время, в формате unix timestamp (если не передано берется текущая дата)
    * @return Integer количество секунд прощедших с эпохи Unix
    */
   public static function addMonthsToTimestamp($monthNumber,$timestamp = null)
   {
       if($timestamp == null){
           $timestamp = time();
       }
       $specifiedDate = date("Y-m-d",$timestamp);      
       $date = new DateTime($specifiedDate);

       if($monthNumber >= 0){          
           $number = 'P'.$monthNumber.'M';
           $date = $date->add(new DateInterval($number));
       }elseif ($monthNumber < 0) {
            $number = abs($monthNumber);            
            $number = 'P'.$number.'M';
            $interval = new DateInterval($number);
            $date = $date->sub($interval);         
       }
              
          $date = $date->format('Y-m-d'); 
          $date = strtotime ($date);
        
          return $date;
   }
   
   
   
    /**
    * Функция прибавляет дни к определенной дате
    * 
    * @param Integer $dayNumber Количество дней (при передаче числа с минусом - функция отнимет данное число дней)
    * @param Number|null $timestamp Время, в формате unix timestamp (если не передано берется текущая дата)
    * @return Integer количество секунд прощедших с эпохи Unix
    */
   public static function addDaysToTimestamp($dayNumber,$timestamp = null)
   {
       if($timestamp == null){
           $timestamp = time();
       }
       $specifiedDate = date("Y-m-d",$timestamp);      
       $date = new DateTime($specifiedDate);

       if($dayNumber >= 0){          
           $number = 'P'.$dayNumber.'D';
           $date = $date->add(new DateInterval($number));
       }elseif ($dayNumber < 0) {
            $number = abs($dayNumber);            
            $number = 'P'.$number.'D';
            $interval = new DateInterval($number);
            $date = $date->sub($interval);         
       }
              
          $date = $date->format('Y-m-d'); 
          $date = strtotime ($date);
        
          return $date;
   }

    public static function secondsToTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        return $timeFormat;
    }
}

