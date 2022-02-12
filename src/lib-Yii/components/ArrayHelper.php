<?php

/**
 * Хелпер для работы с массивами
 *
 * @todo Некоторые методы добавления ключей в массив дублируют друг друга
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class ArrayHelper
{

    /**
     * Очищает массив от пустых элементов
     * 
     * @param Array $arr Исходный массив
     * @param Bool $trim Нужно ли убирать пробелы и табуляции из строк
     * @return Array Массив без пустых элементов
     */
    public static function removeEmptyElements($arr, $trim = true)
    {
        $result = array();
        foreach ($arr as $el)
        {
            $val = $trim ? trim($el) : $el;
            if ($val)
                $result[] = $val;
        }
        return $result;
    }

    /**
     * Создает ключ массива, если его нет и заполняет значением по-умолчанию.
     * Функция полезна для заполнения ассоциативных массивов в циклах.
     * 
     * @param Array $arr Исходный массив
     * @param String $keyName Имя ключа
     * @param Mixed $value Значение по-умолчанию
     * @return Boolean True, если ключ был создан и False, если ключ имелся зарание.
     */
    public static function addKeyIfNotExists(&$arr, $keyName, $value = array())
    {
        if (isset($arr[$keyName]))
            return false;

        $arr[$keyName] = $value;
        return true;
    }

    /**
     * Создает ключи массива согласно пути.
     * Функция полезна для быстрого создания многомерных ассоциативных массивов
     * 
     * @param Array $arr Массив
     * @param String $path Путь из ключей например product.category.title
     * @param Mixed $value Значение, которое необходимо положить в последний ключ
     */
    public static function makeKeysForPath(&$arr, $path, $value)
    {
        //bug::show($path);
        $parts = explode(".", $path);
        $x = &$arr;
        foreach ($parts as $name)
        {
            ArrayHelper::addKeyIfNotExists($x, $name);
            $x = &$x[$name];
        }
        $x = $value;
    }

    /**
     * Добавляет значение в массив, если его там нет
     * 
     * @param Array $arr Исходный массив
     * @param Mixed $value Значение
     * @return Boolean True, Если значения в массиве не было
     */
    public static function addValueIfNotExists(&$arr, $value)
    {
        if (!in_array($value, $arr))
        {
            $arr[] = $value;
            return true;
        }
        return false;
    }

    /**
     * Создает ключ массива, если его нет и заполняет массивом.
     * Далее массив добавляется значение. Удобно для использования в циклах.
     * 
     * @param Array $arr Исходный массив
     * @param String $keyName Имя ключа
     * @param Mixed $value Значение по-умолчанию
     * @return Boolean True, если ключ был создан и False, если ключ имелся зарание.
     */
    public static function addKeyAndValue(&$arr, $keyName, $value)
    {
        $ret = static::createKeyIfNotExists($arr, $keyName, array());
        $arr[$keyName][] = $value;
        return $ret;
    }

    /**
     * Создает ключ массива, если его нет, и заполняет массивом.
     * Далее массив добавляется значение, если его там не было. Удобно для использования в циклах.
     * 
     * @param Array $arr Исходный массив
     * @param String $keyName Имя ключа
     * @param Mixed $value Значение по-умолчанию
     * @return Boolean True, если значение было добавленно и False, если значение имелось зарание.
     */
    public static function addKeyAndAddValueIfNotExists(&$arr, $keyName, $value)
    {
        static::addKeyIfNotExists($arr, $keyName, array());
        $ret = static::addValueIfNotExists($arr[$keyName], $value);
        return $ret;
    }

    /**
     * Проверяет равны ли два массива
     * 
     * @param Array $arr1 Вервый массив
     * @param Array $arr2 Второй массив
     * @param Bool $strict если True, то проверяет точное соответствие типу и порядок элементов 
     * @return Bool True, если массивы равны
     */
    public static function equals($arr1, $arr2, $strict = false)
    {

        $ret = $strict ? $arr1 === $arr2 : $arr1 == $arr2;
        return $ret;
    }

    /**
     * Получает элемент по номеру с конца массива
     * 
     * @param Array $arr Массив
     * @param Int $number Номер элемента с конца
     * @return Mixed Элемент массива
     */
    public static function getLast($arr, $number = 0)
    {
        $reversed = array_reverse($arr);
        return $reversed[$number];
    }

    /**
     * Получение первого элемента массива (в том числе и ассоциативного)
     * 
     * @param Array $array Массив
     * @return Mixed Элемента массива
     */
    public static function getFirst($array)
    {
        reset($array);
        $first = current($array);
        return $first;
    }

    /**
     * Читает Excel файл в массив
     * 
     * @param String $path Путь к файлу
     * @return String[] Массив с данными из Excel файла
     */
    public static function excelToArray($path, $ignoreFormulasExeptions = true)
    {

        //@todo Данные строки повторяются уже дважды
//	$libPath = Yii::getPathOfAlias("webroot.lib-Yii.components.vendor");
//	$excelPath = $libPath."/PHPExcel-1.8/Classes";
//	// Подключаем класс для работы с excel
//	require_once($excelPath.'/PHPExcel.php');
//  Read your Excel workbook
        try
        {
            $inputFileType = PHPExcel_IOFactory::identify($path);
            $reader = PHPExcel_IOFactory::createReader($inputFileType);
            $table = $reader->load($path);
        } catch (Exception $e)
        {
            throw new Exception('Error loading file "' . pathinfo($path, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

//  Get worksheet dimensions
        $sheet = $table->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $result = array();
//  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++)
        {
            $arr = [];
            for ($col = 0; $col <= $highestColumnIndex; $col++)
            {
                //  Read a row of data into an array
                //bug::show($row,PHPExcel_Cell::stringFromColumnIndex($col));
                try
                {
                    $data = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                } catch (Exception $ex)
                {
                    $data = null;
                    if (!$ignoreFormulasExeptions)
                    {
                        throw $ex;
                    }
                }
                $arr[] = $data;
            }

            //bug::show($arr);
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Конвертирует массив в объект таблицы Excel
     * 
     * @param String[] $array Массив
     * @param String[] $columnTitles Заголовки колонок
     * @param String $title Заголовок таблицы 
     * @return PHPExcel Объект таблицы Excel
     */
    public static function arrayToExcel($array, $columnTitles, $title = null)
    {
        EnvHelper::enableComposer();
        // Создаем объект класса PHPExcel
        $xls = new PHPExcel();
        // Устанавливаем индекс активного листа
        $xls->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xls->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('sheet1');


        //Отступ сверху (он меняется в звисимости от выводимых данных)
        $verticalOffset = 0;

        //Выводим заголовок таблицы, если он есть
        if ($title)
        {
            $verticalOffset += 1;
            // Вставляем текст в ячейку A1
            $sheet->setCellValue("A1", $title);

            //И объединяем остальные
            $colCount = 1;
            if (!empty($columnTitles) || !empty($array))
            {
                if ($array)
                    $colCount = count(ArrayHelper::getFirst($array));
                else
                    $colCount = count($columnTitles);
            }

            $sheet->mergeCellsByColumnAndRow(0, 1, $colCount - 1, 1);
            // Выравнивание текста
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $verticalOffset += 1;
        }

        //Выводим заголовки таблиц, если они есть
        if ($columnTitles)
        {
            $verticalOffset += 1;
            foreach ($columnTitles as $j => $title)
            {
                //break;
                $i = $verticalOffset;
                $sheet->setCellValueByColumnAndRow($j, $i, $title);
                // Применяем выравнивание и цвет
                $style = $sheet->getStyleByColumnAndRow($j, $i);
                $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                //Закрашиваем
                $style->getFill()->getStartColor()->setRGB("EEEEEE");
                $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            }
            $verticalOffset += 1;
        }

        //Выводим данные
        foreach ($array as $i => $row)
        {
            $j = 0;
            foreach ($row as $col)
            {
                //Debug::drop($row, $col, $i, 1);
                $sheet->setCellValueByColumnAndRow($j, $i + $verticalOffset, $col);
                // Применяем выравнивание
                $sheet->getStyleByColumnAndRow($j, $i)->getAlignment()->
                        setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $j++;
            }
        }

        return $xls;
    }

    /**
     * Убирает одно измерение из массива. 
     * Данная функция полезна, если мы выбрали только одно поле из БД или если мы превратили массив моделей в массив с полями id.
     * В этом случае массив [[id => 1],[id => 2], [id => 3]] превратится в [1,2,3].
     * 
     * @param Array $arr Многомерный массив
     * @return Array Массив с уменьшенным количеством измерений
     */
    public static function removeDimention($arr)
    {
        $result = [];
        foreach ($arr as $value)
        {
            $result[] = ArrayHelper::getFirst($value);
        }
        return $result;
    }

    /**
     * Добавляет значение в массив после определенного элемента
     * 
     * @param Array $originalArray Массив, в который необходимо добавить значение
     * @param Mixed $value Значение, после которого необходимо добавить значение
     * @param Mixed $newValue Значение, которое необходимо добавить
     * @return Array Новый массив с дополнительным значением
     */
    public static function insertAfterValue($originalArray, $value, $newValue)
    {
        $arr = [];
        foreach ($originalArray as $el)
        {
            $arr[] = $el;
            if ($el == $value)
            {
                $arr[] = $newValue;
            }
        }
        return $arr;
    }

    /**
     * Удаляет элемент массив с определенным значением
     * 
     * @param Array $arr массив Исходный массив
     * @param Mixed $value Значение
     * @param Boolean $strict Использовать ли строгое сравнение
     * @return Array Новый массив
     */
    public static function unsetElementWithValue($arr, $value, $strict = false)
    {
        $result = [];
        foreach ($arr as $val)
        {
            $good = $strict ? $val !== $value : $val != $value;
            if ($good)
            {
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * Превращает массив со множеством измерений, в плоский массив.
     * Удобно, если необходимо передавать модели в другие языки, в которых нет ассоциативных массиов, например для IOS.
     * 
     * @param Array $arr Массив
     * @param Array $separator Разделитель между полями
     * @return Array Ассоциативный массив с одним измерением
     */
    public static function makeFlat($arr,$separator = ".")
    {
        $result = [];
        $stack = [$arr];
        $path = [[]];
        $addedSubArrays = [];
        while (count($stack))
        {
            $current = array_shift($stack);
            $currentPath = array_shift($path);
            foreach ($current as $key => $value)
            {
                $fullpath = array_merge($currentPath,[$key]);
                $newKey = join($separator,$fullpath);
                //bug::dropOn($newKey,50);
                //Проверка не добавляли ли мы уже данные ключи
                if(isset($result[$newKey]) || in_array($newKey, $addedSubArrays))
                {
                    continue;
                }
                
                
                if(is_array($value))
                {
                   //Приходится прерывать текущий проход, чтобы ключи шли в логическом порядке
                   $newPath = array_merge($currentPath,[$key]);
                   array_unshift($stack,$current);
                   array_unshift($path,$currentPath);
                   array_unshift($stack, $value);
                   array_unshift($path,$newPath);
                   $addedSubArrays[] = $newKey;
                   //bug::drop($stack,$path);
                   break;
                }
                else
                {
                    $result[$newKey] = $value;
                }
            }
        }
        //bug::drop($result,$arr);
        return $result;
    }

    public static function sortWithMap(array $array, array $map,$comparsionCallback = null)
    {
        $default = function($a,$b) {return $a == $b;};
        $comparsionCallback = is_callable($comparsionCallback) ? $comparsionCallback : $default;
        $result = [];
        foreacH($map as $mapEl)
        {
            foreach($array as $el)
            {
              if($comparsionCallback($el,$mapEl))
              {
                  $result[] = $el;
                  break;
              }
            }
        }
        foreach($array as $el)
        {
            if(!in_array($el,$result))
            {
                $result[] = $el;
            }
        }
        return $result;
    }

}
