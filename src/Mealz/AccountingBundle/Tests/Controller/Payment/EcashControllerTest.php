<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller\Payment;

use App\Mealz\AccountingBundle\Controller\Payment\EcashController;
use App\Mealz\AccountingBundle\Service\TransactionService;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Exception;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcashControllerTest extends AbstractControllerTestCase
{
    use ProphecyTrait;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);
    }

    /**
     * Check if form and PayPal button is rendered correctly.
     */
    public function testFormRendering(): void
    {
        $this->markTestSkipped('Frontend Test');
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
     * @testWith ["DELETE"]
     *           ["GET"]
     *           ["HEAD"]
     *           ["PUT"]
     *
     * @testdox Calling postPayment action with $httpMethod returns 404 HTTP response.
     */
    public function testPostPaymentFailureInvalidMethod(string $httpMethod): void
    {
        $this->markTestSkipped('Frontend Test');
        $this->client->request('/payment/ecash/form/submit', $httpMethod);
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @dataProvider createTxExceptionProvider
     *
     * @testdox Failure to create a transaction due to $_dataName results in $expRespStatusCode HTTP response.
     */
    public function testPostPaymentFailureTransactionCreateError(string $exception, int $expRespStatusCode): void
    {
        $this->markTestSkipped('frontend Test');

        $txServiceProphet = $this->prophesize(TransactionService::class);
        $txServiceProphet->createFromRequest(Argument::type(Request::class))->willThrow($exception);
        $txServiceMock = $txServiceProphet->reveal();
        $translatorMock = $this->prophesize(TranslatorInterface::class)->reveal();

        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST);
        $controller = self::$container->get(EcashController::class);

        $response = $controller->postPayment($request, $txServiceMock, $translatorMock);
        $this->assertSame($expRespStatusCode, $response->getStatusCode());
    }

    public function createTxExceptionProvider(): array
    {
        return [
            'unauthorized access' => [AccessDeniedHttpException::class, 403],
            'invalid data' => [BadRequestHttpException::class, 400],
            'unprocessable data' => [UnprocessableEntityHttpException::class, 422],
            'any unexpected exception' => [Exception::class, 500],
        ];
    }

    /**
     * @testdox Successful execution of postPayment action returns 200 HTTP response with transaction history page URL as content.
     */
    public function testPostPaymentSuccess(): void
    {
        $this->markTestSkipped('Frontend Test');
        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST);
        $txServiceProphet = $this->prophesize(TransactionService::class);
        $txServiceProphet->createFromRequest(Argument::type(Request::class))->shouldBeCalledOnce();
        $txServiceMock = $txServiceProphet->reveal();
        $translatorMock = $this->prophesize(TranslatorInterface::class)->reveal();

        $controller = self::$container->get(EcashController::class);

        $response = $controller->postPayment($request, $txServiceMock, $translatorMock);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('/accounting/transactions', $response->getContent());
    }
}
