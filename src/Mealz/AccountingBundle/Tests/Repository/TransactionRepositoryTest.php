<?php

namespace App\Mealz\AccountingBundle\Tests\Repository;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Repository\TransactionRepository;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCombinations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadSlots;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class TransactionRepositoryTest.
 */
class TransactionRepositoryTest extends AbstractDatabaseTestCase
{
    private TransactionRepository $transactionRepo;

    protected string $locale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionRepo = self::getContainer()->get(TransactionRepositoryInterface::class);
        $this->locale = 'en';

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadSlots(),
            new LoadCombinations(self::getContainer()->get(EventDispatcherInterface::class)),
            new LoadParticipants(),
        ]);
    }

    /**
     * Test if method findUserDataAndTransactionAmountForGivenPeriod() returns the right summed up amounts for transactions of the LAST month
     * Check if transactions of the last month are cumulated correctly.
     *
     * - create several temporary transactions for a TEST user (spread over this month, last month and the month before last month)
     * - call transactionRepository->findUserDataAndTransactionAmountForGivenPeriod() with parameters for last month and TEST user
     * - compare returned sum of transactions with the sum of the temporary transactions sum which are added to the last month
     */
    public function testTransactionsSummedUpByLastMonth(): void
    {
        // create several temporary transactions for a test user
        $tempTransactions = $this->createTemporaryTransactions();

        // Get first and last day of previous month
        $minDate = new DateTime('first day of previous month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new DateTime('last day of previous month');
        $maxDate->setTime(23, 59, 59);

        // make temporary transactions are available
        $this->assertNotEmpty($tempTransactions);
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
        $epsilon = 0.00001;
        $this->assertTrue(abs($usersTotalAmount - $assumedTotalAmount) < $epsilon);
    }

    /**
     * Returns a random DateTime object of this, last or penultimate Month
     * You can set $month to either 'this', 'last' or 'penultimate'.
     *
     * @param string $month
     *
     * @throws Exception
     */
    private function getRandomDateTime($month = 'this'): DateTime
    {
        $dateTime = new DateTime();
        $subDays = ($dateTime->format('d') > 15) ? 36 : 20;

        switch (strtolower($month)) {
            case 'last':
                $dateTime->sub(new DateInterval('P' . $subDays . 'D'));
                break;
            case 'penultimate':
                $dateTime->sub(new DateInterval('P1M' . $subDays . 'D'));
                break;
            default:
                break;
        }

        return $dateTime;
    }

    /**
     * Sum up the amounts of transactions with a date laying in the last month.
     *
     * @param $transactionsArray array holding Transaction objects
     */
    private function getAssumedTotalAmountForTransactionsFromLastMonth(array $transactionsArray): float
    {
        $result = 0;
        $transactions = array_filter($transactionsArray, ['self', 'isTransactionFromLastMonth']);
        foreach ($transactions as $transaction) {
            $result += $transaction->getAmount();
        }

        return floatval($result);
    }

    /**
     * Create and persist a bunch of transactions and return them in an array.
     *
     * @return Transaction[] of transactions
     *
     * @throws Exception
     *
     * @psalm-return non-empty-list<Transaction>
     */
    private function createTemporaryTransactions(): array
    {
        // create a test user...
        $testUser = $this->createProfile();

        // create 12 transactions for several periods of time and assign it to test user
        $transactions = [];
        for ($i = 1; $i < 12; ++$i) {
            $transaction = new Transaction();
            $transaction->setProfile($testUser);
            $transaction->setAmount(random_int(10, 120) + 0.13);
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
     * Filter transactions from an array that date is NOT within the last month.
     *
     * @param $item Transaction object
     *
     * @see getAssumedTotalAmountForTransactionsFromLastMonth()
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isTransactionFromLastMonth($item): bool
    {
        $firstDayLastMonth = new DateTime('first day of last month');
        $month = $firstDayLastMonth->format('n');
        if ($item instanceof Transaction) {
            return $item->getDate()->format('n') === $month;
        }

        return false;
    }
}
