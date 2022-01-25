<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;

/**
 * Class CostSheetControllerTest.
 */
class CostSheetControllerTest extends AbstractControllerTestCase
{
    private Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadTransactions(),
        ]);

        $participantRepo = $this->getDoctrine()->getRepository(Participant::class);
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
        $this->wallet = new Wallet($participantRepo, $transactionRepo);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * @testdox Check that settlement request is successful for a user with positive balance and
     * the settlement hash is written in database accordingly.
     */
    public function testHashWrittenInDatabaseWithBalance(): void
    {
        $profile = $this->getUserProfile(self::USER_STANDARD);
        $this->assertNull($profile->getSettlementHash(), 'SettlementHash was set already');

        $balance = $this->wallet->getBalance($profile);
        $this->assertGreaterThan(0.00, $balance);

        $this->client->request('GET', '/print/costsheet/settlement/request/' . $profile->getUsername());
        $this->assertResponseIsSuccessful();

        $this->assertNotNull($profile->getSettlementHash(), 'SettlementHash was not set');
    }

    /**
     * @testdox Check that settlement request is successful for a user with zero balance but
     * the settlement hash hasn't changed because the the account doesn't need to be settled.
     */
    public function testHashNotWrittenInDatabaseWithZeroBalance(): void
    {
        $profile = $this->getUserProfile('john.meals');
        $this->assertNull($profile->getSettlementHash(), 'SettlementHash was set already');

        $balance = $this->wallet->getBalance($profile);
        $this->assertEquals(0.00, $balance);

        $this->client->request('GET', '/print/costsheet/settlement/request/' . $profile->getUsername());
        $this->assertResponseIsSuccessful();

        $this->assertNull($profile->getSettlementHash(), 'Settlement was set');
    }

    /**
     * @testdox Check if hashCode was removed from database after settlement confirmation.
     */
    public function testHashRemoveFromDatabase(): void
    {
        $profile = $this->getUserProfile(self::USER_STANDARD);
        $hash = '12345';
        $profile->setSettlementHash($hash);

        $entityManager = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setAmount(random_int(10, 120) + 0.13);
        $transaction->setDate(new DateTime());

        $entityManager->persist($transaction);
        $entityManager->flush();

        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
        $balanceBefore = $transactionRepo->getTotalAmount($profile->getUsername());

        // Pre-action tests
        $this->assertGreaterThan(0, $balanceBefore);
        $this->assertNotNull($profile->getSettlementHash());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/settlement/confirm/' . $hash);
        $this->assertResponseIsSuccessful();

        // Check new balance
        $balanceAfter = $transactionRepo->getTotalAmount($profile->getUsername());
        $this->assertEquals(0, $balanceAfter);
        $this->assertNull($profile->getSettlementHash());
    }

    /**
     * @testdox Check if non-hidden user is hidden after hideuser request is successful.
     */
    public function testHideUserRequestWithNonHiddenUser(): void
    {
        // Pre-action tests
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/hideuser/request/' . $profile->getUsername());
        $this->assertResponseIsSuccessful();

        // Check after action
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());
    }

    /**
     * @testdox Check that hidden user is still hidden after hideuser request is successful.
     */
    public function testHideUserRequestWithHiddenUser(): void
    {
        // Pre-action tests
        $profile = $this->getUserProfile(parent::USER_STANDARD);

        $profile->setHidden(true);
        $this->persistAndFlushAll([$profile]);

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/hideuser/request/' . $profile->getUsername());
        $this->assertResponseIsSuccessful();

        // Check after action
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());
    }
}
