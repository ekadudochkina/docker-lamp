<?php
/* @var $this NeonAdminController */
$menu = $this->generateMainMenu();
?>

<ul id="main-menu" class="main-menu ">
    <?php foreach ($menu as $item): ?>
        <li>
            <a href="<?= $this->createAbsoluteUrl($item[1]) ?>">
                <i class="<?= $item[2] ?>"></i>
                <span class="title"><?= $item[0]; ?></span>
                <?php if (isset($item[3]) && $item[3] > 0): ?>
                    <span class="badge badge-secondary"><?= $item[3] ?></span>
                <?php endif; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>