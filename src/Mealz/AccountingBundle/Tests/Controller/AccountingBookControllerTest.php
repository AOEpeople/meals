<?php

namespace Mealz\AccountinglBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Mealz\AccountingBundle\Controller\AccountingBookController;
use Mealz\AccountingBundle\Entity\TransactionRepository;

use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountingBookControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->createAdminClient();

        $this->clearAllTables();
        $this->loadFixtures([
            #new LoadWeeks(),
            #new LoadDays(),
            #new LoadCategories(),
            #new LoadDishes(),
            new LoadMeals(),
            new LoadUsers($this->client->getContainer()),
            #new LoadParticipants(),
            new LoadTransactions,
        ]);
    }

    /**
     * Testing access to accounting book site for non-admins and admins
     */
    public function testAccessForAdminsOnly()
    {
            // test for admins
        $crawler = $this->client->request('GET','/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertTrue($node->count() > 0, "Accounting book NOT accessable by Admins(ROLE_KITCHEN_STAFF)");

            // test for no or non-admin user
        $this->createDefaultClient();
        $crawler = $this->client->request('GET','/accounting/book');
        $node = $crawler->filterXPath('//table[@id="accounting-book-table"]');
        $this->assertFalse($node->count() > 0, "Accounting book accessable by Non-Admins");
    }

    /**
     * Test the headline of accounting book showing the range of the last month
     */
    public function testHeadlineShowingDateOfLastMonth()
    {
            // test for admins
        $crawler = $this->client->request('GET','/accounting/book');
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
        $this->assertRegExp($regex,$headline,"The headline is not set properly");
    }

    /**
     * Test if sum of all transactions for the last month is displayed in a seperate row at the end of the
     * listed transactions.
     */
    public function testTotalAmountOfTransactionsDisplayedInSeperateRow()
    {
        $res = array();
        $totalCalculated = $totalShown = 0.00;

        $crawler = $this->client->request('GET','/accounting/book');
        $nodesAmount = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"amount")]');
        $nodeTotal = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"table-data-total")]');

        foreach($nodesAmount as $key=>$value){
            $tmpCrawler = new Crawler($value);
            $res[] = floatval($tmpCrawler->text());
        }
        $totalCalculated = floatval(array_sum($res));
        $totalShown = $this->getFloatFromNode($nodeTotal->siblings()->getNode(0));
        $this->assertEquals($totalCalculated,$totalShown,"Total amount of transactions inconsistent");
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
        $usersAndTheirTotals = $transactionRepository->findTotalAmountOfTransactionsPerUser($minDate,$maxDate);

            // fetch what is displayed in the accounting book table....
        $crawler = $this->client->request('GET','/accounting/book');
        $nodesName = $crawler->filterXPath('//table[@id="accounting-book-table"]//td[contains(@class,"name")]');
        $this->assertTrue($nodesName->count() >= 10,"To few of testing records available.");

            // now compare order and displayed syntax of results
        for($i=0;$i<10;$i++){
            $this->assertTrue($nodesName->getNode($i) instanceof \DOMElement);
            $nameDisplayed = $nodesName->getNode($i)->textContent;
            $userInfo = current($usersAndTheirTotals);
            next($usersAndTheirTotals);
            $regex = "/".preg_quote($userInfo['name'])." *, *".preg_quote($userInfo['firstName'])."/i";
            $this->assertRegExp($regex,$nameDisplayed, "Names are displayed incorrectly. Either sorting is wrong or the names are not displayed like it should be (name, firstname)");
        }
    }

###############################################

    /**
     * Return a floatval from a node's textContent
     *
     * @param \DomNode $node
     */
    protected function getFloatFromNode(\DomNode $node)
    {
        $res = $node->textContent;
        $res = str_replace(',','',$res);
        return floatval($res);
    }

    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';
        return new Crawler(json_decode($content), $uri);
    }

}