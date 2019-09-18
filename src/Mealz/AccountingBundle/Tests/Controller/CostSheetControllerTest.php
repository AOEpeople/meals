<?php

namespace Mealz\AccountingBundle\Tests\Controller;

use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;

/**
 * Class CostSheetControllerTest
 * @package Mealz\MealBundle\Tests\Controller
 */
class CostSheetControllerTest extends AbstractControllerTestCase
{
    /**
     * Prepares test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->createAdminClient();
        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers($this->client->getContainer()),
            new LoadTransactions()
        ]);
    }

    /**
     * Check if hashCode was written in database
     * @test
     */
    public function testHashWrittenInDatabase()
    {
        $profile = $this->getUserProfile();

        $this->assertNull($profile->getSettlementHash(), 'SettlementHash was set already');

        $this->client->request('GET', '/print/costsheet/settlement/request/alice');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertNotNull($profile->getSettlementHash(), 'SettlementHash was not set');
    }

    /**
     * Check if hashCode was removed from database
     * @test
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testHashRemoveFromDatabase()
    {
        $profile = $this->getUserProfile();
        $hash = '12345';
        $profile->setSettlementHash($hash);

        $em = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setAmount(mt_rand(10, 120) + 0.13);
        $transaction->setDate(new \DateTime());

        $em->persist($transaction);
        $em->flush();

        $transactionRepository = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $balanceBefore = $transactionRepository->getTotalAmount('alice');

        // Pre-action tests
        $this->assertGreaterThan(0, $balanceBefore);
        $this->assertNotNull($profile->getSettlementHash());

        // Trigger action
        $this->client->request('GET', '/print/costsheet/settlement/confirm/' . $hash);
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Check new balance
        $balanceAfter = $transactionRepository->getTotalAmount('alice');
        $this->assertEquals(0, $balanceAfter);
        $this->assertNull($profile->getSettlementHash());
    }
}
