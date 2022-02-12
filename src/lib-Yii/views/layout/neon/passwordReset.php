<!DOCTYPE html>
<html lang="en">
    <?php
    /* @var $this NeonAdminController  */
    ?>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="Home Studio Control Panel" />
        <meta name="author" content="" />

        <title><?= $this->getPageTitle() ?></title>


        <!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <?php
        /* @var $loginForm SimpleLoginForm */
        /* @var $registrationForm SimpleRegistrationForm */
        /* @var $this AuthController */
        $form = new ActiveForm();
        ?>
    </head>
    <body class="page-body login-page login-form-fall" data-url="">
        <?= $this->renderPartial("lib-Yii.views.layout.neon.flashMessages") ?> 

        <script type="text/javascript">
            var baseurl = '';
        </script>
        <div class="login-container">
            <div class="login-header login-caret">
                <div class="login-content">
      
                        <h2 style="margin: 0px; color: #FFF;font-size: 25px;font-weight: bold; text-transform: uppercase"><?= $this->getApplicationName() ?></h2>
                        <p style="text-align: center;font-size: 11px;"><?= $this->t("CONTROL PANEL", "lib") ?></p>
 
                    <p class="description"><?= $this->t("Password Reset", "lib") ?></p>

                </div>

            </div>	
            <div class="login-progressbar">
                <div></div>
            </div>	
            <div class="login-form">		
                <div class="login-content">			
                    <div class="form-login-error">
                        <h3>Invalid login</h3>
                        <p></p>
                    </div>        
                    <div id="form_login">	    
                        <form method="post">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="entypo-user"></i>
                                    </div>

                                    <?= $form->textField($model, "email", array("placeholder" => "Email", 'size' => 60, 'maxlength' => 128, "class" => "form-control")) ?>

                                </div>
                                <?= $form->error($model, "email") ?>
                            </div>                      


                            <br>
                            <div class="form-group">

                                <button type="submit" class='btn btn-primary btn-block btn-login'><?= $this->t("Send", "lib"); ?></button>

                            </div>                         

                        </form>

                    </div>

                </div>		
            </div>	
        </div>

    </body>
</html>