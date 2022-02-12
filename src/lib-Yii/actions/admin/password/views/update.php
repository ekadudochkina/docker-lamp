<?php
/* @var $this BaseControlller */
/* @var $model PasswordCHangeForm */

$helper = $this->getFormHelper();
?>
<h1><?= $this->t("Edit Password", "lib") ?></h1>
<hr>
<div class="row">
    <div class="panel ">
        <div class="panel-body">
            <form method="post" class="form-horizontal" >


                <div class="form-group">  

                    <div class="form-group">  
                        <label class="col-sm-3 control-label"><?= $this->t("Old Password", "lib") ?></label>
                        <div class="col-sm-5">
                            <?= $helper->passwordField($model, 'oldPassword', ['class' => 'form-control']); ?>
                            <?= $helper->error($model, 'oldPassword'); ?>
                        </div>
                    </div>

                    <div class="form-group">  
                        <label class="col-sm-3 control-label"><?= $this->t("New Password", "lib") ?></label>
                        <div class="col-sm-5">
                            <?= $helper->passwordField($model, 'newPassword', ['class' => 'form-control']); ?>
                            <?= $helper->error($model, 'newPassword'); ?>
                        </div>
                    </div>
                    <div class="form-group">  
                        <label class="col-sm-3 control-label"><?= $this->t("Password Confirm", "lib") ?></label>
                        <div class="col-sm-5">
                            <?= $helper->passwordField($model, 'passwordConfirm', ['class' => 'form-control']); ?>
                            <?= $helper->error($model, 'passwordConfirm'); ?>
                        </div>
                    </div>


                    <div class="form-group"> 
                        <div class="col-sm-offset-3 col-sm-5"> 
                            <button type="submit" class="btn btn-success"><?= $this->t("Save", "lib") ?></button>
                        </div>
                    </div>
                </div>


            </form>
        </div>
    </div>
</div>
