<?php

namespace Mealz\AccountingBundle\Tests\Repository;

use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Mealz\MealBundle\EventListener\LocalisationListener;
use Symfony\Component\Validator\Constraints\DateTime;


class TransactionRepositoryTest extends AbstractDatabaseTestCase
{
    /** @var  TransactionRepository */
    protected $transactionRepository;

    protected $locale;

    public function setUp()
    {
        parent::setUp();
        $this->transactionRepository = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $this->locale = 'en';

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadMeals(),
            new LoadParticipants()
        ]);
    }

    /**
     * Test if method findTotalAmountOfTransactionsPerUser() returns the right summed up amounts for transactions of the LAST month
     * Check if transactions of the last month are cumulated correctly
     *
     * - create several temporary transactions for a TEST user (spread over this month, last month and the month before last month)
     * - call transactionRepository->findTotalAmountOfTransactionsPerUser() with parameters for last month and TEST user
     * - compare returned sum of transactions with the sum of the temporary transactions sum which are added to the last month
     */
    public function testTransactionsSummedUpByLastMonth()
    {
            // create several temporary transactions for a testuser
        $tempTransactions = $this->createTemporaryTransactions();

            // Get first and last day of previous month
        $minDate = new \DateTime('first day of previous month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new \DateTime('last day of previous month');
        $maxDate->setTime(23, 59, 59);

            // make temporary transactions are available
        $this->assertTrue(count($tempTransactions)>0);
        $t1 = array_values($tempTransactions)[0];
        $this->assertTrue($t1 instanceof Transaction);

            // now fetch results from db and get the summed up amount for a certain user...
        $fetchedTransactions = $this->transactionRepository->findTotalAmountOfTransactionsPerUser($minDate, $maxDate, $t1->getProfile());
        $fetchedTransactionsTotalAmount = $fetchedTransactions[$t1->getProfile()->getUsername()]['amount'];
        $fetchedTransactionsTotalAmount = floatval($fetchedTransactionsTotalAmount);

            // calculate sum of amount for tempTransactions
        $assumedTotalAmount = $this->getAssumedTotalAmountForTransactionsFromLastMonth($tempTransactions);

            // compare both amounts
        $this->assertEquals($fetchedTransactionsTotalAmount,$assumedTotalAmount);
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
        $ra = array_filter($transactionsArray,['self','getTransactionsFromLastMonth']);
        foreach($ra as $transaction) {
            $result += $transaction->getAmount();
        }
        return floatval($result);
    }

    /**
     * HELPERFUNCTION
     * Filter transactions from an array that date is NOT within the last month
     *
     * @param $item     Transaction object
     * @return bool
     */
    private function getTransactionsFromLastMonth($item)
    {
        $m = new \DateTime('first day of last month');
        $m = $m->format('n');
        if ($item instanceof Transaction) {
            return ($item->getDate()->format('n') == $m);
        }
        return false;
    }

    /**
     * Create and persist a bunch of transactions and return them in an array
     *
     * @return array    Array of transactions
     */
    protected function createTemporaryTransactions()
    {
            // create a testuser...
        $tu = $this->createProfile();

            // create 12 transactions for several periods of time and assign it to testuser
        $transactions = array();
        for ($i=1;$i < 12; $i++)
        {
            $m = new \DateTime();
            $tt = new Transaction();
            $tt->setProfile($tu);
            $tt->setAmount(mt_rand(10,120)+ 0.13);
                // $period is to gather 2 transactions for current month, 2 for penultimate one and 8 for last month
            $period = ($i<=2) ? 'this' : 'last';
            $period = ($i>10) ? 'penultimate' : $period;
            $tt->setDate($this->getRandomDateTime($period));
            $transactions[] = $tt;
        }

            // persist the transactions
        $this->persistAndFlushAll($transactions);

        return $transactions;
    }


    /**
     * Returns a random DateTime object of this, last or penultimate Month
     * You can set $month to either 'this', 'last' or 'penultimate'
     *
     * @param string $month
     * @return \DateTime
     */
    public function getRandomDateTime($month = 'this')
    {
        $dt = new \DateTime();
        $subDays = ($dt->format("d") > 15) ? 36 : 20;

        switch(strtolower($month)) {
            case 'last':
                $dt->sub(new \DateInterval('P'.$subDays.'D'));
                break;
            case 'penultimate':
                $dt->sub(new \DateInterval('P1M'.$subDays.'D'));
                break;
            default:
                break;
        }

        return $dt;
    }

}