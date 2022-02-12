<?php

/**
 * Базовый класс класс загружаемого файла-картинки
 * 
 * @see File (Использование класса задокументировано в базовом классе)
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Models
 */
class BaseImage extends BaseFile
{

    /**
     * Returns the validation rules for attributes.
     *
     * @return array validation rules to be applied when {@link validate()} is called.
     * @see scenario
     */
    public function rules()
    {
        $rules = parent::rules();
        //фильтрация файлов по расширению
        $rules[] = array('filename', 'file', "allowEmpty" => true, "types" => "jpg, png", "wrongType" => "Поддерживается только загрузка файлов с расширениями jpg и png");
        return $rules;
    }

    /**
     * Приведение изоображения к определенному размеру
     * 
     * @param Number $targetWidth Ширина
     * @param Number $targetHeight Высота
     * @return Resource Изоображение
     */
    public function toSize($targetWidth, $targetHeight)
    {

        $this->toAspectRatio($targetWidth, $targetHeight);
        $w = $this->getWidth();
        $h = $this->getHeight();


        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        $source = $this->getImage();


        //Для PNG надо поколдовать, чтобы прозрачность сохранилась
        $path = $this->getPath();
        $type = exif_imagetype($path);
        if ($type == IMAGETYPE_PNG)
        {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresized($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $w, $h);

        $this->saveImage($target);
        return $target;
    }

    /**
     * Приведение изоображения к определенным пропорциям.
     * 
     * @param Number $targetWidth Ширина
     * @param Number $targetHeight Высота
     * @return Resource Изоображение
     */
    public function toAspectRatio($targetWidth, $targetHeight)
    {

        $targetXyRatio = $targetWidth / $targetHeight;

        $w = $this->getWidth();
        $h = $this->getHeight();
        $xyRatio = $w / $h;

        if ($targetXyRatio == $xyRatio)
            return;


        if ($targetXyRatio > $xyRatio)
        {
            $newH = $w / $targetXyRatio;
            $delta = $h - $newH;
            $y = $delta / 2;
            $params = array('x' => 0, 'y' => $y, 'width' => $w, 'height' => $newH);
        } else
        {
            $newW = $h * $targetXyRatio;
            $delta = $w - $newW;
            $x = $delta / 2;
            $params = array('x' => $x, 'y' => 0, 'width' => round($newW), 'height' => $h);
        }

        $cropped = imagecrop($this->getImage(), $params);
        imagejpeg($cropped, $this->getPath(), 100);
    }

    /**
     * Превращает изоображение в квадрат
     */
    public function squarify()
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        if ($w == $h)
            return;

        $min = min($w, $h);
        $x = 0;
        $y = 0;

        if ($min == $w)
        {
            $delta = $h - $w;
            $y = $delta / 2;
        } else
        {
            $delta = $w - $h;
            $x = $delta / 2;
        }
        $params = array('x' => $x, 'y' => $y, 'width' => $min, 'height' => $min);
        $squared = imagecrop($this->getImage(), $params);
        $this->saveImage($squared);
        return $squared;
    }

    /**
     * Получение ширины изоображения в пикселях
     * 
     * @return Number
     */
    public function getWidth()
    {
        $path = $this->getPath();
        $sourceWidht = getimagesize($path)[0];
        return $sourceWidht;
    }

    /**
     * Получение высоты изоображения в пикселях
     * 
     * @return Number
     */
    public function getHeight()
    {
        $path = $this->getPath();
        $sourceHeight = getimagesize($path)[1];
        return $sourceHeight;
    }

    /**
     * Получение текущего изоображения для редактирования
     * @return Resource
     */
    public function getImage()
    {
        $path = $this->getPath();
        $type = exif_imagetype($path);
        $im = null;
        switch ($type)
        {
            case IMAGETYPE_PNG : $im = imagecreatefrompng($path);
                break;
            case IMAGETYPE_JPEG : $im = imagecreatefromjpeg($path);
                break;
        }
        return $im;
    }

    /**
     * Сохранение Изоображения за место текущей
     * @param Resource $image Картинка
     */
    public function saveImage($image)
    {
        $path = $this->getPath();
        $type = exif_imagetype($path);
        switch ($type)
        {
            case IMAGETYPE_PNG : imagepng($image, $path, 0);
                break;
            case IMAGETYPE_JPEG : imagejpeg($image, $path, 100);
                break;
        }
        //bug::drop($type);
    }

    /**
     * Превращение картинки в тип jpeg
     */
    public function toJpeg()
    {
        $prevPath = $this->getPath();
        $extension = pathinfo($prevPath, PATHINFO_EXTENSION);
        $jpegs = array("jpg", "jpeg");
        if (in_array(strtolower($extension), $jpegs, true))
            return;

        $image = $this->getImage();

        $newFilename = substr($this->filename, 0, strlen($this->filename) - strlen($extension)) . "jpg";
        $this->filename = $newFilename;
        $newPath = $this->getPath();
        imagejpeg($image, $newPath, 100);
        unlink($prevPath);
        $this->save();
    }

    /**
     * Получает изоображение в необходимом размере
     * 
     * @param Number $width Ширина
     * @param Number $height Высота
     * @param String $name Имя поля $_POST (нужно при добавлении нескольких файлов)
     * @return BaseImage Объект изоображения
     */
    public static function createAndSaveResized($width, $height, $name = "file")
    {
        $result = static::createAndSave($name);
        if ($result)
        {
            try
            {
                $result->toSize($width, $height);
                //$result->toJpeg();
            } catch (Exception $e)
            {
                debug::drop($e);
                //я не стал делать удаление файла тут потмоу что это может привести к новым ошибкам
                return null;
            }
        }
        return $result;
    }
}
