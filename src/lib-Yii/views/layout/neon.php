<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="<?= $this->getPageTitle() ?>" />
        <meta name="author" content="" />

        <title><?=$this->getPageTitle()?></title>

  
        
    </head>
    <body class="page-body" data-url="">
        <div class="page-container">             
            <div class="sidebar-menu">
                <div class="sidebar-menu-inner">			
                    <?= $this->renderPartial("lib-Yii.views.layout.neon.header")?> 
                    <?= $this->renderPartial("lib-Yii.views.layout.neon.mainMenu")?> 
                </div>
            </div>
            <div class="main-content">
                <?= $this->renderPartial("lib-Yii.views.layout.neon.flashMessages")?>  
                <?=$content ?>
            </div>
             <?= $this->renderPartial("lib-Yii.views.layout.neon.footer")?> 
        </div>
    </body>
</html>