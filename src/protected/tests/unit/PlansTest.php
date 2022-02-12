<?php


class PlansTest extends AmazonWebUnitTestCase
{


    public function testUnsubscription()
    {

        $user = $this->generateUser("12345",true);
        $this->assertSave($user);
        $user->creationDate = "2019-08-10"; //нет триалки

        $now = DateTimeHelper::mysqlDateToTimestamp("2019-09-01");
        EnvHelper::setNow($now);

        $hasDebt = $user->hasDebt();
        $this->assertTrue($hasDebt,"У пользователя должен быть долг");

        $plan = $user->createPlan("15");
        $this->assertSave($plan);
        $payment = $user->createPlanPayment($plan);
        $payment->status = Payment::STATUS_SUCCESS;
        $this->assertSave($payment);

        $hasDebt = $user->hasDebt();
        $this->assertFalse($hasDebt,"У пользователя не должен быть долг");


        //ОТписываемся
        $user->paymentMethodVerified = User::PAYMENT_METHOD_UNSUBSCRIBED;
        $this->assertSave($user);
        $hasDebt = $user->hasDebt();
        $this->assertFalse($hasDebt,"У пользователя не должен быть долг");

        $aBitLater = DateTimeHelper::mysqlDateToTimestamp("2019-09-15");
        EnvHelper::setNow($aBitLater);
        $hasDebt = $user->hasDebt();
        $this->assertFalse($hasDebt,"У пользователя не должен быть долг");
        $enabledIds = $user->findEnabledIds();
        $enabled = in_array($user->getPk(), $enabledIds);
        $this->assertTrue($enabled,"Отписавшиеся пользовтели все еще обрабатывается до конца месяца");


        $nextMonth = DateTimeHelper::mysqlDateToTimestamp("2019-10-01",);
        EnvHelper::setNow($nextMonth);
        $hasDebt = $user->hasDebt();
        $this->assertTrue($hasDebt,"У пользователя должен быть долг");
        $enabledIds = $user->findEnabledIds();
        $enabled = in_array($user->getPk(), $enabledIds);
        $this->assertFalse($enabled,"Отписавшиеся пользовтели не обрабатываются в долге");
    }

}