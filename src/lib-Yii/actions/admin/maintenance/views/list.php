<?php
/* @var $this AdminController */
/* @var $manager  MaintenanceManager */

$ips = $manager->getIps();
?>
<h1>Maintenance</h1>
<hr>
<div class='panel'>
    <div class='col-sm-6'>
        <?php if ($manager->isAvailable()): ?>
            <a href='<?= $this->createAbsoluteUrl($this->getAction()->getId(), ['action' => "enable"]) ?>' class="btn btn-danger btn-icon icon-left">
                <i class="entypo-down"></i>
                <span>Enable maintanence</span>
            </a>  
        <?php else: ?>
            <a href='<?= $this->createAbsoluteUrl($this->getAction()->getId(), ['action' => "disable"]) ?>' class="btn btn-success btn-icon icon-left">
                <i class="entypo-up"></i>
                <span>Disable maintanence</span>

            </a>  
        <?php endif; ?>
    </div>
    <div class='col-sm-6'>
        <form method='post' >
            <input type='text' name='ip' class='form-control-sm' />
            <button class='btn btn-success' type='submit'>Add IP</button>
            <div>
                Your current IP is: <?=EnvHelper::getClientIp()?>
            </div>
        </form>

    </div>

</div>
<br/>
<br/>
<?php if (!empty($ips)): ?>
    <table class='table table-bordered'>
        <thead>
            <tr>
                <td>#</td>
                <td>Ip</td>

                <td>Actions</td>
            </tr>
        </thead>
        <?php $i = 0; ?>
        <?php foreach ($ips as $ip): ?>
            <tr>
                <td><?= ++$i ?></td>
                <td><?= $ip ?></td>
                <td>
                    <a class='btn btn-danger' href="<?= $this->createAbsoluteUrl($this->getAction()->getId(), ['id' => $i - 1, 'action' => "delete"]) ?>">Remove</a>
                </td>
            </tr>

        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>There are no entries yet </p>
<?php endif; ?>