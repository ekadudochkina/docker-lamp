<?php


class AmazonWebUnitTestCase extends \Hs\Test\WebUnitTestCase
{

    protected $shouldResetDb = false;

    /**
     * Проверяет сохранение моделей
     *
     * @param ActiveRecord $model
     */
    protected function assertSave(ActiveRecord $model)
    {
        $saved = $model->save();
        if(!$saved)
        {
            $error = $model->getFirstError();
            $msg = print_r($model->getAttributes(),true);
            $this->fail("Не удается сохранить модель ".get_class($model).", ошибка: '$error' \n $msg");
        }
        $this->assertTrue(true);
    }

    /**
     * Создает пользователя и входит за него в Asin24
     * @param $password
     * @param User $user
     * @throws Exception
     */
    protected function login($password, User $user = null)
    {
        $user = $user ? $user : $this->generateUser($password);
        $this->assertSave($user);
        $form = new SimpleLoginForm(new SimpleUserProvider(new User()), new MockController());
        $form->login = $user->email;
        $form->password = $password;
        $form->rememberMe = false;
        $request = \Hs\Helpers\TestHelper::modelToRequest($form);

        $result = $this->forward("auth/login", $request);

        $this->assertContains("You succesfuly logged in", $result);
//        $this->log($result);
        return $user;
    }

    /**
     * Создает пользователя
     * @param $password
     * @return User
     */
    protected function generateUser($password = "12345",$createTrialPlan = false)
    {
        $user = new User();
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime(EnvHelper::now());
        $email = null;
        do {
            $number = rand(0, 100000);
            $email = "test$number@asin24.com";
            $existingUser = User::model()->findByAttributes(["email" => $email]);
        } while ($existingUser != null);
        $user->email = $email;
        $user->setPassword($password);

        $plan = $user->createPlan(AmazonHelper::getMinimumProductNumberForPlan());
        $plan->creationDate = DateTimeHelper::timestampToMysqlDateTime(EnvHelper::now());
        $plan->isTrial = true;
        if($createTrialPlan)
        {
            $plan->setRelated($user);
        }

        return $user;
    }

    protected function getClientParams($result)
    {
        $parts = explode("var clientParams =", $result);
        if (count($parts) <= 1) {
            return [];
        }
        $right = $parts[1];
        $parts2 = explode('/*]]>*/', $right);
        $middle = trim($parts2[0]);
        $middle = substr($middle, 0, strlen($middle) - 1);
        $params = json_decode($middle, true);
//        bug::reveal($params,$middle);
        return $params;
    }

    protected function getErrorNotification($html)
    {
        $params = $this->getClientParams($html);
        if (!isset($params["FlashMessage"])) {
            return null;
        }
        $msg = $params["FlashMessage"];
        if ($msg["status"] == "danger") {
            return $msg["message"];
        }
        return null;
    }
}