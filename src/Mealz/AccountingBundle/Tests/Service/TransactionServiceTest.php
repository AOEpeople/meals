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
use App\Mealz\UserBundle\Entity\ProfileRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

class TransactionServiceTest extends AbstractDatabaseTestCase
{
    use ProphecyTrait;

    /**
     * @inheritDoc
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
     * @test
     */
    public function processSuccess(): void
    {
        // test data
        $profileID = 'alice.meals';
        $orderID = '12345';
        $orderAmount = 25.00;
        $orderDateTime = new DateTime('now', new DateTimeZone('UTC'));

        // prepare service dependencies
        $order = new Order($orderID, $orderAmount, $orderDateTime);
        $paypalServiceProphet = $this->prophesize(PayPalService::class);
        $paypalServiceProphet->getOrder($orderID)->shouldBeCalledOnce()->willReturn($order);
        $paypalServiceMock = $paypalServiceProphet->reveal();

        /** @var ProfileRepository $profileRepository */
        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        // instantiate service and call test method
        $transactionService = new TransactionService($paypalServiceMock, $profileRepository, $entityManager);
        $transactionService->create($orderID, $profileID, $orderAmount);

        // verify transaction
        $transactionRepository = $this->getDoctrine()->getRepository(Transaction::class);
        $transaction = $transactionRepository->findOneBy(['orderId' => $orderID]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame($profileID, $transaction->getProfile()->getUsername());
        $this->assertSame($orderAmount, $transaction->getAmount());
        $this->assertEquals($orderDateTime, $transaction->getDate());
        $this->assertEquals('0', $transaction->getPaymethod());
    }

    /**
     * @test
     */
    public function processFailureProfileNotFound(): void
    {
        // test data
        $profileID = 'unhappy.meal';

        // prepare service dependencies
        $paypalServiceMock = $this->prophesize(PayPalService::class)->reveal();
        /** @var ProfileRepository $profileRepositoryMock */
        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);
        /** @var EntityManager $entityManagerMock */
        $entityManagerMock = $this->prophesize(EntityManagerInterface::class)->reveal();

        // instantiate service and call test method
        $transactionService = new TransactionService($paypalServiceMock, $profileRepository, $entityManagerMock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1633093870);
        $this->expectExceptionMessage('profile not found, profile-id: '.$profileID);

        $transactionService->create('12345', $profileID, 10.00);
    }

    /**
     * @test
     */
    public function processFailureOrderNotFound(): void
    {
        // test data
        $profileID = 'alice.meals';
        $orderID = '12345';
        $orderAmount = 25.00;

        // prepare service dependencies
        $paypalServiceProphet = $this->prophesize(PayPalService::class);
        $paypalServiceProphet->getOrder($orderID)->shouldBeCalledOnce()->willThrow(RuntimeException::class);
        $paypalServiceMock = $paypalServiceProphet->reveal();

        /** @var ProfileRepository $profileRepository */
        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);
        /** @var EntityManager $entityManager */
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();

        // instantiate service and call test method
        $transactionService = new TransactionService($paypalServiceMock, $profileRepository, $entityManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1633425633);
        $this->expectExceptionMessage('order not found, order-id: '.$orderID);

        $transactionService->create($orderID, $profileID, $orderAmount);
    }

    /**
     * @test
     */
    public function processFailureInvalidOrderAmount(): void
    {
        // test data
        $profileID = 'alice.meals';
        $orderID = '12345';
        $orderAmount = 15.00;
        $orderDateTime = new DateTime('now', new DateTimeZone('UTC'));

        // prepare service dependencies
        $order = new Order($orderID, $orderAmount, $orderDateTime);
        $paypalServiceProphet = $this->prophesize(PayPalService::class);
        $paypalServiceProphet->getOrder($orderID)->shouldBeCalledOnce()->willReturn($order);
        $paypalServiceMock = $paypalServiceProphet->reveal();

        /** @var ProfileRepository $profileRepository */
        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);
        /** @var EntityManager $entityManager */
        $entityManager = $this->prophesize(EntityManagerInterface::class)->reveal();

        // instantiate service and call test method
        $transactionService = new TransactionService($paypalServiceMock, $profileRepository, $entityManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1633094204);
        $this->expectExceptionMessage('order amount mismatch');

        $transactionService->create($orderID, $profileID, $orderAmount + 1);
    }
}
