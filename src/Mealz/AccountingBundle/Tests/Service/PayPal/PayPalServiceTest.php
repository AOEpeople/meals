<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use PaypalServerSdkLib\Http\ApiResponse;
use PaypalServerSdkLib\Http\HttpRequest;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitBuilder;
use PaypalServerSdkLib\Models\Order;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

class PayPalServiceTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @testdox PayPalService::evaluateResponse() returns NULL if no order with given order-id exists.
     */
    public function testGetOrderFailureOrderNotFound(): void
    {
        $response = new ApiResponse(null, 404, null, null, null, null);
        $paypalService = new PayPalService('clientId', 'clientSecret', 'dev');

        $order = $paypalService->evaluateResponse($response);

        $this->assertNull($order);
    }

    /**
     * @testdox PayPalService::evaluateResponse() throws RuntimeException if PayPal api replies with status code other than 200 or 404.
     */
    public function testGetOrderFailureAPIResponseNotOkay(): void
    {
        $httpStatusCodes = array_merge(
            range(100, 103), range(201, 208), [226], range(300, 308),
            range(400, 403), range(405, 451), range(500, 511),
        );
        $mockRequest = $this->prophesize(HttpRequest::class);

        foreach ($httpStatusCodes as $httpStatusCode) {
            $response = new ApiResponse($mockRequest->reveal(), $httpStatusCode, null, null, null, null);
            $mockRequest->getQueryUrl()->willReturn('http://test.path');
            $mockRequest->getHttpMethod()->willReturn('GET');
            $paypalService = new PayPalService('clientId', 'clientSecret', 'dev');

            try {
                $paypalService->evaluateResponse($response);
                $this->fail('expected RuntimeException'); // should never reach here
            } catch (RuntimeException $rte) {
                $this->assertSame(1633425374, $rte->getCode());
                $this->assertEquals('unexpected api response, status: ' . $httpStatusCode . ', path: http://test.path, method: GET', $rte->getMessage());
            }
        }
    }

    /**
     * @testdox PayPalService::evaluateResponse() returns Order object on success.
     */
    public function testGetOrderSuccess(): void
    {
        $orderID = '123';
        $orderAmount = '10.35';
        $orderDateTime = gmdate('Y-m-d\TH:i:s\Z');

        $purchaseUnit = PurchaseUnitBuilder::init()
            ->amount(AmountWithBreakdownBuilder::init('EUR', $orderAmount)->build())
            ->build();

        $responseBody = new Order();
        $responseBody->setId($orderID);
        $responseBody->setStatus('COMPLETED');
        $responseBody->setUpdateTime($orderDateTime);
        $responseBody->setPurchaseUnits([$purchaseUnit]);

        $response = new ApiResponse(null, 200, null, null, $responseBody, $responseBody);

        $paypalService = new PayPalService('clientId', 'clientSecret', 'dev');
        $order = $paypalService->evaluateResponse($response);

        $this->assertSame($orderID, $order->getId());
        $this->assertTrue($order->isCompleted());
        $this->assertSame((float) $orderAmount, $order->getAmount());
        $this->assertSame($orderDateTime, $order->getDateTime()->format('Y-m-d\TH:i:s\Z'));
    }
}
