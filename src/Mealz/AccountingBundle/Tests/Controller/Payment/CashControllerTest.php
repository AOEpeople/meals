<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller\Payment;

use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Cash controller test.
 *
 * @author Dragan Tomic <dragan.tomic@aoe.com>
 */
class CashControllerTest extends AbstractControllerTestCase
{
    /**
     * Prepares test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadCategories(),
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadParticipants(),
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadTransactions()
        ]);
    }

    /**
     * Checking if transaction history is correct for some user
     *
     * @test
     */
    public function checkTransactionHistory(): void
    {
        // Open home page
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $loginForm = $crawler->filterXPath('//form[@name="login-form"]')->form([
            '_username' => 'alice.meals',
            '_password' => 'Chee7ieRahqu'
        ]);
        $this->client->followRedirects();
        $crawler = $this->client->submit($loginForm, []);

        // read Current balance from header
        $currentBalance = $crawler->filterXPath('//div[@class="balance-text"]/a')->text();
        $currentBalance = (float)substr($currentBalance, 0, strpos($currentBalance, '€'));

        // click on the Balance link
        $balanceLink = $crawler->filterXPath('//div[@class="balance-text"]/a')->link();
        $crawler = $this->client->click($balanceLink);

        // you should be on Transaction history page
        $this->assertGreaterThan(0, $crawler->filterXPath('//div[contains(@class,"transaction-history")]')->count());

        // read balance 4 weeks ago
        $previousBalance = $crawler->filterXPath('//div[@class="last-account-balance"]/span')->text();
        $previousBalance = (float)substr($previousBalance, 0, strpos($previousBalance, '€'));

        // read all participations
        $participations = $crawler->filterXPath('//tr[contains(@class, "table-row") and contains(@class, "transaction-meal")]/td[3]')
            ->each(
                function ($node, $i) {
                    return $node->text();
                }
            );

        $participationAmount = 0;
        foreach ($participations as $participation) {
            $participationAmount += (float)trim(substr($participation, 1, strpos($participation, '€')));
        }

        $transactions = $crawler->filterXPath('//tr[contains(@class, "table-row") and contains(@class, "transaction-payment")]/td[3]')
            ->each(
                function ($node, $i) {
                    return $node->text();
                }
            );

        $transactionAmount = 0;
        foreach ($transactions as $transaction) {
            $transactionAmount += (float)trim(substr($transaction, 1, strpos($transaction, '€')));
        }

        $this->assertEquals($currentBalance, $previousBalance - $participationAmount + $transactionAmount);
    }
}
