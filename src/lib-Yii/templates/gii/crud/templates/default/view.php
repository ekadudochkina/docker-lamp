<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */
?>

<div class="view <?= '<?=' ?>strtolower(get_class($model))?>">
    <h3>View <?php echo $this->modelClass . " #<?=\$model->getPk()?>"; ?></h3>

    <?php foreach ($this->tableSchema->columns as $column): ?>
        <div class="row">
            <label><?= "<?=" ?>$model->getAttributeLabel('<?= $column->name ?>')?></label>
            <span><?= "<?=" ?>$model-><?= $column->name ?>?></span>
        </div>
    <?php endforeach; ?>

    <a class="btn btn-default" href="<?= "<?=" ?>$this->createAbsoluteUrl('index')?>">Back</a>
    <a class="btn btn-success" href="<?= "<?=" ?>$this->createAbsoluteUrl('update', array('id'=>$model->getPk()))?>">Edit</a>
</div>