<?php
/* @var $variable ViewAction */
?>
<h3><?= $this->t("File Manager","lib")?></h3>
<hr>
<div class="panel ">
    <div class="panel-body">
          <?php $this->widget('root.lib-Yii.extensions.elfinder.ElFinderWidget', array('connectorRoute' =>$this->getId().'/connector',));?>
    </div>
</div>
