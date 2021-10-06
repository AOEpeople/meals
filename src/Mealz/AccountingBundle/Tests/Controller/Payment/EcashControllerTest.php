<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller\Payment;

use App\Mealz\AccountingBundle\Service\TransactionService;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use App\Mealz\AccountingBundle\Controller\Payment\EcashController;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcashControllerTest extends AbstractControllerTestCase
{
    use ProphecyTrait;

    /**
     * Check if form and PayPal button is rendered correctly
     */
    public function testFormRendering(): void
    {
        $this->loginAs(self::USER_STANDARD);

        // Open home page
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        // Click on the balance link
        $balanceLink = $crawler->filterXPath('//div[@class="balance-text"]/a')->link();
        $crawler = $this->client->click($balanceLink);

        // Client should be on transaction history page
        $this->assertGreaterThan(
            0,
            $crawler->filterXPath('//div[contains(@class,"transaction-history")]')->count(),
            'Transaction history page not found'
        );

        // Check if "add funds" button exists
        $this->assertGreaterThan(0, $crawler->filterXPath('//*[@id="ecash"]')->count(), 'Add funds button not found');
    }

    /**
     * @test
     *
     * @testdox Calling postPayment action with anything but POST method returns 405 HTTP response.
     */
    public function postPaymentFailureInvalidMethod(): void
    {
        $invalidRequestMethod = ['DELETE', 'GET', 'HEAD', 'PUT'];
        $transactionService = $this->prophesize(TransactionService::class)->reveal();
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        foreach ($invalidRequestMethod as $method) {
            $request = Request::create('', $method);
            $controller = new EcashController(new NullLogger());

            $response = $controller->postPayment($request, $transactionService, $translator);
            $this->assertSame(405, $response->getStatusCode());
        }
    }

    /**
     * @test
     *
     * @testdox Calling postPayment action with $_dataName results in 400 HTTP response.
     *
     * @dataProvider postPaymentInvalidData
     */
    public function postPaymentFailureBadData($data): void
    {
        $transactionService = $this->prophesize(TransactionService::class)->reveal();
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $request = Request::create('', 'POST', [], [], [], [], $data);
        $controller = new EcashController(new NullLogger());

        $response = $controller->postPayment($request, $transactionService, $translator);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function postPaymentInvalidData(): array
    {
        return [
            'no json payload' => [''],
            'no order-id' => [$this->postPaymentRequestPayLoad('', '', '')],
            'no profile-id' => [$this->postPaymentRequestPayLoad('123', '', '')],
            'no amount' => [$this->postPaymentRequestPayLoad('123', 'jon', '')],
            'invalid amount' => [$this->postPaymentRequestPayLoad('123', 'jon', '0.0')],
        ];
    }

    /**
     * @test
     *
     * @testdox Failure to create a transaction in postPayment action results in 500 HTTP response.
     */
    public function postPaymentFailureTransactionCreateError(): void
    {
        $orderID = '1234';
        $orderAmount = 12.75;
        $profileID = 'alice.meals';

        $transactionServiceProphet = $this->prophesize(TransactionService::class);
        $transactionServiceProphet->create($orderID, $profileID, $orderAmount)->willThrow(RuntimeException::class);
        $transactionServiceMock = $transactionServiceProphet->reveal();
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $jsonPayLoad = $this->postPaymentRequestPayLoad($orderID, $profileID, (string) $orderAmount);
        $request = Request::create('', 'POST', [], [], [], [], $jsonPayLoad);
        $controller = new EcashController(new NullLogger());

        $response = $controller->postPayment($request, $transactionServiceMock, $translator);
        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @testdox Successful execution of postPayment action returns 200 HTTP response with transaction history page URL as content.
     */
    public function postPaymentSuccess(): void
    {
        $orderID = '1234';
        $orderAmount = 12.75;
        $profileID = 'alice.meals';

        $transactionServiceProphet = $this->prophesize(TransactionService::class);
        $transactionServiceProphet->create($orderID, $profileID, $orderAmount)->shouldBeCalledOnce();
        $transactionServiceMock = $transactionServiceProphet->reveal();
        $translator = $this->prophesize(TranslatorInterface::class)->reveal();

        $jsonPayLoad = $this->postPaymentRequestPayLoad($orderID, $profileID, (string) $orderAmount);
        $request = Request::create('', 'POST', [], [], [], [], $jsonPayLoad);
        $controller = self::$container->get(EcashController::class);

        $response = $controller->postPayment($request, $transactionServiceMock, $translator);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('/accounting/transactions', $response->getContent());
    }

    /**
     * Generates JSON payload for postPayment action.
     */
    private function postPaymentRequestPayLoad(string $orderID, string $profileID, string $orderAmount): string
    {
        return sprintf(
            '['
            . '{"name": "ecash[orderid]", "value": "%s"}, '
            . '{"name": "ecash[profile]", "value": "%s"}, '
            . '{"name": "ecash[amount]",  "value": "%s"}'
            .']',
            $orderID, $profileID, $orderAmount
        );
    }
}
