<?php /* @var $this Controller */


//$url=CHtml::asset(Yii::getPathOfAlias('application').'/assets/libs/bootstrap/css/bootstrap.css');
//Yii::app()->getClientScript()->registerCssFile($url);

//$assetsPath = Yii::getPathOfAlias('application').'/assets';
//$assetsUrl = Yii::app()->assetManager->publish($assetsPath, false, -1, YII_DEBUG);

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="ru">


    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?=$this->getAssetsUrl() ?>/css/bootstrap/bootstrap.css" rel="stylesheet">
    <link href="<?=$this->getAssetsUrl() ?>/css/font-awesome/css/font-awesome.css" rel="stylesheet">




    <link href="<?=$this->getAssetsUrl() ?>/css/styles.css" rel="stylesheet">


    <script src="<?=$this->getAssetsUrl() ?>/js/jquery.js"></script>
    <script src="<?=$this->getAssetsUrl() ?>/js/bootstrap/bootstrap.js"></script>


    <script src="https://unpkg.com/aos@2.3.0/dist/aos.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/vivus@latest/dist/vivus.min.js"></script>


    <script src="<?=$this->getAssetsUrl() ?>/js/main.js"></script>



    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.0/dist/aos.css" />

    <title>Glosku</title>
</head>

<body>


<?= $this->renderPartial("application.views.layouts.main.flashMessages") ?>

<div class="content" id="page">

    <div class="mobile-menu" id="mobile-menu">
        <div class="top">
            <div class="logo">
                <img src="<?=$this->getAssetsUrl() ?>/images/logo.png">
            </div>
            <div class="icon" id="mobile-menu-close">
                <i class="fa fa-times" aria-hidden="true"></i>
            </div>
        </div>

        <div class="list align-self-center">
            <ul>
                <li class=""><a href="./why.html">Why GLOSKU?</a></li>
                <li class=" active"><a href="./solutions.html">Solutions</a></li>
                <li class=""><a href=/">Our Company</a></li>
                <li class=""><a href="/">Blog</a></li>
                <li class=""><a href="/">Contact Us</a></li>

                <li class=""><a class="" href="/">Log in</a></li>
                <li class="buttons"><a href="/" class="button blue-button">get started now <span class="arrow"><img src="<?=$this->getAssetsUrl() ?>/images/home/button-arrow.png"></span></a></li>
            </ul>
        </div>
    </div>

 <header class="header" id="header">
            <div class="container">
                <div class="row">

                    <div class="col-lg-2 col-md-12">
                        <a href="/"><div class="logo"> <img src="<?=$this->getAssetsUrl() ?>/images/logo.png"></div></a>

                        <div class="menu-button" id="menu-button">
                            <i class="fa fa-bars" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="navigation col-md-10 col-lg-10">

                                <div class="menu">
                                        <ul  class="navigation-menu">
                                            <li class="link"><a href="./why.html">Why GLOSKU?</a></li>
                                            <li class="link active"><a href="./solutions.html">Solutions</a></li>
                                            <li class="link"><a href=/">Our Company</a></li>
                                            <li class="link"><a href="/">Blog</a></li>
                                            <li class="link"><a href="/">Contact Us</a></li>
                                        </ul>

                                    <div class="buttons">
                                        <a href="/" class="log">Log in</a>
                                        <a href="/" class="button blue-button">get started now <span class="arrow"><img src="<?=$this->getAssetsUrl() ?>/images/home/button-arrow.png"></span></a>
                                    </div>
                                 </div>

                    </div>
                </div>
            </div>
    </header>
    <!-- header -->


    <?=$content?>

    <div class="clear"></div>

    <footer class="footer">
        <div class="container">
            <div class="row d-flex justify-content-between">
                <div class="col-md-6">
                    <div class="left">
                        <div class="logo"> <a href="/"><img src="<?=$this->getAssetsUrl() ?>/images/logo2.png" /></a></div>
                        <div class="info">We empower eCommerce-based businesses to reach markets in China. By taking care of shipping compliance, APP integration, influencer and affiliate connections, and more, we accelerate the growth and profitability potential of your business</div>
                        <div class="copyright">© Copyright 2020 - GLOSKU Inc - All Rights Reserved.</div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="footer-element">
                        <div class="title">Company</div>

                        <div class="list">
                            <ul>
                                <li><a href="">Company</a></li>
                                <li><a href="">Solutions</a></li>
                                <li><a href="">Our Company</a></li>
                                <li><a href="">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="footer-element">
                        <div class="title">Integrations</div>

                        <div class="list">
                            <ul>
                                <li><a href="">Shopify</a></li>
                                <li><a href="">WooCommerce</a></li>
                                <li><a href="">Magento</a></li>
                                <li><a href="">BigCommerce</a></li>
                                <li><a href="">Custom Integration</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mr-auto">
                    <div class="footer-element">
                        <div class="title">LEGAL</div>

                        <div class="list">
                            <ul>
                                <li><a href="">Privacy</a></li>
                                <li><a href="">T&C</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mobile-copyright">© Copyright 2020 - GLOSKU Inc - All Rights Reserved.</div>
        </div>
    </footer><!-- footer -->

</div><!-- page -->

</body>
</html>


