<?php
$flashMessages = Yii::app()->user->getFlashes();
$map = array();
$map['success']=array('success','icon-ok-sign');
$map['error']=array('danger','icon-exclamation-sign');
$map['warning']=array('warning','icon-pencil');
$map['info']=array('info','icon-info-sign');
?>
<?php if ($flashMessages):?>
    <?php foreach($flashMessages as $key => $message): ?>
    <div class="col-sm-12 main-alert-<?=$key?> alert alert-box alert-<?=$key?> ">
	<button class="close" type="button" data-dismiss="alert">×<span class="sr-only">Закрыть</span>
	</button>
	<strong><?=$message?></strong>
    </div>  
<?php endforeach?>
<?php endif?>
       