<?php

namespace Hs\Helpers;

/**
 * Помошник для работы с библиотекой PHPExcel
 *
 * @see ArrayHelper::exportToExcel
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class PHPExcelHelper
{

    /**
     * Экспорт таблицы в CSV
     * 
     * @param \PHPExcel $sheet Таблица
     * @return String Содержимое файла CSV
     */
    public static function excelToCSV(\PHPExcel $sheet)
    {
        $objWriter = \PHPExcel_IOFactory::createWriter($sheet, 'CSV');
        ob_start();
        $objWriter->save('php://output');
        $csv = ob_get_clean();
        return $csv;
    }

}
