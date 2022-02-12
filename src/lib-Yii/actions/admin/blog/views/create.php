<?php
/* @var $this AdminController */
/* @var $model BlogArticle */

$helper = $this->getFormHelper();
?>
<h3><?= $model->isNew() ? "Create" : "Edit" ?> Article</h3>
<hr>
<div class="panel ">
    <div class="panel-body">
        <form method="post" class="form-horizontal " enctype="multipart/form-data" >


            <div class="form-group">  
<!--                <p class="note">Fields with <span class="required">*</span> are required.</p>-->

                <?= $helper->errorSummary($model); ?>

                <div class="">
                    <h4><?= $helper->labelEx($model, 'title'); ?></h4>
                    <?= $helper->textField($model, 'title', ['class' => 'form-control']); ?>
                    <?= $helper->error($model, 'title'); ?>
                </div>

                <?php if (property_exists(get_class($model), "mainImage")) { ?>
                    <div class=""> 
                        <h4><?= $helper->labelEx($model, "mainImage", array("class" => "")) ?></h4>
                            <?= Image::fileField() ?>
                            <?= Image::error(); ?>                       
                    </div>
                <?php } ?>  

                <div class="">
                    <h4><?= $helper->labelEx($model, 'preview'); ?></h4>

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
                    <h4><?= $helper->labelEx($model, 'text'); ?></h4>
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
                    <a class="btn btn-default" href="<?= $this->createAbsoluteUrl('index') ?>">Back</a>
                    <button type="submit" class="btn btn-success"><?= $model->isNew() ? "Create" : "Save" ?></button>
                </div>

            </div>


        </form>
    </div>
</div>
