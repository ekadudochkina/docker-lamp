<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
$model = ActiveRecordHelper::createModelInstance($this->getModelClass());
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getModelClass() . "Controller"; ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */

$helper = $this->getFormHelper();
?>

<div class="form <?= "<?=" ?>$model->isNew()? "create" : "edit"?> <?= strtolower($this->modelClass) ?>">
     <h3>Create <?php echo $this->modelClass; ?></h3>
    <form method="post">

        <p class="note">Fields with <span class="required">*</span> are required.</p>

        <?php echo "<?=\$helper->errorSummary(\$model); ?>\n"; ?>

        <?php foreach ($model->getAttributes() as $name => $attribute): ?>
            <?php
            if (in_array($name, array("id")))
                continue;
            ?>
            <div class="row">
                <?= "<?=\$helper->labelEx(\$model,'$name'); ?>\n"; ?>
                <?= "<?=\$helper->textField(\$model,'$name'); ?>\n"; ?>
                <?= "<?=\$helper->error(\$model,'$name'); ?>\n"; ?>
            </div>

        <?php endforeach; ?>
        <div class="row buttons">
            <button type="submit" class="btn btn-success"><?= "<?=" ?>$model->isNew() ? "Create" : "Save"?></button>
        </div>
    </form>
    <a class="btn btn-default" href="<?= "<?=" ?>$this->createAbsoluteUrl('index')?>">Back</a>
</div>