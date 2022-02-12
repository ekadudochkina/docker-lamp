<?php
/* @var $this SimpleAdminController */
/* @var $model ActiveRecord */

$fields = $model->isNew() ? $this->getCreateFields() : $this->getUpdateFields();
$helper = $this->getFormHelper();
?>
<h1><?= $model->isNew()? $this->getCreateTitle() : $this->getUpdateTitle()?></h1>
<hr>
<div class="row">
    <div class="panel ">
        <div class="panel-body">
            <form method="post" enctype="multipart/form-data"  class="form-horizontal ">
                <?php foreach($fields as $field): ?>
                    <div class="form-group">  
                    <?= $this->getFieldInputLabel($model, $field) ?>
                    <div class="col-sm-5">
                        <?= $this->getFieldInputHtml($model,$field,["disabled"=>"disabled"]); ?>
                    </div> 
                </div>
                <?php endforeach; ?>

                <div class="form-group"> 
                    <div class="col-sm-offset-3 col-sm-5"> 
                        <a  href="<?=$this->createAbsoluteUrl("index")?>" class="btn  btn-blue">Back</a> 
                    </div> 
                </div>

            </form>
        </div>
    </div>
</div>


