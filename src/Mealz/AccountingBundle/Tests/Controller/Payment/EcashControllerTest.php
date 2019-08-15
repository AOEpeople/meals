<?php

namespace Mealz\AccountingBundle\Tests\Controller\Payment;

use Symfony\Component\HttpFoundation\Request;
use Mealz\AccountingBundle\Controller\Payment\EcashController;
use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

class EcashControllerTest extends AbstractControllerTestCase
{

    /**
     * Set up the testing environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures(
            array(
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
            )
        );
    }

    /**
     * Check if form and PayPal button is rendered correctly
     */
    public function testFormRendering()
    {
        $userProfile = $this->getUserProfile();

        // Open home page
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Login
        $loginForm = $crawler->filterXPath('//form[@name="login-form"]')
            ->form(
                array(
                    '_username' => $userProfile->getUsername(),
                    '_password' => $userProfile->getUsername()
                )
            );
        $this->client->followRedirects();
        $crawler = $this->client->submit($loginForm);

        // Click on the balance link
        $balanceLink = $crawler->filterXPath('//div[@class="balance-text"]/a')->link();
        $crawler = $this->client->click($balanceLink);

        // Client should be on transaction history page
        $this->assertGreaterThan(0, $crawler->filterXPath('//div[contains(@class,"transaction-history")]')->count(), 'Transaction history page not found');

        // Check if "add funds" button exists
        $this->assertGreaterThan(0, $crawler->filterXPath('//*[@id="ecash"]')->count(), 'Add funds button not found');
    }

    /**
     * Test PayPal response handling and database persistence
     */
    public function testPaymentFormHandlingAction()
    {
        
    }
}
