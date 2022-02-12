<?php
/* @var $this SimpleAdminController */
/* @var $model ActiveRecord */

$fields = $this->getListFields();
?>
<h2><?= $this->getListTitle() ?></h2>
<hr>
<?= $this->getSubview("before-action-list") ?>
<?php $actions = $this->getActionsForList(); ?>
<?php foreach ($actions as $action): ?>
    <a href="<?= $action->getUrl() ?>" class="<?= $action->getClass() ?>">
        <?php if ($action->hasIcon()): ?>
            <i class="<?= $action->getIcon() ?>"></i>
        <?php endif; ?>
        <span><?= $action->getTitle() ?></span>
    </a>
<?php endforeach; ?>
<br/>
<br/>
<?= $this->getSubview("after-action-list") ?>
<div class="row">
    <div class="panel panel-primary">
        <table class="table table-bordered table-responsive">
            <thead>
            <tr>
                <?php if ($this->showNumbersInList()): ?>
                    <th><?= $this->getNumberSign() ?></th>
                <?php endif; ?>
                <?php foreach ($fields as $field): ?>
                    <?php $width = $this->getColumnWidthFor($field) ? "style='width: {$this->getColumnWidthFor($field)}'" : ""; ?>
                    <th <?= $width ?>>
                        <?= $this->getFieldTitle($field) ?>
                    </th>
                <?php endforeach; ?>
                <?php if ($this->hasActionsColumn()): ?>
                    <?php $width = $this->getColumnWidthFor("_actions") ? "style='width: {$this->getColumnWidthFor("_actions")}'" : ""; ?>
                    <th <?= $width ?>>
                        Actions
                    </th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; ?>
            <?php foreach ($models as $model): ?>
                <tr class="hs-row-<?= ($model->getPk() ? $model->getPk() : $i++) ?>">
                    <?php if ($this->showNumbersInList()): ?>
                        <td style="width: 20px;"><?= $this->getCurrentNumber() ?></td>
                    <?php endif; ?>
                    <?php foreach ($fields as $field): ?>
                        <td class="hs-<?= $field ?>">
                            <?= $this->getFieldValue($model, $field) ?>
                        </td>
                    <?php endforeach; ?>

                    <?php if ($this->hasActionsColumn()): ?>
                        <td>
                            <div>
                                <?php $actions = $this->getActionsForModel($model); ?>
                                <?php foreach ($actions

                                as $action): ?>
                                <?php $target = $action->opensOnNewPage() ? "target='_blank'" : "" ?>

                                <a href="<?= $action->getUrl() ?>" <?= $target ?>
                                   class="<?= $action->getClass() ?>">
                                    <?php if ($action->hasIcon()): ?>
                                        <i class="<?= $action->getIcon() ?>"></i>
                                    <?php endif; ?>
                                    <span><?= $action->getTitle() ?></span>
                                </a>
                                <?php if ($action->isEndOfRow()): ?>
                            </div>
                            <div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->renderPartial("root.lib-Yii.views.admin.pagination.simple", array("filter" => $filter)) ?>

