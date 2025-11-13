<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Repository;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\Repository\BaseRepository;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Override;

/**
 * @extends BaseRepository<int, Transaction>
 */
final class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    /**
     * Get total amount of transactions. Prevent unnecessary ORM mapping.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Override]
    public function getTotalAmount(string $userId): float
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('SUM(t.amount) AS amount');
        $queryBuilder->andWhere('t.profile = :user');
        $queryBuilder->setParameter('user', $userId, Types::STRING);

        return (float) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Get all successful transactions for period and profile.
     *
     * @param DateTime $minDate Start Date
     * @param DateTime $maxDate End Date
     * @param Profile  $profile User profile
     *
     * @return Transaction[]
     */
    #[Override]
    public function getSuccessfulTransactionsOnDays(DateTime $minDate, DateTime $maxDate, Profile $profile): array
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('t');

        $minDate = clone $minDate;
        $minDate->setTime(0, 0, 0);

        $queryBuilder->andWhere('t.date >= :minDate');
        $queryBuilder->andWhere('t.date <= :maxDate');
        $queryBuilder->setParameter('minDate', $minDate, Types::DATETIME_MUTABLE);
        $queryBuilder->setParameter('maxDate', $maxDate, Types::DATETIME_MUTABLE);

        $queryBuilder->andWhere('t.profile = :profile');
        $queryBuilder->setParameter('profile', $profile->getUsername(), Types::STRING);

        $queryBuilder->orderBy('t.date', 'ASC');

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Get first name, last name and amount of transactions in the given time per user.
     *
     * @param DateTime|null $minDate Start Date
     * @param DateTime|null $maxDate End Date
     * @param Profile|null  $profile User profile
     *
     * @psalm-return array<string, array{firstName: string, name: string, amount: string, paymethod: string|null}>
     */
    #[Override]
    public function findUserDataAndTransactionAmountForGivenPeriod(
        ?DateTime $minDate = null,
        ?DateTime $maxDate = null,
        ?Profile $profile = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('p.username, p.firstName, p.name, MIN(t.paymethod) as paymethod, SUM(t.amount) AS amount');
        $queryBuilder->leftJoin('t.profile', 'p');

        if ($minDate instanceof DateTime) {
            $queryBuilder->andWhere('t.date >= :minDate');
            $queryBuilder->setParameter('minDate', $minDate, Types::DATETIME_MUTABLE);
        }

        if ($maxDate instanceof DateTime) {
            $queryBuilder->andWhere('t.date <= :maxDate');
            $queryBuilder->setParameter('maxDate', $maxDate, Types::DATETIME_MUTABLE);
        }

        if ($profile instanceof Profile) {
            $queryBuilder->andWhere('p.username = :username');
            $queryBuilder->setParameter('username', $profile->getUsername(), Types::STRING);
        }

        $queryBuilder->groupBy('p.username');
        $queryBuilder->orderBy('p.name, p.firstName');
        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        $result = [];

        foreach ($queryResult as $item) {
            $result[$item['username']] = [
                'firstName' => $item['firstName'],
                'name' => $item['name'],
                'amount' => $item['amount'],
                'paymethod' => $item['paymethod'],
            ];
        }

        return $result;
    }

    /**
     * Returns all transactions that were made between the given dates.
     *
     * @param DateTime $minDate Start Date
     * @param DateTime $maxDate End Date
     *
     * @psalm-return array<array-key, non-empty-list<array{amount: float, date: string, firstName: string, name: string}>>
     */
    #[Override]
    public function findAllTransactionsInDateRange(DateTime $minDate, DateTime $maxDate): array
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('t.date');

        $minDate = clone $minDate;
        $minDate->setTime(0, 0, 0);
        $maxDate = clone $maxDate;
        $maxDate->setTime(23, 59, 59);

        $queryBuilder->andWhere('t.date >= :minDate');
        $queryBuilder->andWhere('t.date <= :maxDate');
        $queryBuilder->setParameter('minDate', $minDate, Types::DATETIME_MUTABLE);
        $queryBuilder->setParameter('maxDate', $maxDate, Types::DATETIME_MUTABLE);

        $queryBuilder->orderBy('t.date', 'ASC');

        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        $result = [];
        foreach ($queryResult as $item) {
            if (false === array_key_exists($item['date']->format('Y-m-d'), $result)) {
                $transactions = $this->getAllTransactionsOnDay($item['date']);
                if (false === empty($transactions)) {
                    $result[$item['date']->format('Y-m-d')] = $transactions;
                }
            }
        }

        return $result;
    }

    /**
     * Helper function for findAllTransactionsInDateRange().
     *
     * @psalm-return list<array{firstName: string, name: string, amount: float, date: string}>
     */
    private function getAllTransactionsOnDay(DateTime $day): array
    {
        // Get all dates where transactions were made
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('t.amount, t.date, p.firstName, p.name');
        $queryBuilder->leftJoin('t.profile', 'p');

        $minDate = clone $day;
        $minDate->setTime(0, 0, 0);
        $maxDate = clone $day;
        $maxDate->setTime(23, 59, 59);

        $queryBuilder->andWhere('t.date >= :minDate');
        $queryBuilder->andWhere('t.date <= :maxDate');
        $queryBuilder->andWhere('t.paymethod IS NULL');
        $queryBuilder->setParameter('minDate', $minDate, Types::DATETIME_MUTABLE);
        $queryBuilder->setParameter('maxDate', $maxDate, Types::DATETIME_MUTABLE);

        $queryBuilder->orderBy('t.date', 'ASC');

        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        $result = [];
        foreach ($queryResult as $item) {
            // TODO: should not be floatval
            $result[] = [
                'amount' => floatval($item['amount']),
                'date' => $item['date']->format('d.m.Y'),
                'firstName' => $item['firstName'],
                'name' => $item['name'],
            ];
        }

        return $result;
    }
}
