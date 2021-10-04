<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\Order;
use DateTime;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @test
     */
    public function instantiate(): void
    {
        $now = new DateTime();
        $order = new Order('#1', 10.35, $now);

        $this->assertSame('#1', $order->getId());
        $this->assertSame(10.35, $order->getAmount());
        $this->assertEquals($now, $order->getDateTime());

        // changing used date-time reference shouldn't change the order date-time
        $origDateTime = clone $now;
        $now->modify('tomorrow');
        $this->assertEquals($origDateTime, $order->getDateTime());
    }
}
