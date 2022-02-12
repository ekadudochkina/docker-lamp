<?php
namespace Hs\Helpers;

/**
 * Хелпер для работы с тестами
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class TestHelper
{
    /**
     * Получение последней модели в базе данных
     * @param CActiveRecord $modelObj
     * @return CActiveRecord
     */
    static function getLatestModel(\CActiveRecord $modelObj)
    {
        $model = $modelObj->find(["order" => "id DESC"]);
        return $model;
    }

    /**
     * Заполнение данных формы страйпа
     */
    public static function purchaseWithStripe($browser)
    {
        //Ждем страйп
        $browser->waitForElement(".stripe-button-el");
        $stripeButton = $browser->findElementBySelector(".stripe-button-el");
        $stripeButton->click();
        sleep(1);
        
         //Переключаемся на iframe
        $iframe = $browser->findElementBySelector("iframe.stripe_checkout_app");
        $browser->switchTo()->frame($iframe);
        
        //Заполняем и отправляем форму
        $card = $browser->findElementBySelector("input[placeholder='Card number']");
        $card->sendKeys("5555555555554444");
        $expire = $browser->findElementBySelector("input[placeholder='MM / YY']");
        $expire->sendKeys("1021");
        $code = $browser->findElementBySelector("input[placeholder='CVC']");
        $code->sendKeys("123");
        $payButton = $browser->findElementBySelector("button[type='submit']");
        $payButton->click();
        $browser->switchTo()->defaultContent();
    }

    public static function loginWithCredentials($browser, $provider)
    {
        $login = $provider->getUserName();
        $password = $provider->getPassword();
        $loginRoute = $provider->getLoginRoute();
        $loginSelector = $provider->getLoginInputSelector();
        $passwordSelector = $provider->getPasswordInputSelector();
        $submitSelector = $provider->getSubmitLoginFormSelector();
        
        $browser->getRoute($loginRoute);
        $browser->findElementBySelector($loginSelector)->sendKeys($login);
        $browser->findElementBySelector($passwordSelector)->sendKeys($password);
        $browser->findElementBySelector($submitSelector)->click();
    }

    public static function modelToRequest(\CModel $model,$array = [])
    {
        $attrs = $model->getAttributes();
        $name = get_class($model);
        $array[$name] = $attrs;
        return $array;
    }

}
