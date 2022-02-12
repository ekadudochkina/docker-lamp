<?php

/**
 * Description of ExtraAdminController
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
abstract class ExtraAdminController extends SimpleAdminController
{

    public function getPageTitle()
    {
        $ret = parent::getPageTitle()." (".AmazonHelper::getVersion().")";
        return $ret;
    }

    public function getApplicationName()
    {
        $ret = "ASIN24";
        return $ret;
    }

    /**
     * Создает главное меню
     * 
     * @return string[]
     */
    public function generateMainMenu()
    {
        $menu = array();
        $menu[] = array('Users','extraUsers/index','entypo-users right');
        $menu[] = array('Admins','extraAdmins/index','entypo-user right');
        $menu[] = array('Edit password','extraPasswordReset/index','entypo-key right');
        $menu[] = array('Found Sellers','extraFoundSellers/index','entypo-vcard right');
        $menu[] = array('Sellers','extraSellers/index','entypo-vcard right');
        $menu[] = array('Found Categories','extraFoundCategories/index','entypo-flow-tree right');
        $menu[] = array('Found Categories Products','ExtraCategoryProducts/index','entypo-box right');
        $menu[] = array('Found Categories Sellers','extraCategorySellers/index','entypo-vcard right');
        $menu[] = array('Daemons','extraDaemons/index','entypo-rocket right');
        $menu[] = array('Nodes','extraNodes/index','entypo-cloud right');
        $menu[] = array('Parallel Execution','extraParallels/index','entypo-shuffle right');
        $menu[] = array('Subdomains','extraSubdomains/index','entypo-network right');
//        $menu[] = array('Proxy Lists','extraProxy/index','entypo-shuffle right');
        $menu[] = array('Cycle Logs','extraCycles/index','entypo-doc-text right');
        $menu[] = array('Task Logs','extraTaskLogs/index','entypo-list right');
        $menu[] = array('Action Logs','extraActionLogs/index','entypo-list right');
        $newErrors = WorkflowError::model()->getNewErrorsNumber();
        $menu[] = array('Workflow Errors','extraErrors/index','entypo-attention right',$newErrors);
        $menu[] = array('System','extraSystem/index','entypo-flash right');
//        $menu[] = array('Version','extraVersion/index','entypo-github right');
        $menu[] = array('Proxy Statistics','ExtraProxyStats/index','entypo-chart-pie right');
        $menu[] = array('Sellers Statistics','ExtraSellerStats/index','entypo-chart-area right');
        $menu[] = array('Codes','ExtraCodes/index','entypo-publish right');
        $menu[] = array('Blog','ExtraBlog/index','entypo-book right');
        $menu[] = array('Alerts','ExtraAlert/index','entypo-sound right');
        $menu[] = array('Log Out','logout','entypo-logout right');

        return $menu;
    }
    
    
       
      public function beforeAction($action) {
        $ret = parent::beforeAction($action);

        $this->addCSSFile("admin.css");
        if(AmazonHelper::isSiteServer() && !EnvHelper::isLocal()){
            $this->redirectToRoute("site/index");
        }

        return $ret;
    }
}
