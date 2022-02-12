<?php

/**
 * Контроллер для администраторов
 *
 * @package Hs\Controllers
 * @author Dudochkina Ekaterina <edudochkina@home-studio.pro>
 */
class NeonAdminController extends BaseController {

    /**
     * @param string $id id of this controller
     * @param CWebModule $module the module that this controller belongs to.
     */
    public function __construct($id, $module = null) {
        $this->layout = "admin";
        parent::__construct($id, $module);
    }

    /**
     * Получение названия страницы, ктороое будет отображено на вкладке в браузере
     * 
     * @return String
     */
    public function getPageTitle() {
        return $this->getApplicationName() . " | Control panel";
    }

    /**
     * Получение названия текущего приложения.
     * Оно отображается на нескольких страницах панели управления, например на странице авторизации
     * 
     * @return string
     */
    public function getApplicationName() {
        return "Home Studio";
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    public function beforeAction($action) {
        $ret = parent::beforeAction($action);

        $this->setLayout("lib-Yii.views.layout.neon");
        $this->addAssets();
        //Выкидываем не пользователей      

        if ($action !== "passwordReset" && $action !== "passwordResetConfirm") {
            $checker = new ClassAccessChecker($this->getUserProvider()->getUserClass());
            $checker->checkAccessAndRedirect($this, "login");
        }


        return $ret;
    }

    /**
     * Получение провайдера пользователей-администраторов
     * 
     * @return \EmailUserProvider
     */
    public function getUserProvider() {
        return new EmailUserProvider(new SimpleAdmin(), "/");
    }

    /**
     * Экшен по-умолчанию.
     */
    public function actionIndex() {
        $this->render("lib-Yii.views.layout.neon.hello");
    }

    /**
     * Авторизация админа
     */
    public function actionLogin() {
        $this->setLayout(null);
        $provider = $this->getUserProvider();

        $loginForm = new SimpleLoginForm($provider, $this);

        if ($this->setParamsToModels($loginForm)) {
            $loggedIn = $loginForm->login();
            if ($this->showActionMessage($loggedIn, $loginForm, $this->t("You successfully logged in", "lib"))) {
                $this->redirectToRoute("index");
            }
        }

        //bug::drop($this->getError());
        $this->addViewData($loginForm, "loginForm");
        $this->render("lib-Yii.views.layout.neon.login");
    }

    /**
     * Выход админа из системы
     */
    public function actionLogout() {
        $form = new SimpleLoginForm($this->getUserProvider(), $this);
        $form->logout();
        $this->redirectToRoute("/");
    }

    /**
     *  На админку технические работы не распространяются
     */
    public function processMaintance() {
        //ничего не делаем
        return true;
    }

    /**
     * Возвращает массив массивов элементов главного меню
     * @return Array
     */
    public function generateMainMenu() {
        $menu = array();
        $menu[] = array('Log Out', 'logout', 'entypo-logout right');
        return $menu;
    }

    /**
     * Добавляем ассеты. Их много, поэтому выделил отдельный метод
     */
    public function addAssets() {
        $this->addCSSFile("js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css", "neon");
        $this->addCSSFile("css/font-icons/entypo/css/entypo.css", "neon");
        $this->addCSSFile("css/bootstrap.css", "neon");
        $this->addCSSFile("css/neon-core.css", "neon");
        $this->addCSSFile("css/neon-theme.css", "neon");
        $this->addCSSFile("css/neon-forms.css", "neon");
        $this->addCSSFile("css/custom.css", "neon");
        $this->addCSSFile("/js/zurb-responsive-tables/responsive-tables.css", "neon");


        $this->addJavascriptFile("js/jquery-1.11.0.min.js", null, "neon");
        $this->addJavascriptFile("js/gsap/main-gsap.js", null, "neon");
        $this->addJavascriptFile("js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js", null, "neon");
        $this->addJavascriptFile("js/bootstrap.js", null, "neon");
        $this->addJavascriptFile("js/joinable.js", null, "neon");
        $this->addJavascriptFile("js/neon-api.js", null, "neon");
        $this->addJavascriptFile("js/jquery.validate.min.js", null, "neon");
        $this->addJavascriptFile("js/neon-login.js", null, "neon");
        $this->addJavascriptFile("js/neon-custom.js", null, "neon");
        $this->addJavascriptFile("js/neon-demo.js", null, "neon");


        $this->addJavascriptFile("js/gsap/main-gsap.js", null, "neon");
        $this->addJavascriptFile("js/bootstrap.js", null, "neon");
        $this->addJavascriptFile("js/joinable.js", null, "neon");
        $this->addJavascriptFile("js/resizeable.js", null, "neon");
        $this->addJavascriptFile("js/neon-api.js", null, "neon");
        //Чат? Нееенадо
        //$this->addJavascriptFile("/js/neon/neon-chat.js", null, "neon");
        $this->addJavascriptFile("/js/neon-custom.js", null, "neon");
        $this->addJavascriptFile("/js/neon-demo.js", null, "neon");
    }

    /**
     * Восстановление пароля администратора, при помощи его email адреса
     */
    public function actionPasswordReset() {
        $this->setLayout(null);
        $model = new SimplePasswordResetForm($this, $this->getUserProvider());

        if ($this->setParamsToModels($model)) {
            $email = $model->email;
             $user = $this->getUserProvider()->findByEmail($email);
            if ($user) {
                $code = $this->getUserProvider()->createCode($user);
                $url = $this->createAbsoluteUrl("passwordResetConfirm", array("id" => $code));

                $mailer = $this->getMailer();
                $mailer->AddAddress($email);
                $mailer->Subject = $this->t("Password reset on", "lib")." " . $this->getApplicationName();
                $mailer->Body = $this->t("Greetings,", "lib")." ". $user->getName() . ". ". $this->t("Click here to reset password:", "lib") . " <a href='". $url ."'>". $url ."</a>";
                $result = $mailer->Send();

                if ($result) {
                    $this->showSuccessMessage($this->t("We sent you email with instructions", "lib"));
                    $this->redirectToRoute("login");
                } else {
                    $this->showErrorMessage($model->getFirstError());
                }
            } else {
                $this->showErrorMessage($this->t("This e-mail address is not registered", "lib"));
            }
        }

        $this->addViewData($model, "model");
        $this->render("lib-Yii.views.layout.neon.passwordReset");
    }

    /**
     * Завершение восстановления пароля для пользователя
     */
    public function actionPasswordResetConfirm($id) {
        $this->setLayout(null);
        $resetForm = new SimplePasswordResetForm($this, $this->getUserProvider());

        $model = $resetForm->getPasswordChangeForm();

        if ($model == null) {
            $this->showErrorMessage($this->t("Error!Link is invalid.", "lib"));
            $this->redirectToRoute("login");
        }
        if ($this->setParamsToModels($model)) {
            $result = $model->changePassword();

            if ($result) {
                $this->showSuccessMessage($this->t("Password has been succesfuly set", "lib"));
                $this->redirectToRoute("login");
            }
        }

        $this->addViewData($model, "model");
        $this->render("lib-Yii.views.layout.neon.passwordResetConfirm");
    }

}
