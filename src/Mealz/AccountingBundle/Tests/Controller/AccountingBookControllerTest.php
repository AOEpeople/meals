<?php

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use DateTime;
use DOMElement;
use DomNode;
use Exception;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\DomCrawler\Crawler;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Class AccountingBookControllerTest
 * @package Mealz\AccountingBundle\Tests\Controller
 */
class AccountingBookControllerTest extends AbstractControllerTestCase
{
    /**
     * prepare test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);

        $time = time();

        // Create profile for user1
        $user1FirstName = 'Max';
        $user1LastName  = 'Mustermann'.$time;
        $user1 = $this->createProfile($user1FirstName, $user1LastName);

        // Create profile for user2
        $user2FirstName = 'John';
        $user2LastName  = 'Doe'.$time;
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
     * Testing access to accounting book site for non-admins and admins
     */
    public function testAccessForAdminsOnly(): void
    {
        // test for admins
        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertFalse($node->count() > 0, "Accounting book NOT accessable by Admins(ROLE_KITCHEN_STAFF)");

        // test for no or non-admin user
        $this->loginAs(self::USER_STANDARD);
        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertFalse($node->count() > 0, "Accounting book accessable by Non-Admins");

        // test for fincance admins
        $this->loginAs(self::USER_FINANCE);
        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertTrue($node->count() > 0, "Accounting book NOT accessable by Admins(ROLE_KITCHEN_STAFF)");
    }

    /**
     * Test the headline of accounting book showing the range of the last month
     */
    public function testHeadlineShowingDateOfLastMonth(): void
    {
        // test for admins
        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//div[contains(@class,"accounting-book")]//h1[@class="headline"]');
        $this->assertTrue($node->count() > 0, 'There is no h1.headline element)');

        $headline = $node->first()->text();

        // Get first and last day of previous month
        $minDate = new DateTime('first day of previous month');
        $maxDate = new DateTime('last day of previous month');

        $firstDay = $minDate->format('d');
        $lastDay = $maxDate->format('d');
        $monthNumber = $minDate->format('m');
        $year = $minDate->format('Y');

        $regex = "/".preg_quote($firstDay)."\.".preg_quote($monthNumber)."\.(".preg_quote($year).")? *[-|bis|to] *".preg_quote($lastDay)."\.".preg_quote($monthNumber)."\.(".preg_quote($year).")?/i";
        $this->assertMatchesRegularExpression($regex, $headline, "The headline is not set properly");
    }

    /**
     * Test if sum of all transactions for the last month is displayed in a seperate row at the end of the
     * listed transactions.
     */
    public function testTotalAmountOfTransactionsDisplayedInSeperateRow(): void
    {
        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/accounting/book');
        $nodesAmount = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"amount") and not(contains(@class, "table-data-total"))]');
        $nodeTotal = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"table-data-total") and not(contains(@class, "amount"))]');

        $res = [];
        foreach ($nodesAmount as $value) {
            $tmpCrawler = new Crawler($value);
            $res[] = (float) $tmpCrawler->text();
        }

        $totalCalculated = (float) array_sum($res);
        $totalShown = $this->getFloatFromNode($nodeTotal->siblings()->getNode(0));

        $this->assertEquals($totalCalculated, $totalShown, 'Total amount of transactions inconsistent');
    }

    /**
     * Test users are orderd by lastname, firstname and listed this way:
     * lastname, firstname      amount
     */
    public function testDisplayUsersOrderedByLastnameAndFirstname(): void
    {
        // Get first and last day of previous month
        $minDate = new DateTime('first day of previous month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new DateTime('last day of previous month');
        $maxDate->setTime(23, 59, 59);

        // fetch infos for previous month from database.
        // These results are already ordered by lastname, firstname!!
        $transactionRepo = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $usersAndTheirTotals = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod($minDate, $maxDate);

        // fetch what is displayed in the accounting book table....
        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/accounting/book');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $nodesName = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"name")]');

        // now compare order and displayed syntax of results
        for ($i = 0; $i < $nodesName->count(); $i++) {
            $this->assertInstanceOf(DOMElement::class, $nodesName->getNode($i));
            $nameDisplayed = $nodesName->getNode($i)->textContent;
            $userInfo = current($usersAndTheirTotals);
            next($usersAndTheirTotals);
            $regex = "/".preg_quote($userInfo['name'])." *, *".preg_quote($userInfo['firstName'])."/i";
            $this->assertMatchesRegularExpression($regex, $nameDisplayed, 'Names are displayed incorrectly. Either sorting is wrong or the names are not displayed like it should be (name, firstname)');
        }
    }

    /**
     * Return a floatval from a node's textContent
     * @param DomNode $node
     * @return float
     */
    protected function getFloatFromNode(DomNode $node)
    {
        $res = $node->textContent;
        $res = str_replace(',', '', $res);

        return floatval($res);
    }

    /**
     * return a new crawler
     * @return Crawler
     */
    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';

        return new Crawler(json_decode($content), $uri);
    }

    /**
     * Tests if finance staff can access the transaction export page and admins and default users can not
     * @test
     */
    public function testAccessForFinanceOnly()
    {
        $this->loginAs(self::USER_FINANCE);

        $crawler = $this->client->request('GET', '/accounting/book/finance/list');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "Finances page not accessible by finance staff");

        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertTrue($node->count() > 0, "Accounting book table could not be rendered on the finances page");

        // Test if default users can access the finances page
        $this->loginAs(self::USER_STANDARD);
        $this->client->request('GET', '/accounting/book/finance/list');
        $this->assertFalse($this->client->getResponse()->isSuccessful(), "Finances page accessible by default users");

        // Test if admins can access the finances page
        $this->loginAs(self::USER_KITCHEN_STAFF);
        $this->client->request('GET', '/accounting/book/finance/list');
        $this->assertFalse($this->client->getResponse()->isSuccessful(), "Finances page accessible by administrators");
    }

    /**
     * Tests if the transactions are displayed correctly
     */
    public function testTransactionsListing(): void
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository('MealzAccountingBundle:Transaction')->clear();

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

        $crawler = $this->client->request('GET', '/accounting/book/finance/list/' . $dateFormatted . "&" . $dateFormatted);

        $date = $crawler->filterXPath('//*[@class="table-data date"]/text()')->getNode(0)->textContent;
        $this->assertEquals($transactionDate->format('d.m.Y'), trim($date), "Date displayed incorrectly");

        $name = $crawler->filterXPath('//*[@class="table-data name"]/text()')->getNode(0)->textContent;
        $this->assertEquals($profile->getFullName(), trim($name), "Name displayed incorrectly");

        $amount = $crawler->filterXPath('//*[@class="table-data amount"]/text()')->getNode(0)->textContent;
        $this->assertEquals("42.17", trim($amount), "Amount displayed incorrectly");
    }

    /**
     * Test if PayPal payments are shown on the finances page
     * @test
     * @throws Exception
     */
    public function testOnlyCashPaymentsListed()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository('MealzAccountingBundle:Transaction')->clear();

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

        $crawler = $this->client->request('GET', '/accounting/book/finance/list/' . $dateFormatted . "&" . $dateFormatted);

        $nodes = $crawler->filterXPath('//*[@class="table-data amount"]/text()');
        $this->assertEquals(0, $nodes->count(), "PayPal payment listed on finances page");
    }

    /**
     * Tests if the daily closing amount is calculated correctly
     * @test
     * @throws Exception
     */
    public function testDailyClosingCalculation()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository('MealzAccountingBundle:Transaction')->clear();

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

        $crawler = $this->client->request('GET', '/accounting/book/finance/list/' . $dateFormatted . "&" . $dateFormatted);

        $dailyClosing = $crawler->filterXPath('//*[@class="table-data daily-closing"]/text()')->getNode(0)->textContent;
        $this->assertEquals("100.00", trim($dailyClosing), "Daily closing calculated incorrectly");
    }
}
