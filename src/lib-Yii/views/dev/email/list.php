<?php
/* @var $this EmailController */
?>
<style>
    div {
        margin: 20px;
    }
</style>
<ul>
<?php foreach($actions as $action): ?>
    <li><?=$action?>
    (<a href="<?=$this->createAbsoluteUrl("view",["id"=>$action])?>">View</a>)
    (<a href="<?=$this->createAbsoluteUrl("send",["id"=>$action])?>">Send</a>)
    </li>
<?php endforeach; ?>
</ul>
