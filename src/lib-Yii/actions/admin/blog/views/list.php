<?php
/* @var $this AdminController */
/* @var $filter ModelFilter */
/* @var $models IUser[] */
$models = $filter->getModels();
?>
<h1>Blog</h1>
<hr>
 <a href='<?=$this->createAbsoluteUrl("create")?>' class="btn btn-success btn-icon icon-left"><i class="entypo-plus"></i>Create</a>  
 <br/>
 <br/>
<?php if (!empty($models)): ?>
    <table class='table table-bordered'>
        <thead>
        <tr>
            <td>#</td>
            <td>Title</td>
            <td>Date</td>
            <td>Actions</td>
        </tr>
        </thead>
        <?php foreach ($models as $model): ?>
            <tr>
                <td><?= $model->getPk() ?></td>
                <td><?= $model->title ?></td>
                <td><?= $model->creationDate ?></td>
                <td>
                    <a class='btn btn-success' href="<?= $this->createAbsoluteUrl("update", ['id' => $model->getPk()]) ?>">Edit</a>
                    <a class='btn btn-danger' href="<?= $this->createAbsoluteUrl("delete", ['id' => $model->getPk()]) ?>">Delete</a>
                </td>
            </tr>

        <?php endforeach; ?>
    </table>
    <?php $this->renderPartial("views.admin.pagination.simple",['filter'=>$filter]);?>
<?php else: ?>
    <p>There are no entries yet </p>
<?php endif; ?>