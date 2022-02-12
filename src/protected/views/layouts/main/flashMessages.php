<?php
$flashMessages = Yii::app()->user->getFlashes();
$map = array();
$map['success']=array('alert_ok','icon-ok-sign');
$map['error']=array('alert_warning','icon-exclamation-sign');
$map['warning']=array('alert_note','icon-pencil');
$map['info']=array('alert_info','icon-info-sign');

?>
<?php if ($flashMessages):?>
    <?php foreach($flashMessages as $key => $message): ?>
        <div class="alert-message alert  alert-<?=$key?> ">

            <strong><?=$message?></strong>
            <button class="close" type="button" data-dismiss="alert">x<span class="sr-only">Закрыть</span>
            </button>
        </div>

    <?php endforeach?>
<?php endif?>
       