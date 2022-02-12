<?php
/* @var $this AdminController */
/* @var $filter ModelFilter */
/* @var $models IUser[] */
$models = $filter->getModels();
?>
<h1><?=$this->t("Blog","lib")?></h1>
<hr>
 <a href='<?=$this->createAbsoluteUrl("create")?>' class="btn btn-success btn-icon icon-left">
     <i class="entypo-plus"></i><?=$this->t("Create","lib")?></a>  
 <br/>
 <br/>
<?php if (!empty($models)): ?>
    <table class='table table-bordered'>
        <thead>
        <tr>
            <td>#</td>
            <td><?=$this->t("Title","lib")?></td>
            <td><?=$this->t("Date","lib")?></td>
            <td><?=$this->t("Actions","lib")?></td>
        </tr>
        </thead>
        <?php foreach ($models as $model): ?>
            <tr>
                <td><?= $model->getPk() ?></td>
                <td><?= $model->title ?></td>
                <td><?= $model->creationDate ?></td>
                <td>
                    <a class='btn btn-success' href="<?= $this->createAbsoluteUrl("update", ['id' => $model->getPk()]) ?>"><?=$this->t("Edit","lib")?></a>
                    <a class='btn btn-danger' href="<?= $this->createAbsoluteUrl("delete", ['id' => $model->getPk()]) ?>"><?=$this->t("Delete","lib")?></a>
                </td>
            </tr>

        <?php endforeach; ?>
    </table>
    <?php $this->renderPartial("views.admin.pagination.simple",['filter'=>$filter]);?>
<?php else: ?>
    <p><?=$this->t("There are no entries yet","lib")?></p>
<?php endif; ?>