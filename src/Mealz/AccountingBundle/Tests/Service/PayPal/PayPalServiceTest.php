<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\Client as PayPalClient;
use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PayPalServiceTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function getOrder(): void
    {
        $orderID = '123';
        $orderAmount = 10.35;
        $orderDateTime = gmdate('Y-m-d\TH:i:s\Z');

        $responseBody = (object) [
            'id' => $orderID,
            'update_time' => $orderDateTime,
            'purchase_units' => [
                0 => (object) [
                    'amount' => (object) [
                        'value' => $orderAmount
                    ]
                ]
            ]
        ];

        $response = new HttpResponse(200, $responseBody, []);

        $paypalClientProphet = $this->prophesize(PayPalClient::class);
        $paypalClientProphet->execute(Argument::that(function ($request) use ($orderID): bool {
            return ($request instanceof HttpRequest) && (strpos($request->path,$orderID) !== false);
        }))
        ->willReturn($response);
        $paypalClientMock = $paypalClientProphet->reveal();

        $paypalService = new PayPalService($paypalClientMock);
        $order = $paypalService->getOrder($orderID);

        $this->assertSame($orderID, $order->getId());
        $this->assertSame($orderAmount, $order->getAmount());
        $this->assertSame($orderDateTime, $order->getDateTime()->format('Y-m-d\TH:i:s\Z'));
    }
}
