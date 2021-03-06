<?php

namespace Mealz\AccountingBundle\Tests\Repository;

use DateInterval;
use DateTime;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

/**
 * Class TransactionRepositoryTest
 * @package Mealz\AccountingBundle\Tests\Repository
 */
class TransactionRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var  transactionRepo */
    protected $transactionRepo;

    /**
     * @var String
     */
    protected $locale;

    /**
     * prepare test environment
     */
    public function setUp()
    {
        parent::setUp();
        $this->transactionRepo = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $this->locale = 'en';

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadMeals(),
            new LoadParticipants(),
        ]);
    }

    /**
     * Test if method findUserDataAndTransactionAmountForGivenPeriod() returns the right summed up amounts for transactions of the LAST month
     * Check if transactions of the last month are cumulated correctly
     *
     * - create several temporary transactions for a TEST user (spread over this month, last month and the month before last month)
     * - call transactionRepository->findUserDataAndTransactionAmountForGivenPeriod() with parameters for last month and TEST user
     * - compare returned sum of transactions with the sum of the temporary transactions sum which are added to the last month
     * @test
     */
    public function testTransactionsSummedUpByLastMonth()
    {
        // create several temporary transactions for a testuser
        $tempTransactions = $this->createTemporaryTransactions();

        // Get first and last day of previous month
        $minDate = new DateTime('first day of previous month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new DateTime('last day of previous month');
        $maxDate->setTime(23, 59, 59);

        // make temporary transactions are available
        $this->assertTrue(count($tempTransactions) > 0);
        $firstTransaction = array_values($tempTransactions)[0];
        $this->assertTrue($firstTransaction instanceof Transaction);

        // now fetch results from db and get the summed up amount for a certain user...
        $userData = $this->transactionRepo->findUserDataAndTransactionAmountForGivenPeriod(
            $minDate,
            $maxDate,
            $firstTransaction->getProfile()
        );
        $usersTotalAmount = floatval($userData[$firstTransaction->getProfile()->getUsername()]['amount']);

        // calculate sum of amount for tempTransactions
        $assumedTotalAmount = $this->getAssumedTotalAmountForTransactionsFromLastMonth($tempTransactions);

        // compare both amounts
        $this->assertEquals($usersTotalAmount, $assumedTotalAmount);
    }

    /**
     * Returns a random DateTime object of this, last or penultimate Month
     * You can set $month to either 'this', 'last' or 'penultimate'
     *
     * @param string $month
     * @return DateTime
     */
    public function getRandomDateTime($month = 'this')
    {
        $dateTime = new DateTime();
        $subDays = ($dateTime->format("d") > 15) ? 36 : 20;

        switch (strtolower($month)) {
            case 'last':
                $dateTime->sub(new DateInterval('P'.$subDays.'D'));
                break;
            case 'penultimate':
                $dateTime->sub(new DateInterval('P1M'.$subDays.'D'));
                break;
            default:
                break;
        }

        return $dateTime;
    }

    /**
     * Sum up the amounts of transactions with a date laying in the last month
     *
     * @param $transactionsArray    An array holding Transaction objects
     * @return float
     */
    protected function getAssumedTotalAmountForTransactionsFromLastMonth($transactionsArray)
    {
        $result = 0;
        $transactions = array_filter($transactionsArray, ['self', 'isTransactionFromLastMonth']);
        foreach ($transactions as $transaction) {
            $result += $transaction->getAmount();
        }

        return floatval($result);
    }

    /**
     * Create and persist a bunch of transactions and return them in an array
     *
     * @return array    Array of transactions
     */
    protected function createTemporaryTransactions()
    {
        // create a testuser...
        $testUser = $this->createProfile();

        // create 12 transactions for several periods of time and assign it to testuser
        $transactions = array();
        for ($i = 1; $i < 12; $i++) {
            $transaction = new Transaction();
            $transaction->setProfile($testUser);
            $transaction->setAmount(mt_rand(10, 120) + 0.13);
            // $period is to gather 2 transactions for current month, 2 for penultimate one and 8 for last month
            $period = ($i <= 2) ? 'this' : 'last';
            $period = ($i > 10) ? 'penultimate' : $period;
            $transaction->setDate($this->getRandomDateTime($period));
            $transactions[] = $transaction;
        }
        // persist the transactions
        $this->persistAndFlushAll($transactions);

        return $transactions;
    }

    /**
     * HELPERFUNCTION
     * Filter transactions from an array that date is NOT within the last month
     *
     * @param $item     Transaction object
     * @return bool
     *
     * @see getAssumedTotalAmountForTransactionsFromLastMonth()
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isTransactionFromLastMonth($item)
    {
        $firstDayLastMonth = new DateTime('first day of last month');
        $month = $firstDayLastMonth->format('n');
        if ($item instanceof Transaction) {
            return ($item->getDate()->format('n') == $month);
        }

        return false;
    }
}
