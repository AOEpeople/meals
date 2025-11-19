<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;
use Override;

/**
 * Class CostSheetControllerTest.
 */
final class CostSheetControllerTest extends AbstractControllerTestCase
{
    private Wallet $wallet;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
            new LoadTransactions(),
        ]);

        $participantRepo = self::getContainer()->get(ParticipantRepositoryInterface::class);
        $transactionRepo = self::getContainer()->get(TransactionRepositoryInterface::class);
        $this->wallet = new Wallet($participantRepo, $transactionRepo);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * @testdox Check that settlement request is successful for a user with positive balance and
     * the settlement hash is written in database accordingly.
     */
    public function testHashWrittenInDatabaseWithBalance(): void
    {
        $profile = $this->getUserProfileByUsername(self::USER_STANDARD);
        $data = json_encode([
            'id' => $profile->getId(),
        ]);
        $this->assertNull($profile->getSettlementHash(), 'SettlementHash was set already');

        $balance = $this->wallet->getBalance($profile);
        $this->assertGreaterThan(0.00, $balance);

        $this->client->request('POST', '/api/costs/settlement', [], [], [], $data);
        self::assertResponseIsSuccessful();

        $this->assertNotNull($profile->getSettlementHash(), 'SettlementHash was not set');
    }

    /**
     * @testdox Check that settlement request returns an error for a user with zero balance and
     * the settlement hash hasn't changed because the account doesn't need to be settled.
     */
    public function testHashNotWrittenInDatabaseWithZeroBalance(): void
    {
        $profile = $this->getUserProfileByUsername('john.meals');
        $data = json_encode([
            'id' => $profile->getId(),
        ]);
        $this->assertNull($profile->getSettlementHash(), 'SettlementHash was set already');

        $balance = $this->wallet->getBalance($profile);
        $this->assertEquals(0.00, $balance);

        $this->client->request('POST', '/api/costs/settlement', [], [], [], $data);
        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());

        $this->assertNull($profile->getSettlementHash(), 'Settlement was set');
    }

    /**
     * @testdox Check if hashCode was removed from database after settlement confirmation.
     */
    public function testHashRemoveFromDatabase(): void
    {
        $profile = $this->getUserProfileByUsername(self::USER_STANDARD);
        $hash = '12345';
        $profile->setSettlementHash($hash);

        $entityManager = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setAmount(random_int(10, 120) + 0.13);
        $transaction->setDate(new DateTime());

        $entityManager->persist($transaction);
        $entityManager->flush();

        $transactionRepo = self::getContainer()->get(TransactionRepositoryInterface::class);
        $balanceBefore = $transactionRepo->getTotalAmount($profile->getId());

        // Pre-action tests
        $this->assertGreaterThan(0, $balanceBefore);
        $this->assertNotNull($profile->getSettlementHash());

        // Trigger action
        $this->client->request('POST', '/api/costs/settlement/confirm/' . $hash);
        $this->assertResponseIsSuccessful();

        // Check new balance
        $balanceAfter = $transactionRepo->getTotalAmount($profile->getId());
        $this->assertEquals(0, $balanceAfter);
        $this->assertNull($profile->getSettlementHash());
    }

    /**
     * @testdox Check if non-hidden user is hidden after hideuser request is successful.
     */
    public function testHideUserRequestWithNonHiddenUser(): void
    {
        // Pre-action tests
        $profile = $this->getUserProfileByUsername(parent::USER_STANDARD);
        $data = json_encode([
            'username' => $profile->getUsername(),
        ]);
        $this->assertFalse($profile->isHidden());

        // Trigger action
        $this->client->request('POST', '/api/costs/hideuser', [], [], [], $data);
        $this->assertResponseIsSuccessful();

        // Check after action
        $profile = $this->getUserProfileByUsername(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());
    }

    /**
     * @testdox Check that hidden user is still hidden after hideuser request is successful.
     */
    public function testHideUserRequestWithHiddenUser(): void
    {
        // Pre-action tests
        $profile = $this->getUserProfileByUsername(parent::USER_STANDARD);
        $data = json_encode([
            'id' => $profile->getId(),
        ]);

        $profile->setHidden(true);
        $this->persistAndFlushAll([$profile]);

        $profile = $this->getUserProfileByUsername(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());

        // Trigger action
        $this->client->request('POST', '/api/costs/hideuser', [], [], [], $data);
        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());

        // Check after action
        $profile = $this->getUserProfileByUsername(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());
    }
}
