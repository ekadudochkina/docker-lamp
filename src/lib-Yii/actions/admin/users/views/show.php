<?php
/* @var $this ViewAction */
/* @var $filter ModelFilter */
/* @var $models IUser[] */
$models = $filter->getModels();

?>
<h1>Users</h1>
<?php if (!empty($models)): ?>
    <table class='table table-bordered'>
        <thead>
        <tr>
            <td>Name</td>
            <td>Login</td>
            <td>Email</td>
            <td>Registered</td>
            <td>Actions</td>
        </tr>
        </thead>
        <?php foreach ($models as $model): ?>
            <tr>
                <td><?= $model->getName() ?></td>
                <td><?= $model->getLogin() ?></td>
                <td><?= $model->getEmail() ?></td>
                <td><?= $model->creationDate ?></td>
                <td>
                    <a class='btn btn-success' href="<?= $this->createAbsoluteUrl("edit", ['id' => $model->getLogin()]) ?>">Edit</a>
                </td>
            </tr>

        <?php endforeach; ?>
    </table>
    <?php $this->renderPartial("views.admin.pagination.simple",['filter'=>$filter]);?>
<?php else: ?>
    <p>There are no users yet </p>
<?php endif; ?>
