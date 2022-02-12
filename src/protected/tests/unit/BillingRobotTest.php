<?php


class BillingRobotTest extends AmazonWebUnitTestCase
{

    /**
     * Триальным пользователям платежей не делаем
     */
    public function testNoPaymentsForNewUser()
    {
        $user = $this->generateUser("12345");
        $this->assertSave($user);
        $billing = $this->getBilling();
        $billing->start([]);
        $user->refresh();

        $payments = $user->getPayments(true);
        $this->assertCount(0,$payments,"Платежей не должно быть");

        //Еще один прогон
        EnvHelper::setNow(EnvHelper::now()+ 60*60*24*13);
        $billing->start([]);
        $user->refresh();
        $payments = $user->getPayments(true);
        $this->assertCount(0,$payments,"Платежей не должно быть");

    }

    /**
     * Триальный платеж
     */
    public function testTrialPayment()
    {
        $start = DateTimeHelper::mysqlDateToTimestamp("2019-09-01 12:00:00");

        EnvHelper::setNow($start);
        $user = $this->generateUser("12345");
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime($start);
        $this->assertSave($user);
        $billing = $this->getBilling();

        $this->log("User id ".$user->getPk());
        AmazonHelper::getTrialDaysNumber();
        $daysToPass = 60*60*24*14; //14 дней триала
        EnvHelper::setNow(EnvHelper::now() + $daysToPass);

        $billing->start([]);
        $user->refresh();
        $payments = $user->getPayments(true);
        $this->assertCount(1,$payments,"Платеж должен быть 1");

        $shouldBe = 5.33; //(5*2)/30*16 Остается 16 дней
        $this->assertEquals($shouldBe,$payments[0]->sum,"Сумма должна быть $shouldBe");
        $this->assertEquals(Payment::SERVICE_MONTHLY_PLAN_START,$payments[0]->service,"Неправильный тип платжеа");

        //Еще прогон, чтобы убедиться, что мы не дублируем платежи
        EnvHelper::setNow(EnvHelper::now()+ 60*60*24*1);
        $billing->start([]);
        $user->refresh();
        $payments = $user->getPayments(true);
        $this->assertCount(1,$payments,"Платеж должен быть 1");
    }

    /**
     * Обычный платеж
     */
    public function testMonthlyPayment()
    {
        $start = DateTimeHelper::mysqlDateToTimestamp("2019-08-01 12:00:00");
        EnvHelper::setNow($start);
        $user = $this->generateUser("12345");
        $user->creationDate = DateTimeHelper::timestampToMysqlDateTime($start);
        $this->assertSave($user);
        $billing = $this->getBilling();


        $nextMonth = DateTimeHelper::mysqlDateToTimestamp("2019-09-01 12:00:00");
        EnvHelper::setNow($nextMonth);

        $billing->start([]);
        $user->refresh();
        $payments = $user->getPayments(true);
        $this->assertCount(1,$payments,"Платеж должен быть 1");
        $shouldBe = 10; //(25*2)/30*16 Остается 16 дней
        $this->assertEquals($shouldBe,$payments[0]->sum,"Сумма должна быть $shouldBe");
        $this->assertEquals(Payment::SERVICE_MONTHLY_PLAN,$payments[0]->service,"Неправильный тип платжеа");

        //Сохраняем платеж как успешный
        $payments[0]->status = Payment::STATUS_SUCCESS;
        $payments[0]->save();

        //Еще прогон, чтобы убедиться, что мы не дублируем платежи
        EnvHelper::setNow(EnvHelper::now()+ 60*60*24*1);
        $billing->start([]);
        $user->refresh();
        $payments = $user->getPayments(true);
        $this->assertCount(1,$payments,"Платеж должен быть 1");

    }



    /**
     * @return BillingRobotCommand
     */
    private function getBilling()
    {
        Yii::import("application.commands.robots.BillingRobotCommand");
        $robot = new BillingRobotCommand("",null);
        return $robot;
    }

}