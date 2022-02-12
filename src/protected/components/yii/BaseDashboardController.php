<?php
/**
 * Базовый Контроллер для Dashboard
 *
 */
abstract class BaseDashboardController extends Controller {

  
    public function beforeAction($action)
    {
        $ret = parent::beforeAction($action);
        $this->setLayout("dashboard");

        $this->addCssFile("scroll/jquery.jscrollpane.css");
        $this->addCssFile("select/jquery.formstyler.css");
        $this->addCssFile("select/jquery.formstyler.theme.css");
        $this->addCssFile("style.css");
        $this->addCssFile("site.css");

        $this->addJavascriptFile("dashboard.js");

        //скролинг
        $this->addJavascriptFile("libs/scroll/jquery.mousewheel.js");
        $this->addJavascriptFile("libs/scroll/jquery.jscrollpane.js");

        //Шлаги для стран
        $this->addCssFile("flags/bootstrap-formhelpers.css");
        $this->addJavascriptFile("libs/bootstrap-formhelpers.js");

        $this->addJavascriptFile("libs/select/jquery.formstyler.min.js");
        $this->addJavascriptFile("all.js");

        //новый дизайн
        $this->addCssFile("asin24.css");
        //Попапы
        $this->addJavascriptFile("libs/tether/tether.min.js");

        $checker = new AuthorizedAccessChecker(null);
        $checker->checkAccessAndRedirect($this, "landing/index");


        return $ret;
    }

    protected function safeDelete(ActiveRecord $model) {
        try {
            $model->delete();
        } catch (CDbException $ex) {
            if (StringHelper::hasSubstring($ex->getMessage(), "ntegrity constraint violation")) {
                $model->addActionError("Object cannot be deleted because it's already bound with other objects");
                return false;
            }
            throw $ex;
        }
        return true;
    }

}
