<?php

namespace Mealz\AccountingBundle\Tests\Controller\Payment;

use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Cash controller test.
 *
 * @author Dragan Tomic <dragan.tomic@aoe.com>
 */
class CashControllerTest extends \Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase
{
    /**
     * Prepares test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures(
            [
                new LoadCategories(),
                new LoadWeeks(),
                new LoadDays(),
                new LoadDishes(),
                new LoadDishVariations(),
                new LoadMeals(),
                new LoadParticipants(),
                new LoadRoles(),
                new LoadUsers($this->client->getContainer()),
                new LoadTransactions()
            ]
        );
    }

    /**
     * Checking if transaction history is correct for some user
     *
     * @test
     *
     * @return void
     */
    public function checkTransactionHistory()
    {
        $userProfile = $this->getUserProfile();

        // Open home page
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $loginForm = $crawler->filterXPath('//form[@name="login-form"]')
            ->form(
                [
                    '_username' => $userProfile->getUsername(),
                    '_password' => $userProfile->getUsername()
                ]
            );
        $this->client->followRedirects();
        $crawler = $this->client->submit($loginForm);

        // read Current balance from header
        $currentBalance = $crawler->filterXPath('//div[@class="balance-text"]/a')->text();
        $currentBalance = floatval(substr($currentBalance, 0, strpos($currentBalance, '€')));

        // click on the Balance link
        $balanceLink = $crawler->filterXPath('//div[@class="balance-text"]/a')->link();
        $crawler = $this->client->click($balanceLink);

        // you should be on Transaction history page
        $this->assertGreaterThan(0, $crawler->filterXPath('//div[contains(@class,"transaction-history")]')->count());

        // read balance 4 weeks ago
        $previousBalance = $crawler->filterXPath('//div[@class="last-account-balance"]/span')->text();
        $previousBalance = floatval(substr($previousBalance, 0, strpos($previousBalance, '€')));

        // read all participations
        $participations = $crawler->filterXPath('//tr[contains(@class, "table-row") and contains(@class, "transaction-meal")]/td[3]')
            ->each(
                function ($node, $i) {
                    return $node->text();
                }
            );

        $participationAmount = 0;
        foreach ($participations as $participation) {
            $participationAmount += floatval(trim(substr($participation, 1, strpos($participation, '€'))));
        }

        $transactions = $crawler->filterXPath('//tr[contains(@class, "table-row") and contains(@class, "transaction-payment")]/td[3]')
            ->each(
                function ($node, $i) {
                    return $node->text();
                }
            );

        $transactionAmount = 0;
        foreach ($transactions as $transaction) {
            $transactionAmount += floatval(trim(substr($transaction, 1, strpos($transaction, '€'))));
        }

        $this->assertEquals($currentBalance, $previousBalance - $participationAmount + $transactionAmount);
    }

}
