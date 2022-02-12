<?php

/**
 * Модель из базы данных, в которой поля названы не по конвенции Yii.
 *
 * Разрабочики Yii утверждают, что это не будут делать ремаппинг, так как это 
 * проблема разработчиков. Однако иногда приходится подключаться к чужим базам данных
 * и в этом случае придется нарушать конвенцию, что не совсем не хорошо
 * 
 * @todo Что-то архитетура решения какая-то слишком сложная. Намного проще можно проблему решить магическими методами. Но сначала тесты надо написать
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Models
 */
abstract class RemappedActiveRecord extends ActiveRecord
{
    
    protected $disableMappings = false;
        
    /**
     * Finds all active records that have the specified attribute values.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return CActiveRecord[] the records found. An empty array is returned if none is found.
     */
    public function findAllByAttributes($attributes, $condition = '', $params = array())
    {
        $newAttributes = $this->remapToDb($attributes);
        return parent::findAllByAttributes($newAttributes, $condition, $params);
    }

    /**
     * Finds a single active record that has the specified attribute values.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return CActiveRecord the record found. Null if none is found.
     */
    public function findByAttributes($attributes, $condition = '', $params = array())
    {
        $remapped = $this->remapToDb($attributes);
        return parent::findByAttributes($remapped, $condition, $params);
    }

    /**
     * Приводит массив с ключами полями к массиву с ключами из базы данных
     * 
     * @param String[] $attributes Массив, где ключами являются поля класса
     * @return String Массив, где ключами являются поля из базы данных
     */
    protected function remapToDb($attributes)
    {
        $mappings = $this->getMappings();
        $reversedMappings = array_flip($mappings);
        $newAttributes = $this->remapFields($attributes, $reversedMappings);
        return $newAttributes;
    }

    /**
     * Вспомогательная функция, которая переименовывает ключи массива, в соответствии с картой
     * 
     * @param String[] $attributes Массив, с определенным набором ключей
     * @param String[] $mappings Карта, переименования ключей. Ключами являются старые ключи, а значениями - новые
     * @return String[] Массив, с переименованными ключами
     */
    protected function remapFields($attributes, $mappings)
    {
        $newAttributes = array();
        foreach ($attributes as $name => $attr)
            if (isset($mappings[$name]))
                $newAttributes[$mappings[$name]] = $attr;
            else
                $newAttributes[$name] = $attr;

        return $newAttributes;
    }

    /**
     * Функция которые назначает поля объекту.
     * Изначально была написана как шорткат, но не уверен, что часто будет использоваться.
     * 
     * @param Object $obj Объект, реализующий интерфейс массива (объект или массив)
     */
    public function applyMap(&$obj, $newAttributes)
    {
        foreach ($newAttributes as $key => $value)
            $obj[$key] = $value;
    }

    /**
     * Обработчик события, которое вызывается при загрузке модели из БД
     */
    public function afterFind()
    {
        //Переименовываем поля
        $this->remapModelFields();
    }

    /**
     * Возвращает карту полей
     * Данную функцию необходимо переопределять в классах наследниках
     * 
     * @return String[] Массив где ключами являются старые названия полей, а значениями - новые
     */
    public abstract function getMappings();

    /**
     *  Назвачает правильные значения публичным полям, исходя из маппинга
     */
    protected function remapModelFields()
    {
        $mappings = $this->getMappings();
        $attributes = parent::getAttributes();
        $newAttributes = $this->remapFields($attributes, $mappings);
        $this->applyMap($this, $newAttributes);
    }

    /**
     * Returns all column attribute values.
     * Note, related objects are not returned.
     * @param mixed $names names of attributes whose value needs to be returned.
     * If this is true (default), then all attribute values will be returned, including
     * those that are not loaded from DB (null will be returned for those attributes).
     * If this is null, all attributes except those that are not loaded from DB will be returned.
     * @return array attribute values indexed by attribute names.
     */
    public function getAttributes($names = true)
    {
        if($this->disableMappings)
        {
            return parent::getAttributes();
        }
        $mappings = $this->getMappings();
        $attributes = parent::getAttributes();
        $newAttributes = $this->remapFields($attributes, $mappings);
        return $newAttributes;
    }

    /**
     * Сохраняет модель в базе данных
     * 
     * @param Bool $runValidation Запускать ли валидацию
     * @param String[] $attributes Массив имен полей, которые необходимо сохранить
     * @return Bool True, в случае успеха
     */
    public function save($runValidation = true, $attributes = null)
    {
	//Назначаем старые маппинги
	$mappings = $this->getMappings();
	foreach($mappings as $original => $new)
	    $this[$original] = $this[$new];
        
        //Для апдейтов
	$this->disableMappings = true;
	$result = parent::save($runValidation, $original);
        $this->disableMappings = false;
	return $result;
    }

}
