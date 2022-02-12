<?php

/**
 * Пользователь у которого есть роли.
 * Для назначения ролей, мы используем роли Yii.
 *
 * @see CAuthItem
 * @see CAuthManager
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Models
 */
class RoleBasedUser extends BaseUser
{
    /**
     * Имя поля, к которому привязывается роль
     * @var String 
     */
    protected $rbacIdName = "name";
    
    /**
     * Есть ли у модели свои собственные роли 
     * или ее роли объявлены в общей таблице
     * 
     * @var Bool 
     */
    protected $hasOwnRoles = false;
    
    /**
     * Менеджер аутентификации.
     * 
     * @var DbAuthManager 
     */
    protected $authManager;
    
    public function __construct($scenario = 'insert')
    {
        $this->authManager = Yii::app()->authManager;
        parent::__construct($scenario);
    }
    
    /**
     * Проверка наличия роли у пользователя
     * 
     * @param String $name Название роли
     * @return Bool True, Если роль имеется
     */
    public function hasRole($name)
    {
        $this->authManager->setUserPrefix($this);
        
        $role =  $this->authManager->getAuthAssignment($name, $this->getLogin());
        if(!$role)
            return false;
        return true;
    }
    
    /**
     * Получение роли пользователя
     * 
     * @return CAuthItem Ассоциативный массив ролей
     */
    public function getRole()
    {
        $this->authManager->setUserPrefix($this);
        
        $roles = $this->authManager->getRoles($this->getLogin());
        if(count($roles) == 0)
            return null;
        $role = array_shift($roles);
        
        return $role;
    }
    
    /**
     * Назначение роли пользователю
     * <b>Предыдущие роли будут удалены</b>
     * @param String $name Имя роли
     * @throws Exception
     */
    public function setRole($name)
    {
        $this->authManager->setUserPrefix($this);
        
        $role = $this->authManager->getAuthItem($name);
        if(!$role)
            throw new Exception("Роль '$name' не найдена");

        if($this->getRole())
            $this->authManager->revoke ($this->getRole()->getName (),$this->getLogin());
        
        $this->authManager->assign($role->getName(), $this->getLogin());
    }
    
    /**
     * Получение префикса для таблиц авторизации
     * 
     * @return String Префикс
     */
    public function getRbacPrefix()
    {
        $className = get_class($this);
        $prefix = strtolower($className);
        return $prefix;
    }
    
    /**
     * Получение имени поля, к которому привязывается роль
     * 
     * @return String Имя поля, которое является ключем к которому привязывается роль
     * @throws Exception
     */
    public function getRbacIdName()
    {
        $defaultName = $this->rbacIdName;
        $class = get_class($this);
        
        //Потому что PHP - имбицил не имеет нормального способа отличить undefined от null
        //Так еще Yii говна подливает со своими свойствами
        try
        {
            $x = $this->$defaultName;
        } catch (Exception $ex) 
        {
            throw new Exception("Не обнаружено поле '$defaultName' в классе '$class'. Для работы ролей, необходимо перегрузить функцию getRbacIdName()");
        }
              
        return $defaultName;
    }
    
    /**
     * Есть ли у модели свои собственные роли 
     * или ее роли объявлены в общей таблице
     * 
     * @return Bool
     */
    public function hasOwnRoleSet()
    {
        return $this->hasOwnRoles;
    }
    
}
