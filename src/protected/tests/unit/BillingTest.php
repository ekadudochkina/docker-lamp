<?php


class BillingTest extends \AmazonWebUnitTestCase
{

    /**
     * Тестируем то, что пользователь выходит из долга, если внести платеж
     */
    public function testDebtFix()
    {
        $startMonth = DateTimeHelper::mysqlDateToTimestamp("2019-07-01 12:00:00");
        EnvHelper::setNow($startMonth);
        $user = $this->generateUser();
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime($startMonth);
        $this->assertSave($user);

        $plan = $user->createPlan(50);
        $this->assertSave($plan);
        $user->refresh();

        //Подготавливаем платеж
        $payment = $user->createPlanPayment($plan);
        $payment->status = Payment::STATUS_SUCCESS;


        //Путешествуем во времени и првоеряем, что долг есть
        $middleOfMonth = DateTimeHelper::mysqlDateToTimestamp("2019-07-20 12:00:00");
        EnvHelper::setNow($middleOfMonth);
        $debt = $user->getDebt();
        $this->assertNotEquals(0, $debt, "Долг не должен быть 0");


        //Сохраняем платеж и проверяем, что долг исчез
        $this->assertSave($plan);

        $debt = $user->getDebt();
        $this->assertEquals(0, $debt, "Долг должен быть 0");
    }


    /**
     * Тестирует, что вначале месяца у пользователя, который не заплатил все еще есть какой-то срок, когда он все еще обрабатывается
     * @throws CException
     */
    public function testDebtFreeDays()
    {
        $startMonth = DateTimeHelper::mysqlDateToTimestamp("2019-07-01 12:00:00");
        EnvHelper::setNow($startMonth);
        $user = $this->generateUser();
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime($startMonth);
        $this->assertSave($user);

        $plan = $user->createPlan(50);
        $this->assertSave($plan);
        $user->refresh();

        //Подготавливаем платеж
        $payment = $user->createPlanPayment($plan);
        $payment->status = Payment::STATUS_SUCCESS;
        $this->assertSave($plan);


        $debt = $user->getDebt();
        $this->assertEquals(0,$debt,"Долг должен быть 0");

        $nextMonth = DateTimeHelper::mysqlDateToTimestamp("2019-08-01 12:00:00");
        EnvHelper::setNow($nextMonth);
        $debt = $user->getDebt();
        $shouldBe = 100; //50*2
        $this->assertEquals($shouldBe,$debt,"Долг должен быть $shouldBe");

        $numberOfDebtDays = 7; //дней, которые можно работать без оплаты
        for($i = 0; $i < $numberOfDebtDays; $i++) {

            $date = DateTimeHelper::timestampToMysqlDate(EnvHelper::now());
            $enabledIds = $user->findEnabledIds();
            $enabled = in_array($user->getPk(), $enabledIds);
            $this->assertTrue($enabled, "Пользователь должен обрабатываться $date числа");
            $next = EnvHelper::now() + 60*60*24;
            EnvHelper::setNow($next);
            $this->log("$date : ".($enabled ? "processed" : "not processed"));
        }

        $numberOfDebtDays = 40; //проверяем на 40 дней вперед, чтобы начало следующего месяца также попало
        for($i = 8; $i <= $numberOfDebtDays; $i++) {

            $date = DateTimeHelper::timestampToMysqlDate(EnvHelper::now());
            $enabledIds = $user->findEnabledIds();
            $enabled = in_array($user->getPk(), $enabledIds);
            $this->assertFalse($enabled, "Пользователь не должен обрабатываться $date числа");
            $next = EnvHelper::now() + 60*60*24;
            EnvHelper::setNow($next);
            $this->log("$date : ".($enabled ? "processed" : "not processed"));
        }
    }

    /**
     * Тестируем, что новый пользователь не имеет долга
     */
    public function testDebtAfterRegistration()
    {
        $user = $this->generateUser();
        $this->assertSave($user);
        $debt = $user->getDebt();
        $hasDebt = $user->hasDebt();
        $this->assertFalse($hasDebt,$debt,"Новый пользователь не должен иметь долга");
        $this->assertEquals(0,$debt,"Новый пользователь не должен иметь долга");
    }



    /**
     * Тестирует, что первый платеж будет равняться полной сумме числу в начале месяца
     */
    public function testFirstPayment()
    {
        $user = $this->generateUser("12345");
        $user->creationDate = "2019-07-01 22:40:51";
        $this->assertSave($user);

        $now = "2019-08-01 18:03:03";
        EnvHelper::setNow(DateTimeHelper::mysqlDateToTimestamp($now));

        $user->refresh();
        $debt = $user->getDebt();
        $sum = 10; //5*2
        $this->assertEquals($sum,$debt,"Долг пользователя должен быть $sum");
    }

    /**
     * Тестирует, что первый платеж будет равняться только части общей платы за месяц в середине месяца
     */
    public function testFirstPaymentInTheMiddleOfTheMonth()
    {
        $user = $this->generateUser("12345");
        $user->creationDate = "2019-08-01 22:40:51";
        $this->assertSave($user);

        $now = "2019-09-16 18:03:03";
        EnvHelper::setNow(DateTimeHelper::mysqlDateToTimestamp($now));

        $user->refresh();
        $debt = $user->getDebt();
        //В сентябре 30 дней, после 16 числа остатся 15, потому что 16-е включительно
        $sum = 5; // 5*2 /30 * 15  = 15
        $this->assertEquals($sum,$debt,"Долг пользователя должен быть $sum");
    }

    /**
     *  Проверяем, что первый платеж на триалке не уменьшается на размер тарифа триалки
     */
    public function testChangingPlanOnTrial()
    {
        $startMonth = DateTimeHelper::mysqlDateToTimestamp("2019-07-01 12:00:00");
        EnvHelper::setNow($startMonth);
        $user = $this->generateUser();
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime($startMonth);
        $this->assertSave($user);

        $plan = $user->createPlan(50);
        $this->assertSave($plan);
        $user->refresh();

        //Подготавливаем платеж
        $payment = $user->createPlanPayment($plan);
        $shouldBe = 100; //50*2
        $this->assertEquals($shouldBe,$payment->sum,"Неправильная сумма платежа");
    }

    /**
     * Тестирование смена плана на триалке в середине месяца
     */
    public function testChangingPlanOnTrialInTheMiddleOfTheMonth()
    {
        $startMonth = DateTimeHelper::mysqlDateToTimestamp("2019-09-13 12:00:00");
        EnvHelper::setNow($startMonth);
        $user = $this->generateUser();
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime($startMonth);
        $this->assertSave($user);

        $plan = $user->createPlan(50);
        $this->assertSave($plan);
        $user->refresh();

        $middle = DateTimeHelper::mysqlDateToTimestamp("2019-09-16 12:00:00");
        EnvHelper::setNow($middle);

        //Подготавливаем платеж
        $this->assertTrue($user->isInTrial(),"Пользователь не на триалке");
        $payment = $user->createPlanPayment($plan);
        $shouldBe = 50; //50*2/2
        $this->assertEquals($shouldBe,$payment->sum,"Неправильная сумма платежа");
    }



}