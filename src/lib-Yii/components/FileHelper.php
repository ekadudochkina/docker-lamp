<?php

/**
 * Помошник для работы с файлами
 *
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Helpers
 */
class FileHelper
{
    /**
     * Читает папку и возвращает файлы, которые в ней находятся
     * 
     * @param String $dir Путь к папке
     * @return String[] Файлы в папке
     */
    public static function getFilesInDirectory($dir, $includeDirs = false)
    {
        if (!is_dir($dir))
            throw new Exception("'$dir' doesn't exist!");
        $files = scandir($dir);
        $result = array();
        foreach ($files as $file)
            if ($includeDirs || is_file(static::joinPaths($dir, $file)))
                if ($file != "." && $file != "..")
                    $result[] = $file;
        return $result;
    }

    /**
     * Соединяет пути к файлам - не нужно заботиться о слешах
     * 
     * @param String $path1 Кусок пути
     * @param String $path2 Кусок пути
     * @return String Путь к файлу
     */
    public static function joinPaths($path1, $path2)
    {
        $args = func_get_args();
        //Debug::show($args);
        $ds = '/\\';
        $len = count($args);
        //Первый и последний элемент тримим аккуратно
        $args[0] = rtrim($args[0],$ds);
        $args[$len - 1] = ltrim($args[$len - 1],$ds);

        //Трим серединку
        for ($i = 1; $i < $len - 1; $i++)
        {
            $args[$i] = trim($args[$i], $ds);
        }
        //Debug::show($args);
        $result = join(DIRECTORY_SEPARATOR, $args);
        return $result;
    }

    /**
     * Проверяет принадлежит ли файл фреймворку Yii
     * 
     * @param String $path Путь к файлу
     * @return boolean True, если файл является файлом Yii
     */
    public static function isYiiFile($path)
    {
        if (StringHelper::hasSubstring($path, "lib-Yii/framework")|| StringHelper::hasSubstring($path, "lib-Yii\\framework"))
        {
            return true;
        }
        return false;
    }

    /**
     * Получает список всех файлов в директории. В списке будут полные пути к файлам.
     * 
     * @param String $path Путь к директории
     * @param Boolean $recursive Искать ли файлы в поддиректориях
     * @return String[] Массив путей к файлам
     */
    public static function getFilePathsInDirectory($path, $recursive = false)
    {
        $result = [];
        $stack = [$path];
        
        while (count($stack) > 0)
        {
            $dir = array_shift($stack);
            //bug::show($dir);
            $files = static::getFilesInDirectory($dir, true);
            foreach ($files as $file)
            {
                
                $filepath = static::joinPaths($dir, $file);
                if (is_dir($filepath))
                {
                    if($recursive)
                    {
                        $stack[] = $filepath;
                    }
                    continue;
                } 
                
                 $result[] = $filepath;
                
            }
        }
        return $result;
    }

    /**
     * Оставляет только файлы с определенным расширением в массиве
     * 
     * @param String[] $files Массив путей к файлам или файлов
     * @param String[] $extensions Массив расширений, которые необходимо оставить (без точки)
     * @result String[] $array Отфильтрованный массив
     */
    public static function filterExtensionsFromArray($files,$extensions)
    {
        $result = [];
        foreach($files as $file)
        {
            $filename = basename($file);
            foreach($extensions as $ext)
            {
                if(StringHelper::hasSubstring($filename, ".".$ext))
                {
                    $result[] = $file;
                }
            }
        }
        return $result;
    }

    /**
     * Ищет файл в данной папке с именем, включающем данный шаблон
     * 
     * @param String $path Путь к папке, в которой нужно искать
     * @param String $pattern Шаблон имени файла
     * @result String Имя найденного файла, или null, если файл не найден
     */
    public static function findFile($path, $pattern) {
        $filesInDir = static::getFilesInDirectory($path, false);
        foreach ($filesInDir as $fileInDir)
        { 
            if (StringHelper::hasSubstring($fileInDir, $pattern))
            {
                return $fileInDir;
            }
        }
        return null;
    }
    /**
     * Ищет файлы в данной папке с именем, включающем данный шаблон
     * 
     * @param String $path Путь к папке, в которой нужно искать
     * @param String $pattern Шаблон имени файла
     * @result String[]
     */
    public static function findFiles($path, $pattern) {
        $filesInDir = static::getFilesInDirectory($path, false);
        $result = [];
        foreach ($filesInDir as $fileInDir)
        { 
            if (StringHelper::hasSubstring($fileInDir, $pattern)!=false)
            {
                $result[] = $fileInDir;
            }
        }
        return $result;
    }


    /**
     * @param $bytes
     * @param null $force_unit
     * @param int $precision
     * @param bool $si
     * @return string
     */
    public static function convertFromBytes($bytes, $force_unit = NULL, $precision = 2, $si = TRUE)
    {
        // Format string
        $format = "%01.{$precision}f";

        // IEC prefixes (binary)
        if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
        {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod   = 1024;
        }
        // SI prefixes (decimal)
        else
        {
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod   = 1000;
        }

        // Determine unit to use
        if (($power = array_search((string) $force_unit, $units)) === FALSE)
        {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

         return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }

    public static function copyDirectory($src, $dst, $options = [])
    {
        $src = static::normalizePath($src);
        $dst = static::normalizePath($dst);

        if ($src === $dst || strpos($dst, $src . DIRECTORY_SEPARATOR) === 0) {
            throw new InvalidArgumentException('Trying to copy a directory to itself or a subdirectory.');
        }
        $dstExists = is_dir($dst);
        if (!$dstExists && (!isset($options['copyEmptyDirectories']) || $options['copyEmptyDirectories'])) {
            static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
            $dstExists = true;
        }

        $handle = opendir($src);
        if ($handle === false) {
            throw new InvalidArgumentException("Unable to open directory: $src");
        }
        if (!isset($options['basePath'])) {
            // this should be done only once
            $options['basePath'] = realpath($src);
            $options = static::normalizeOptions($options);
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $file;
            $to = $dst . DIRECTORY_SEPARATOR . $file;
            if (static::filterPath($from, $options)) {
                if (isset($options['beforeCopy']) && !call_user_func($options['beforeCopy'], $from, $to)) {
                    continue;
                }
                if (is_file($from)) {
                    if (!$dstExists) {
                        // delay creation of destination directory until the first file is copied to avoid creating empty directories
                        static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
                        $dstExists = true;
                    }
                    copy($from, $to);
                    if (isset($options['fileMode'])) {
                        @chmod($to, $options['fileMode']);
                    }
                } else {
                    // recursive copy, defaults to true
                    if (!isset($options['recursive']) || $options['recursive']) {
                        static::copyDirectory($from, $to, $options);
                    }
                }
                if (isset($options['afterCopy'])) {
                    call_user_func($options['afterCopy'], $from, $to);
                }
            }
        }
        closedir($handle);
    }

    public static function normalizePath($path, $ds = DIRECTORY_SEPARATOR)
    {
        $path = rtrim(strtr($path, '/\\', $ds . $ds), $ds);
        if (strpos($ds . $path, "{$ds}.") === false && strpos($path, "{$ds}{$ds}") === false) {
            return $path;
        }
        // fix #17235 stream wrappers
        foreach (stream_get_wrappers() as $protocol) {
            if (strpos($path, "{$protocol}://") === 0) {
                return $path;
            }
        }
        // the path may contain ".", ".." or double slashes, need to clean them up
        if (strpos($path, "{$ds}{$ds}") === 0 && $ds == '\\') {
            $parts = [$ds];
        } else {
            $parts = [];
        }
        foreach (explode($ds, $path) as $part) {
            if ($part === '..' && !empty($parts) && end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' || $part === '' && !empty($parts)) {
                continue;
            } else {
                $parts[] = $part;
            }
        }
        $path = implode($ds, $parts);
        return $path === '' ? '.' : $path;
    }

    protected static function normalizeOptions(array $options)
    {
        if (!array_key_exists('caseSensitive', $options)) {
            $options['caseSensitive'] = true;
        }
        if (isset($options['except'])) {
            foreach ($options['except'] as $key => $value) {
                if (is_string($value)) {
                    $options['except'][$key] = self::parseExcludePattern($value, $options['caseSensitive']);
                }
            }
        }
        if (isset($options['only'])) {
            foreach ($options['only'] as $key => $value) {
                if (is_string($value)) {
                    $options['only'][$key] = self::parseExcludePattern($value, $options['caseSensitive']);
                }
            }
        }

        return $options;
    }

    public static function filterPath($path, $options)
    {
        if (isset($options['filter'])) {
            $result = call_user_func($options['filter'], $path);
            if (is_bool($result)) {
                return $result;
            }
        }

        if (empty($options['except']) && empty($options['only'])) {
            return true;
        }

        $path = str_replace('\\', '/', $path);

        if (!empty($options['except'])) {
            if (($except = self::lastExcludeMatchingFromList($options['basePath'], $path, $options['except'])) !== null) {
                return $except['flags'] & self::PATTERN_NEGATIVE;
            }
        }

        if (!empty($options['only']) && !is_dir($path)) {
            if (($except = self::lastExcludeMatchingFromList($options['basePath'], $path, $options['only'])) !== null) {
                // don't check PATTERN_NEGATIVE since those entries are not prefixed with !
                return true;
            }


            return false;
        }


        return true;
    }

    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {// https://github.com/yiisoft/yii2/issues/9288
                throw new \yii\base\Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new \yii\base\Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Removes a directory (and all its content) recursively.
     *
     * @param string $dir the directory to be deleted recursively.
     * @param array $options options for directory remove. Valid options are:
     *
     * - traverseSymlinks: boolean, whether symlinks to the directories should be traversed too.
     *   Defaults to `false`, meaning the content of the symlinked directory would not be deleted.
     *   Only symlink would be removed in that default case.
     *
     * @throws ErrorException in case of failure
     */
    public static function removeDirectory($dir, $options = [])
    {
        if (!is_dir($dir)) {
            return;
        }
        if (!empty($options['traverseSymlinks']) || !is_link($dir)) {
            if (!($handle = opendir($dir))) {
                return;
            }
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::removeDirectory($path, $options);
                } else {
                    static::unlink($path);
                }
            }
            closedir($handle);
        }
        if (is_link($dir)) {
            static::unlink($dir);
        } else {
            rmdir($dir);
        }
    }

    public static function unlink($path)
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if (!$isWindows) {
            return unlink($path);
        }

        if (is_link($path) && is_dir($path)) {
            return rmdir($path);
        }

        try {
            return unlink($path);
        } catch (ErrorException $e) {
            // last resort measure for Windows
            if (is_dir($path) && count(static::findFiles($path)) !== 0) {
                return false;
            }
            if (function_exists('exec') && file_exists($path)) {
                exec('DEL /F/Q ' . escapeshellarg($path));

                return !file_exists($path);
            }
            return false;
        }
    }

}
