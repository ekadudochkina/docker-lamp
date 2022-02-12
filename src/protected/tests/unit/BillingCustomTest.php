<?php

/**
 * Тут содержатся тесты конкретных пользователей по багам
 */
class BillingCustomTest extends AmazonWebUnitTestCase
{


    /**
     * Тест на неправаильное опрделение долга для пользователя
     * @throws Exception
     */
    public function testDebtForInterpauda()
    {
        $user = $this->generateUser("12345");
        $user->creationDate = "2019-04-06 22:40:51";
        $this->assertSave($user);

        $sums = [1,1,1,8,22,12];
        $dates = [
            "2019-05-03",
            "2019-07-08",
            "2019-07-08",
            "2019-07-01",
            "2019-06-01",
            "2019-07-01",

        ];
        $services = [
            Payment::SERVICE_TEST,
            Payment::SERVICE_TEST,
            Payment::SERVICE_TEST,
            Payment::SERVICE_DEBT,
            Payment::SERVICE_MONTHLY_PLAN,
            Payment::SERVICE_CHANGE_PLAN,
        ];
        $pdate = "2019-07-13 16:22:09";
        $payments = [];
        foreach($sums as $key => $sum)
        {
            $payment = new Payment();
            $payment->userId = $user->getPk();
            $payment->sum = $sum;
            $payment->creationDate = $pdate;
            $payment->type = Payment::TYPE_PERIODIC;
            $payment->service = $services[$key];
            $payment->chargeDate = $pdate;
            $payment->status = Payment::STATUS_SUCCESS;
            $payment->date = $dates[$key];
            $this->assertTrue($payment->save(),"Не сохраняется платеж:".print_r($payment->getErrors(),1));
            $payments[] = $payment;
        }
        $plan = new Plan();
        $plan->products = 11;
        $plan->userId = $user->getPk();
        $plan->paymentId = ArrayHelper::getLast($payments)->getPk();
        $this->assertTrue($plan->save(false),"Не сохраняется платеж:".print_r($plan->getErrors(),1));

        $now = "2019-07-25 18:03:03";
        EnvHelper::setNow(DateTimeHelper::mysqlDateToTimestamp($now));

        $user->refresh();
        $debt = $user->getDebt();
        $this->assertLessThanOrEqual(0,$debt,"Долг пользователя должен быть 0");

        $this->login("12345",$user);

        $hasDebt = $user->hasDebt();
        $this->assertFalse($hasDebt,"Пользователь не должен иметь долг");
        $result = $this->forward("profile/index");
        $error = $this->getErrorNotification($result);
        $this->assertNull($error,"Не должно быть ошибок в уведомлениях пользователя");
        $this->assertNotContains("Account blocked",$result,"На странице не должно выводиться долговых увдомлений");
    }
}