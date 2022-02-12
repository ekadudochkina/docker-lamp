<?php
/* @var $this AdminController */
/* @var $model BlogArticle */

$helper = $this->getFormHelper();
?>
<h3><?= $model->isNew() ? $this->t("Create","lib") : $this->t("Edit","lib") ?> <?=$this->t("Article","lib")?></h3>
<hr>
<div class="panel ">
    <div class="panel-body">
        <form method="post" class="form-horizontal " enctype="multipart/form-data" >


            <div class="form-group">  
<!--                <p class="note">Fields with <span class="required">*</span> are required.</p>-->

                <?= $helper->errorSummary($model); ?>

                <div class="">
                    <h4><?=$this->t("Title","lib")?></h4>
                    <?= $helper->textField($model, 'title', ['class' => 'form-control']); ?>
                    <?= $helper->error($model, 'title'); ?>
                </div>
                
                <?php if(!$this->getAction()->isHidden("author")): ?>
                 <div class="">
                    <h4><?=$this->t("Author","lib")?></h4>
                    <?= $helper->textField($model, 'author', ['class' => 'form-control']); ?>
                    <?= $helper->error($model, 'author'); ?>
                </div>
                <?php endif;?>

                <?php if (property_exists(get_class($model), "mainImage")) { ?>
                    <div class=""> 
                        <h4><?=$this->t("mainImage","lib")?></h4>
                            <?= Image::fileField() ?>
                            <?= Image::error(); ?>                       
                    </div>
                <?php } ?>
                
                   <?php if(!$this->getAction()->isHidden("keywords")): ?>
                <div class="">
                    <h4><?=$this->t("keywords","lib")?></h4>
                    <?= $helper->textArea($model, 'keywords', ['class' => 'form-control']); ?>
                    <?= $helper->error($model, 'keywords'); ?>
                </div>
                 <?php endif;?>
                   <?php if(!$this->getAction()->isHidden("description")): ?>
                <div class="">
                    <h4><?=$this->t("description","lib")?></h4>

                       <?= $helper->textArea($model, 'description', ['class' => 'form-control']); ?>

                    <?= $helper->error($model, 'description'); ?>
                </div>
            <?php endif;?>
                <div class="">
                    <h4><?=$this->t("preview","lib")?></h4>

                    <?php
                    $this->widget('root.lib-Yii.components.TextEditor', array(
                        'uploadRoute' => "filemanager/upload",
                        'browseRoute' => "filemanager/browse",
                        'model' => $model,
                        'attribute' => 'preview',
                        'language' => 'en',
                        'editorTemplate' => 'full',
                        'height' => '300px',
                    ));
                    ?>

                    <?= $helper->error($model, 'preview'); ?>
                </div>

                <div class="">
                    <h4><?=$this->t("text","lib")?></h4>
                </div>
                <div>
                    <?php
                    $this->widget('root.lib-Yii.components.TextEditor', array(
                        'uploadRoute' => "filemanager/upload",
                        'browseRoute' => "filemanager/browse",
                        'model' => $model,
                        'attribute' => 'text',
                        'language' => 'en',
                        'editorTemplate' => 'full',
                        'height' => '300px',
                    ));
                    ?>

                    <?= $helper->error($model, 'text'); ?>
                </div>

                <div class=" buttons">
                    <a class="btn btn-default" href="<?= $this->createAbsoluteUrl('index') ?>"><?=$this->t("Back","lib")?></a>
                    <button type="submit" class="btn btn-success"><?= $model->isNew() ? $this->t("Create","lib") : $this->t("Save","lib") ?></button>
                </div>

            </div>


        </form>
    </div>
</div>
