<?php

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;

/**
 * Class CostSheetControllerTest
 * @package Mealz\MealBundle\Tests\Controller
 */
class CostSheetControllerTest extends AbstractControllerTestCase
{
    /**
     * Prepares test environment.
     */
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
            new LoadTransactions()
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Check if hashCode was written in database
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
     * Check if hashCode was removed from database
     */
    public function testHashRemoveFromDatabase(): void
    {
        $profile = $this->getUserProfile(self::USER_STANDARD);
        $hash = '12345';
        $profile->setSettlementHash($hash);

        $enityManager = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setAmount(random_int(10, 120) + 0.13);
        $transaction->setDate(new \DateTime());

        $enityManager->persist($transaction);
        $enityManager->flush();

        $transactionRepo = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $balanceBefore = $transactionRepo->getTotalAmount(self::USER_STANDARD);

        // Pre-action tests
        $this->assertGreaterThan(0, $balanceBefore);
        $this->assertNotNull($profile->getSettlementHash());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/settlement/confirm/' . $hash);
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Check new balance
        $balanceAfter = $transactionRepo->getTotalAmount(self::USER_STANDARD);
        $this->assertEquals(0, $balanceAfter);
        $this->assertNull($profile->getSettlementHash());
    }
}
