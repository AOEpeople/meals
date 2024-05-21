<?php

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountingBookControllerTest.
 */
class AccountingBookControllerTest extends AbstractControllerTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);

        $time = time();

        // Create profile for user1
        $user1FirstName = 'Max';
        $user1LastName = 'Mustermann'.$time;
        $user1 = $this->createProfile($user1FirstName, $user1LastName);

        // Create profile for user2
        $user2FirstName = 'John';
        $user2LastName = 'Doe'.$time;
        $user2 = $this->createProfile($user2FirstName, $user2LastName);

        $this->persistAndFlushAll([$user1, $user2]);

        // Create transactions for users if they're persisted
        if (($this->getUserProfile($user1FirstName.'.'.$user1LastName) instanceof Profile) === true) {
            $this->createTransactions($user1, 10.50, new DateTime('first day of previous month'));
        }

        if (($this->getUserProfile($user2FirstName.'.'.$user2LastName) instanceof Profile) === true) {
            $this->createTransactions($user2, 11.50, new DateTime('first day of previous month'));
        }
    }

    /**
     * Tests if admin staff can access the cash register other users can not.
     */
    public function testAccessForAdminOnly(): void
    {
        $this->loginAs(self::USER_FINANCE);
        $this->client->request('GET', '/api/accounting/book');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode(), 'Cash register page accessible by finance staff');

        // Test if default users can access the cash register page
        $this->loginAs(self::USER_STANDARD);
        $this->client->request('GET', '/api/accounting/book');
        $this->assertFalse($this->client->getResponse()->isSuccessful(), 'Cash register page accessible by default users');

        // Test if admins can access the cash register page
        $this->loginAs(self::USER_KITCHEN_STAFF);
        $this->client->request('GET', '/api/accounting/book');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Cash register page not accessible by administrators');
    }

    /**
     * Tests if the transactions are displayed correctly.
     */
    public function testTransactionsListing(): void
    {
        $this->markTestSkipped('Frontend Test');
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository(Transaction::class)->clear();

        $profile = $this->getUserProfile(self::USER_STANDARD);
        $transactionDate = new DateTime('today');
        $dateFormatted = $transactionDate->format('Y-m-d');

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setDate($transactionDate);
        $transaction->setAmount(42.17);

        $entityManager->persist($transaction);
        $entityManager->flush();

        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/api/accounting/book/finance/list/'.$dateFormatted.'&'.$dateFormatted);

        $date = $crawler->filterXPath('//*[@class="table-data date"]/text()')->getNode(0)->textContent;
        $this->assertEquals($transactionDate->format('d.m.Y'), trim($date), 'Date displayed incorrectly');

        $name = $crawler->filterXPath('//*[@class="table-data name"]/text()')->getNode(0)->textContent;
        $this->assertEquals($profile->getFullName(), trim($name), 'Name displayed incorrectly');

        $amount = $crawler->filterXPath('//*[@class="table-data amount"]/text()')->getNode(0)->textContent;
        $this->assertEquals('42.17', trim($amount), 'Amount displayed incorrectly');
    }

    /**
     * Test if PayPal payments are shown on the finances page.
     *
     * @throws Exception
     */
    public function testOnlyCashPaymentsListed(): void
    {
        $this->markTestSkipped('Frontend Test');
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository(Transaction::class)->clear();

        $profile = $this->getUserProfile(self::USER_STANDARD);
        $transactionDate = new DateTime('today');
        $dateFormatted = $transactionDate->format('Y-m-d');

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setDate($transactionDate);
        $transaction->setAmount(42.17);
        $transaction->setPaymethod(0);

        $entityManager->persist($transaction);
        $entityManager->flush();

        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/api/accounting/book/finance/list/'.$dateFormatted.'&'.$dateFormatted);

        $nodes = $crawler->filterXPath('//*[@class="table-data amount"]/text()');
        $this->assertEquals(0, $nodes->count(), 'PayPal payment listed on finances page');
    }

    /**
     * Tests if the daily closing amount is calculated correctly.
     *
     * @throws Exception
     */
    public function testDailyClosingCalculation(): void
    {
        $this->markTestSkipped('Frontend Test');
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository(Transaction::class)->clear();

        $profile = $this->getUserProfile(self::USER_STANDARD);
        $transactionDate = new DateTime('today');
        $dateFormatted = $transactionDate->format('Y-m-d');

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setDate($transactionDate->setTime(12, 00, 00));
        $transaction->setAmount(42.17);

        $entityManager->persist($transaction);

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setDate($transactionDate->setTime(13, 00, 00));
        $transaction->setAmount(57.83);

        $entityManager->persist($transaction);
        $entityManager->flush();

        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/api/accounting/book/finance/list/'.$dateFormatted.'&'.$dateFormatted);

        $dailyClosing = $crawler->filterXPath('//*[@class="table-data daily-closing"]/text()')->getNode(0)->textContent;
        $this->assertEquals('100.00', trim($dailyClosing), 'Daily closing calculated incorrectly');
    }
}
