<?php

namespace Mealz\AccountingBundle\Tests\Controller;

use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use Mealz\AccountingBundle\Controller\CostSheetController;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use Mealz\UserBundle\Entity\Profile;

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

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures([
        new LoadUsers($this->client->getContainer()),
        new LoadTransactions()
        ]);
    }

    /**
     * check that hash is written in database
     * @test
     */
    public function testHashWritteInDatabase()
    {
        // get Instanzes from class
        $costSheetController = new CostSheetController();
        $profile = $this->getUserProfile();
        $em = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setAmount(mt_rand(-10, -120) + 0.13);
        $transaction->setDate(new \DateTime('yesterday'));

        $em->transactional(function ($em) use ($transaction) {
            $em->persist($transaction);
            $em->flush();
        });

        // do pre write Tests
        $this->assertNull($profile->getSettlementHash('alice'));

        $costSheetController->sendSettlementRequestAction('alice');

        // do post write Tests
        $this->assertNotNull($profile->getSettlementHash('alice'));
    }

    /**
     * check that hash is removed from database
     * @test
    */
    /*public function testHashRemoveFromDatabase()
    {
        // get Instanzes from class
        $costSheetController = new CostSheetController();
        $profile = $this->getUserProfile();

        $balanceBefore = $costSheetController->get('mealz_accounting.wallet')->getBalance($profile);

        //do pre remove Tests
        $this->assertGreaterThan(0, $balanceBefore);
        $this->assertNotNull($profile->getSettlementHash('alice'));

        // removeHash and get new Balance
        $costSheetController->confirmSettlementAction($profile->getSettlementHash('alice'));
        $balanceAfter = $costSheetController->get('mealz_accounting.wallet')->getBalance($profile);

        // conform Settelement and remove Hash
        $this->assertEquals(0, $balanceAfter);
        $this->assertNull($profile->getSettlementHash('alice'));
    }*/
}
