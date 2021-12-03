<?php

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Class CostSheetControllerTest.
 */
class CostSheetControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadTransactions(),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Check if hashCode was written in database.
     */
    public function testHashWrittenInDatabase(): void
    {
        $profile = $this->getUserProfile(self::USER_STANDARD);

        $this->assertNull($profile->getSettlementHash(), 'SettlementHash was set already');

        $this->client->request('GET', '/print/costsheet/settlement/request/alice.meals');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertNotNull($profile->getSettlementHash(), 'SettlementHash was not set');
    }

    /**
     * Check if hashCode was removed from database.
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
        $transaction->setDate(new \DateTime());

        $entityManager->persist($transaction);
        $entityManager->flush();

        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
        $balanceBefore = $transactionRepo->getTotalAmount(self::USER_STANDARD);

        // Pre-action tests
        $this->assertGreaterThan(0, $balanceBefore);
        $this->assertNotNull($profile->getSettlementHash());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/settlement/confirm/'.$hash);
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Check new balance
        $balanceAfter = $transactionRepo->getTotalAmount(self::USER_STANDARD);
        $this->assertEquals(0, $balanceAfter);
        $this->assertNull($profile->getSettlementHash());
    }

    public function testHideUserRequestWithNonHiddenUser(): void
    {
        // Pre-action tests
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/hideuser/request/'.$profile->getUsername());
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Check after action
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());
    }

    public function testHideUserRequestWithHiddenUser(): void
    {
        // Pre-action tests
        $profile = $this->getUserProfile(parent::USER_STANDARD);

        $profile->setHidden(true);
        $this->persistAndFlushAll([$profile]);

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/hideuser/request/'.$profile->getUsername());
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Check after action
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());
    }
}
