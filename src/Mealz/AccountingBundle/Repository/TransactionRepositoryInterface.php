<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Repository;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Persistence\ObjectRepository;

interface TransactionRepositoryInterface extends ObjectRepository
{
    /**
     * Get total amount of transactions. Prevent unnecessary ORM mapping.
     */
    public function getTotalAmount(string $username): float;

    /**
     * Get all successful transactions for period and profile.
     *
     * @param DateTime $minDate Start Date
     * @param DateTime $maxDate End Date
     * @param Profile  $profile User profile
     *
     * @return Transaction[]
     */
    public function getSuccessfulTransactionsOnDays(DateTime $minDate, DateTime $maxDate, Profile $profile): array;

    /**
     * Get first name, last name and amount of transactions in the given time per user.
     *
     * @param DateTime|null $minDate Start Date
     * @param DateTime|null $maxDate End Date
     * @param Profile|null  $profile User profile
     *
     * @psalm-return array<string, array{firstName: string, name: string, amount: float, paymethod: string|null}>
     */
    public function findUserDataAndTransactionAmountForGivenPeriod(
        ?DateTime $minDate = null,
        ?DateTime $maxDate = null,
        ?Profile $profile = null
    ): array;

    /**
     * Get all transactions that were made between the given dates.
     *
     * @param DateTime $minDate Start Date
     * @param DateTime $maxDate End Date
     *
     * @psalm-return array<array-key, non-empty-list<array{amount: float, date: string, firstName: string, name: string}>>
     */
    public function findAllTransactionsInDateRange(DateTime $minDate, DateTime $maxDate): array;
}
