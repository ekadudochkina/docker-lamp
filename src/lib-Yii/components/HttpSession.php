<?php

/**
 * Объект сессии предназначен исправить постоянную ошибку Yii, 
 * которая мешает делат дебаг, так как постоянно кидает исключения.
 * 
 * Просто не открываем сессию, если она уже есть.
 * 
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Yii
 */
class HttpSession extends CHttpSession
{
    /**
     * Updates the current session id with a newly generated one .
     * Please refer to {@link http://php.net/session_regenerate_id} for more details.
     * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
     * @since 1.1.8
     */
    public function regenerateID($deleteOldSession=false)
    {
        @session_regenerate_id($deleteOldSession);
    }

        /**
	 * Starts the session if it has not started yet.
	 */
	public function open()
	{
		if($this->getUseCustomStorage())
			@session_set_save_handler(array($this,'openSession'),array($this,'closeSession'),array($this,'readSession'),array($this,'writeSession'),array($this,'destroySession'),array($this,'gcSession'));
		
		//Вся соль тут. Если сессия уже есть то не надо делать новую.
		$status = session_status();
		if ($status == PHP_SESSION_NONE) 
		{
		    @session_start();
		}
		if(YII_DEBUG && session_id()=='')
		{
			$message=Yii::t('yii','Failed to start session.');
			if(function_exists('error_get_last'))
			{
				$error=error_get_last();
				if(isset($error['message']))
					$message=$error['message'];
			}
			Yii::log($message, CLogger::LEVEL_WARNING, 'system.web.CHttpSession');
		}
	}
}
