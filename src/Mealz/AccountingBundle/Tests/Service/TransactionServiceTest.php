<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Service\PayPal\Order;
use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use App\Mealz\AccountingBundle\Service\TransactionService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TransactionServiceTest extends AbstractDatabaseTestCase
{
    use ProphecyTrait;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);
    }

    /**
     * @testdox createFromRequest() on success creates a new record in transaction table.
     */
    public function testCreateFromRequestSuccess(): void
    {
        // test data
        $orderID = '2DD03984N872668774X';
        $orderAmount = 25.00;
        $orderDateTime = new DateTime('now', new DateTimeZone('UTC'));

        // service dependencies
        $order = new Order($orderID, $orderAmount, $orderDateTime, 'COMPLETED');
        $paypalServiceMock = $this->getPayPayServiceMock($orderID, $order);
        $securityServiceMock = $this->getSecurityMock('alice.meals');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        // instantiate service
        $transactionService = new TransactionService($paypalServiceMock, $entityManager, $securityServiceMock);

        // call test method
        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST, [], [], [], [], $this->createTxReqPayLoad($orderID));
        $transactionService->createFromRequest($request);

        // verify transaction
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
        $transaction = $transactionRepo->findOneBy(['orderId' => $orderID]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame('alice.meals', $transaction->getProfile()->getUsername());
        $this->assertSame($orderAmount, $transaction->getAmount());
        $this->assertEquals($orderDateTime, $transaction->getDate());
        $this->assertEquals('0', $transaction->getPaymethod());
    }

    /**
     * @testdox Calling createFromRequest() without login session throws AccessDeniedHttpException.
     */
    public function testCreateFromRequestFailureNoLoginSession(): void
    {
        // prepare service dependencies
        $paypalServiceMock = $this->prophesize(PayPalService::class)->reveal();
        $entityManagerMock = $this->prophesize(EntityManagerInterface::class)->reveal();
        $securityServiceMock = $this->getSecurityMock(null);

        // instantiate service
        $transactionService = new TransactionService($paypalServiceMock, $entityManagerMock, $securityServiceMock);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('login required');

        // call test method
        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST);
        $transactionService->createFromRequest($request);
    }

    /**
     * @testdox Calling createFromRequest() with $request argument containing $_dataName throws BadRequestHttpException.
     *
     * @dataProvider createFromRequestInvalidData
     */
    public function testCreateFromRequestFailureBadData($data): void
    {
        // prepare service dependencies
        $paypalServiceMock = $this->prophesize(PayPalService::class)->reveal();
        $entityManagerMock = $this->prophesize(EntityManagerInterface::class)->reveal();
        $securityServiceMock = $this->getSecurityMock('alice.meals');

        // instantiate service
        $transactionService = new TransactionService($paypalServiceMock, $entityManagerMock, $securityServiceMock);

        $this->expectException(BadRequestHttpException::class);

        // call test method
        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST, [], [], [], [], $data);
        $transactionService->createFromRequest($request);
    }

    public function createFromRequestInvalidData(): array
    {
        return [
            'no json payload' => [''],
            'wrong data' => ['["lorem", "ipsum"]'],
            'no order-id' => [$this->createTxReqPayLoad('')],
        ];
    }

    /**
     * @testdox Calling createFromRequest() with $request argument containing invalid order-id throws UnprocessableEntityHttpException.
     */
    public function testCreateFromRequestFailureOrderNotFound(): void
    {
        // test data
        $orderID = '2DD03984N872668774X';

        // prepare service dependencies
        $paypalServiceMock = $this->getPayPayServiceMock($orderID, null);
        $entityManagerMock = $this->prophesize(EntityManagerInterface::class)->reveal();
        $securityServiceMock = $this->getSecurityMock('alice.meals');

        // instantiate service
        $transactionService = new TransactionService($paypalServiceMock, $entityManagerMock, $securityServiceMock);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionCode(1633509057);

        // call test method
        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST, [], [], [], [], $this->createTxReqPayLoad($orderID));
        $transactionService->createFromRequest($request);
    }

    /**
     * @testdox Calling createFromRequest() with $request argument referring incomplete order throws UnprocessableEntityHttpException.
     */
    public function testCreateFromRequestFailureIncompleteOrder(): void
    {
        // test data
        $orderID = '2DD03984N872668774X';
        $orderAmount = 25.00;
        $orderDateTime = new DateTime('now', new DateTimeZone('UTC'));

        // prepare service dependencies
        $order = new Order($orderID, $orderAmount, $orderDateTime, 'VOIDED');
        $paypalServiceMock = $this->getPayPayServiceMock($orderID, $order);
        $entityManagerMock = $this->prophesize(EntityManagerInterface::class)->reveal();
        $securityServiceMock = $this->getSecurityMock('alice.meals');

        // instantiate service
        $transactionService = new TransactionService($paypalServiceMock, $entityManagerMock, $securityServiceMock);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionCode(1633513408);

        // call test method
        $request = Request::create('', \Symfony\Component\HttpFoundation\Request::METHOD_POST, [], [], [], [], $this->createTxReqPayLoad($orderID));
        $transactionService->createFromRequest($request);
    }

    private function getProfile(string $username): Profile
    {
        /** @var ProfileRepositoryInterface $profileRepo */
        $profileRepo = self::$container->get(ProfileRepositoryInterface::class);
        $profile = $profileRepo->find($username);

        if (null === $profile) {
            throw new RuntimeException($username . ': profile not found');
        }

        return $profile;
    }

    /**
     * Returns mocked PayPal service that returns $order for $orderID.
     *
     * @param Order|null $order order to be returned by mocked service
     */
    private function getPayPayServiceMock(string $orderID, ?Order $order): PayPalService
    {
        $paypalServiceProphet = $this->prophesize(PayPalService::class);
        $paypalServiceProphet->getOrder($orderID)->shouldBeCalledOnce()->willReturn($order);

        return $paypalServiceProphet->reveal();
    }

    /**
     * Returns mocked Security (symfony) service that returns the user identified by $username.
     *
     * @param string|null $username Username of logged-in user. NULL for no logged-in user.
     */
    private function getSecurityMock(?string $username): Security
    {
        $profile = $username ? $this->getProfile($username) : null;
        $secServiceProphet = $this->prophesize(Security::class);
        $secServiceProphet->getUser()->willReturn($profile);

        return $secServiceProphet->reveal();
    }

    private function createTxReqPayLoad(string $orderID): string
    {
        return sprintf('[{"name": "ecash[orderid]", "value": "%s"}]', $orderID);
    }
}
