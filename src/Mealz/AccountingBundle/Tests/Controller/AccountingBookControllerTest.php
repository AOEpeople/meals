<?php

namespace Mealz\AccountinglBundle\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;

use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Class AccountingBookControllerTest
 * @package Mealz\AccountinglBundle\Tests\Controller
 */
class AccountingBookControllerTest extends AbstractControllerTestCase
{
    /**
     * prepare test environment
     */
    public function setUp()
    {
        $this->createAdminClient();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadMeals(),
            new LoadUsers($this->client->getContainer()),
        ]);

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
        if ($this->getUserProfile($user1FirstName.'.'.$user1LastName)) {
            $this->createTransactions($user1, 10.50, new \DateTime('first day of previous month'));
        }

        if ($this->getUserProfile($user2FirstName.'.'.$user2LastName)) {
            $this->createTransactions($user2, 11.50, new \DateTime('first day of previous month'));
        }
    }

    /**
     * Testing access to accounting book site for non-admins and admins
     */
    public function testAccessForAdminsOnly()
    {
        // test for admins
        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertTrue($node->count() > 0, "Accounting book NOT accessable by Admins(ROLE_KITCHEN_STAFF)");

        // test for no or non-admin user
        $this->createDefaultClient();
        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertFalse($node->count() > 0, "Accounting book accessable by Non-Admins");
    }

    /**
     * Test the headline of accounting book showing the range of the last month
     */
    public function testHeadlineShowingDateOfLastMonth()
    {
        // test for admins
        $crawler = $this->client->request('GET', '/accounting/book');
        $node = $crawler->filterXPath('//div[contains(@class,"accounting-book")]//h1[@class="headline"]');
        $this->assertTrue($node->count() > 0, "There is no h1.headline element)");

        $headline = $node->first()->text();

        // Get first and last day of previous month
        $minDate = new \DateTime('first day of previous month');
        $maxDate = new \DateTime('last day of previous month');

        $firstDay = $minDate->format('d');
        $lastDay = $maxDate->format('d');
        $monthNumber = $minDate->format('m');
        $year = $minDate->format('Y');

        $regex = "/".preg_quote($firstDay)."\.".preg_quote($monthNumber)."\.(".preg_quote($year).")? *[-|bis|to] *".preg_quote($lastDay)."\.".preg_quote($monthNumber)."\.(".preg_quote($year).")?/i";
        $this->assertRegExp($regex, $headline, "The headline is not set properly");
    }

    /**
     * Test if sum of all transactions for the last month is displayed in a seperate row at the end of the
     * listed transactions.
     */
    public function testTotalAmountOfTransactionsDisplayedInSeperateRow()
    {
        $res = array();
        $totalCalculated = $totalShown = 0.00;

        $crawler = $this->client->request('GET', '/accounting/book');
        $nodesAmount = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"amount")]');
        $nodeTotal = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"table-data-total")]');

        foreach ($nodesAmount as $key => $value) {
            $tmpCrawler = new Crawler($value);
            $res[] = floatval($tmpCrawler->text());
        }
        $totalCalculated = floatval(array_sum($res));
        $totalShown = $this->getFloatFromNode($nodeTotal->siblings()->getNode(0));
        $this->assertEquals($totalCalculated, $totalShown, "Total amount of transactions inconsistent");
    }

    /**
     * Test users are orderd by lastname, firstname and listed this way:
     * lastname, firstname      amount
     */
    public function testDisplayUsersOrderedByLastnameAndFirstname()
    {
        // Get first and last day of previous month
        $minDate = new \DateTime('first day of previous month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new \DateTime('last day of previous month');
        $maxDate->setTime(23, 59, 59);

        // fetch infos for previous month from database.
        // These results are already ordered by lastname, firstname!!
        $transactionRepository = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $usersAndTheirTotals = $transactionRepository->findUserDataAndTransactionAmountForGivenPeriod($minDate, $maxDate);

        // fetch what is displayed in the accounting book table....
        $crawler = $this->client->request('GET', '/accounting/book');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $nodesName = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"name")]');

        // now compare order and displayed syntax of results
        for ($i = 0; $i < $nodesName->count(); $i++) {
            $this->assertTrue($nodesName->getNode($i) instanceof \DOMElement);
            $nameDisplayed = $nodesName->getNode($i)->textContent;
            $userInfo = current($usersAndTheirTotals);
            next($usersAndTheirTotals);
            $regex = "/".preg_quote($userInfo['name'])." *, *".preg_quote($userInfo['firstName'])."/i";
            $this->assertRegExp($regex, $nameDisplayed, "Names are displayed incorrectly. Either sorting is wrong or the names are not displayed like it should be (name, firstname)");
        }
    }

    /**
     * Return a floatval from a node's textContent
     * @param \DomNode $node
     * @return float
     */
    protected function getFloatFromNode(\DomNode $node)
    {
        $res = $node->textContent;
        $res = str_replace(',', '', $res);

        return floatval($res);
    }

    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';

        return new Crawler(json_decode($content), $uri);
    }
}