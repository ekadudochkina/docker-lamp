<?php
/**
 * Ввведенные реквизиты пользователя. Используется для авторизации Yii.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
class UserIdentity extends CUserIdentity
{    
    /**
     * Проверяем, является ли пользователь зарегистрированным или нет.
     * 
     * @return Bool True, если пользователь зарегистрирован
     */
    public function  authenticate()
    {
        $atrArray = array('name'=>$this->username);
        $model = BaseUser::model()->findByAttributes($atrArray);
            
        if($model === null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if($model->password!==$model->encodePassword($this->password))
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else
            $this->errorCode=self::ERROR_NONE;
        return !$this->errorCode;
    }    
}
?>
