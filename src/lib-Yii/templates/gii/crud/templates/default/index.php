<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
$model = ActiveRecordHelper::createModelInstance($this->getModelClass());
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $models <?= $this->modelClass ?>[] */

$model = new <?= $this->modelClass ?>();
?>

<div class="container <?= '<?=' ?>strtolower(get_class($model))?> list">
    <h1><?= $this->pluralize($this->class2name($this->modelClass)) ?></h1>
    <a href='<?= "<?=" ?>$this->createAbsoluteUrl("create")?>' class='btn btn-success'>Add</a>

    <table>
        <thead>
            <tr>
                <?php foreach ($model->getAttributes() as $name => $attribute): ?>
                    <th><?= "<?=\$model->getAttributeLabel('$name')?>"; ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <?= "<?php" ?> foreach($models as $model): ?>  
        <tr>
            <?php foreach ($model->getAttributes() as $name => $attribute): ?>
                <td><?= "<?=\$model->$name?>"; ?></td>
            <?php endforeach; ?>
            <td>
                <a class="btn btn-success" href="<?= "<?=" ?>$this->createAbsoluteUrl('update',array('id'=> $model->getPk()))?>">Update</a>
                <a class="btn btn-danger" href="<?= "<?=" ?>$this->createAbsoluteUrl('delete',array('id'=> $model->getPk()))?>">Delete</a>
            </td>    
        </tr>
        <?= "<?php" ?> endforeach; ?>
    </table>
</div>