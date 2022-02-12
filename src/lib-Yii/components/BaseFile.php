<?php

/**
 * Базовая модель файла.
 * Используется для удобной загрузки файлов. Файл перемещается в папку upload 
 * и для него сохраняется модель в базе данных. Далее эту модель можно связывать с другими моделями.
 * 
 * Расширять класс файла функционалом, который к нему не относится к обработке файлов  - не надо. 
 * Если нужно хранить дополнительную информацию нужно создавать новую модель и связывать ее с файлом.
 * Основная причина в том, что файл может быть залит, а может быть не залит со стороны клиента. В этом
 * основа логики данного класса и изменить ее будет тяжело.
 * 
 * Для стандартного использования класса, существует 3 метода: 
 * <br> File::fileField - создает верстку для файлового поля.
 * <br> File::error - отображает верстку ошибки, в случае ошибки.
 * <br> File::createAndSaveInt - пытается получить, проверить и сохранить файл, затем возвращает его Id.
 * @example
 *      //Код представления
 * <br> <span class="wpcf7-form-control-wrap">
 * <br>     <?= File::fileField(); ?>
 * <br>     <?= File::error(); ?>
 * <br> </span> 
 * @example 
 *      //Код контроллера
 * <br> $item = new Item(); 
 * <br> $item->setAttributes($_POST['Item']); 
 * <br> $item->fileId = File::createAndSaveInt();
 * <br> $item->save();
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Models
 */
class BaseFile extends ActiveRecord
{

    /**
     * Первичный ключ
     * @var Integer
     */
    public $id;

    /**
     * Имя файла
     * @var String
     */
    public $filename;

    /**
     * Дата создания
     * @sqltype DATETIME
     * @var String
     */
    public $creationDate;

    /**
     * Директория куда надо складывать загруженные файлы
     * (Отсчитывается от директории проекта)
     * @example "upload/itemImages"
     * @var String
     */
    static protected $directory = "upload";

    /**
     * Ассоциативный массив моделей,которые были созданы.
     * Используется для того, чтобы получить ссылку на созданную модель,
     * в случае, если необходимо отобразить ошибки валидации
     * @var ActiveRecord[]
     */
    static protected $models = array();

    /**
     * This method is invoked before saving a record (after validation, if any).
     * The default implementation raises the {@link onBeforeSave} event.
     * You may override this method to do any preparation work for record saving.
     * Use {@link isNewRecord} to determine whether the saving is
     * for inserting or updating record.
     * Make sure you call the parent implementation so that the event is raised properly.
     * @return boolean whether the saving should be executed. Defaults to true.
     */
    protected function beforeSave()
    {
        $result = parent::beforeSave();
        $this->creationDate = DateTimeHelper::timestampToMysqlDateTime();
        return $result;
    }

    /**
     * Returns the validation rules for attributes.
     *
     * @return array validation rules to be applied when {@link validate()} is called.
     * @see scenario
     */
    public function rules()
    {
        $array = parent::rules();
        //безопастное введение через атрибуты
        $array[] = array('filename', 'safe');
        return $array;
    }

    /**
     * Удаление файла. Удаление модели файла также ведет к физическому удалению файла.
     * 
     * @return Bool True, в случае успеха.
     */
    public function delete()
    {
        $result = parent::delete();
        if ($result)
        {
            unlink($this->getPath());
        }
        return $result;
    }

    /**
     * Возвращает url по которому файл доступен пользователю из интернета
     * @return string Url файла
     */
    public function getUrl()
    {
        $fullpath = '/' . static::$directory . "/" . urlencode($this->filename);
        $absoluteUrl = Yii::app()->createAbsoluteUrl('/');
        $url = $absoluteUrl . $fullpath;
        return $url;
    }

    /**
     * Возвращает путь к файлу в файловой системе
     * @return string Путь
     */
    public function getPath()
    {
        $path = Yii::getPathOfAlias('webroot') . '/' . static::$directory . '/' . $this->filename;
        return $path;
    }

    /**
     * Возвращает текущую модель или null в случае ее отсутствия
     * @param String $name Пространство имен для объекта, чтобы различать модели файлов,
     * если на странице требуется загрузить несколько файлов.
     * @return BaseFile Модель файла
     */
    public static function getModel($name = "file")
    {
        if (isset(static::$models[$name]))
            return static::$models[$name];
        else
            return null;
    }

    /**
     * Возвращает модель. Если модель такого типа была уже создана и отвалидирована, то возвращает существующую.
     * @param String $name Пространство имен для объекта, чтобы различать модели файлов,
     * если на странице требуется загрузить несколько файлов.
     * @return BaseFile Новая или уже существующая модель.
     */
    protected static function generateModel($name)
    {
        $model = static::getModel($name);
        if ($model)
            return $model;
        $classname = get_class(static::model());
        $model = new $classname();
        static::$models[$name] = $model;
        return $model;
    }

    /**
     * Пытается создать и сохранить модель в базу данных. В случае успеха возвращает модель.
     * @param String $name Пространство имен для объекта, чтобы различать модели файлов,
     * если на странице требуется загрузить несколько файлов.
     * @return BaseFile Модель файла, сохраненная в базе данных
     */
    public static function createAndSave($name = "file")
    {
        $model = static::generateModel($name);
        //Так как мы выводим не имя модели в форме, а имя $name
        //То и забирать файл надо не по имени модели, а вручную
        $filename = $name . "[filename]";
        $file = CUploadedFile::getInstanceByName($filename);

        //Если файл вовсе не загружался, то null
        if ($file == null)
        {
            Yii::log("File '$name' wasn't uploaded with form");
            return null;
        }

        //Генерируем имя файла
        //Убираем пробелы
        $x = " ";
        $y = "";
        $name = $file->getName();
        //Добавляем соль, чтобы защититься от дублей 
        $salt = EnvHelper::generateString(4);
        $str = $salt . "_" . str_replace($x, $y, $name);

        //Перемещаем файл в необходимую дирректорию и пытаемся отвалидировать модель файла
        $dir = Yii::getPathOfAlias('webroot') . '/' . static::$directory;
        $path = $dir . "/" . $str;
        if (!file_exists($dir) || !is_writable($dir))
            throw new Exception("Путь '$dir' не существует или в него нельзя записывать!");
        $file->saveAs($path);
        $model->filename = $str;

        //Если файл не правильный, то удаляем его
        if (!$model->validate())
        {
            unlink($path);
            return null;
        }

        //Если файл загружен, перемещен и соответсвует всем требованиям,
        // то сохраняем его в БД и возвращаем модель

        $flag = $model->save();
        //var_dump( $model->getErrors());
        return $model;
    }

    /**
     * Пытается создать и сохранить модель в базу данных. В случае успеха возвращает <b>идентификатор</b> модели.
     * Этот метод немного удобнее для использования, чем обычный createAndSave(), так как позволяет писать меньше строчек кода.
     * @param String $name Пространство имен для объекта, чтобы различать модели файлов,
     * если на странице требуется загрузить несколько файлов.
     * @return Int Идентификатор(id) модели файла, сохраненной в базе данных
     */
    public static function createAndSaveInt($name = "file")
    {
        $model = static::createAndSave($name);
        if ($model)
            return $model->id;
        return null;
    }

    /**
     * Создает верстку для формы загрузки файла.
     * @param String $name Пространство имен для объекта, чтобы различать модели файлов,
     * если на странице требуется загрузить несколько файлов.
     * @param String[] $htmlOptions Массив аттрибутов для тега
     * @return String HTML код поля input
     */
    public static function fileField($name = "file", $htmlOptions = null)
    {
        $model = static::generateModel($name);
        //Используем свой класс активных форм
        $form = new ActiveForm();
        //Потому что в этом волшебном классе можно создавать формы для моделей с любыми именами 
        //Что поможет нам без проблем создать более одного файла на одной странице
        //(по умолчанию имя по имени класса, что не дает добавлять 2 модели одного типа на стрнице)
        $form->setNamespace($name);
        return $form->fileField($model, 'filename', $htmlOptions);
    }

    /**
     * Отображает верстку ошибок, в случае наличия ошибок
     * @param String $name Пространство имен для объекта, чтобы различать модели файлов,
     * если на странице требуется загрузить несколько файлов.
     * @return string HTML код ошибок валидации, если они есть
     */
    public static function error($name = "file")
    {
        $model = static::getModel($name);
        if (!$model)
            return "";
        $form = new ActiveForm();
        return $form->error($model, "filename");
    }

}
