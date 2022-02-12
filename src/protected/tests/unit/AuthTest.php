<?php


class AuthTest extends AmazonWebUnitTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->shouldResetDb = false;
    }

    /**
     * Тестируем вход в систему
     * @throws Exception
     */
    public function testLogin()
    {
        $this->login("12345");
        $html = $this->forward("shop/search");
        $this->assertContains("Add multiple amazon stores to asin24",$html);
//        $this->log($html);
    }

    /**
     * Проверка платежных данных пользователей после регистрации
     * @throws Exception
     */
    public function testRegistration()
    {
        $userCode = new UserCode();
        $userCode->code = $userCode->generateCode();
        $this->assertSave($userCode);
        $html = $this->forward("auth/register",["code" => $userCode->code]);
        $this->assertContains("Registration currently available only with invites",$html);

        $password = "1q2w3e4rDD";
        $user = $this->generateUser();
        $registrationForm = new SimpleRegistationForm(new User(),new SimpleUserProvider(new User()),new MockController());
        $registrationForm->email = $user->email;
        $registrationForm->password = $password;
        $registrationForm->passwordConfirm = $password;
        $params = ["userCode" => $userCode->code, "code" => $userCode->code];
        $params = \Hs\Helpers\TestHelper::modelToRequest($registrationForm,$params);
//        bug::drop($params);
        $html = $this->forward("auth/register",$params);
        $this->assertContains("You succesfuly registered",$html);

        $savedUser = User::model()->findByAttributes(["email" => $user->email]);
        $this->assertNotNull($savedUser,"Пользователь не найден");

        $plans = $savedUser->getPlans();
        $this->assertCount(1,$plans);
        $trialPlan = $plans[0];
        $shouldBe = 5; //5
        $this->assertEquals($shouldBe,$trialPlan->products,"Неверное количество продуктов в плане");
        $this->assertNotNull($trialPlan->getPk(),"План должен быть реальным");
    }
}