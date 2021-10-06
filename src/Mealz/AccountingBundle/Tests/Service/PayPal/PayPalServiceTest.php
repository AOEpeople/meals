<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use Exception;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

class PayPalServiceTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     *
     * @testdox PayPalService::getOrder() returns NULL if no order with given order-id exists.
     */
    public function getOrderFailureOrderNotFound(): void
    {
        $response = new HttpResponse(404, [], []);

        $paypalClientProphet = $this->prophesize(PayPalHttpClient::class);
        $paypalClientProphet->execute(Argument::type(OrdersGetRequest::class))->willReturn($response);
        $paypalClientMock = $paypalClientProphet->reveal();

        $paypalService = new PayPalService($paypalClientMock);
        $order = $paypalService->getOrder('12345');

        $this->assertNull($order);
    }

    /**
     * @test
     *
     * @testdox PayPalService::getOrder() throws RuntimeException if PayPal api replies with status code other than 200 or 404.
     */
    public function getOrderFailureAPIResponseNotOkay(): void
    {
        $httpStatusCodes = array_merge(
            range(100, 103), range(201, 208), [226], range(300, 308),
            range(400, 403), range(405, 451), range(500, 511),
        );

        foreach ($httpStatusCodes as $httpStatusCode) {
            $response = new HttpResponse($httpStatusCode, [], []);

            $clientProphet = $this->prophesize(PayPalHttpClient::class);
            $clientProphet->execute(Argument::type(OrdersGetRequest::class))->willReturn($response);
            $paypalClientMock = $clientProphet->reveal();

            $paypalService = new PayPalService($paypalClientMock);

            try {
                $paypalService->getOrder('12345');
                $this->fail('expected RuntimeException'); // should never reach here
            } catch (RuntimeException $rte) {
                $this->assertSame(1633425374, $rte->getCode());
                $this->assertStringContainsString('unexpected api response, status: '.$httpStatusCode, $rte->getMessage());
            }
        }
    }

    /**
     * @test
     *
     * @testdox PayPalService::getOrder() returns Order object on success.
     */
    public function getOrderSuccess(): void
    {
        $orderID = '123';
        $orderAmount = 10.35;
        $orderDateTime = gmdate('Y-m-d\TH:i:s\Z');

        $responseBody = (object) [
            'id' => $orderID,
            'status' => 'COMPLETED',
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

        $paypalClientProphet = $this->prophesize(PayPalHttpClient::class);
        $paypalClientProphet->execute(Argument::type(OrdersGetRequest::class))->willReturn($response);
        $paypalClientMock = $paypalClientProphet->reveal();

        $paypalService = new PayPalService($paypalClientMock);
        $order = $paypalService->getOrder($orderID);

        $this->assertSame($orderID, $order->getId());
        $this->assertTrue($order->isCompleted());
        $this->assertSame($orderAmount, $order->getAmount());
        $this->assertSame($orderDateTime, $order->getDateTime()->format('Y-m-d\TH:i:s\Z'));
    }
}
