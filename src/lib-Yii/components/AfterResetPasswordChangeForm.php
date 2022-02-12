<?php
/**
 * Форма для изменения пароля после его сброса.
 * 
 * Отличается от стандартной формы смены пароля тем, что не требует введения старого пароля.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 */
class AfterResetPasswordChangeForm extends PasswordChangeForm
{
    
    /**
     * Возвращает правила валидации для формы
     * 
     * @return String[]
     */
    public function rules()
    {
        $rules = parent::rules();
        //Фактически, мы тут просто убираем проверки старого пароля
        ActiveRecordHelper::removeFieldRule($rules, "oldPassword","validateOldPassword");
        ActiveRecordHelper::removeFieldRule($rules, "oldPassword","required");
        return $rules;
    }
}