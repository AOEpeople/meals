<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service\Mocks;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Override;

final class TransactionRepositoryMock implements TransactionRepositoryInterface
{
    public ?Transaction $outputFind = null;
    public mixed $inputFind = null;
    public array $outputFindAll = [];
    public array $outputFindBy = [];
    public array $inputFindByCriteria = [];
    public ?array $inputFindByOrderBy = null;
    public ?int $inputFindByLimit = null;
    public ?int $inputFindByOffset = null;
    public ?Transaction $outputFindOneBy = null;
    public array $inputFindOneByCriteria = [];
    public string $outputGetClassName = self::class;
    public float $outputGetTotalAmount = 0.0;
    public string $inputGetTotalAmountUsername = '';
    public array $outputGetSuccessfulTransactionsOnDays = [];
    public ?DateTime $inputGetSuccessfulMinDate = null;
    public ?DateTime $inputGetSuccessfulMaxDate = null;
    public ?Profile $inputGetSuccessfulProfile = null;
    public array $outputFindUserDataAndTransactionAmountForGivenPeriod = [];
    public ?DateTime $inputFindUserDataMinDate = null;
    public ?DateTime $inputFindUserDataMaxDate = null;
    public ?Profile $inputFindUserDataProfile = null;
    public array $outputFindAllTransactionsInDateRange = [];
    public ?DateTime $inputFindAllRangeMinDate = null;
    public ?DateTime $inputFindAllRangeMaxDate = null;

    #[Override]
    public function find($id)
    {
        $this->inputFind = $id;

        return $this->outputFind;
    }

    #[Override]
    public function findAll()
    {
        return $this->outputFindAll;
    }

    #[Override]
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        $this->inputFindByCriteria = $criteria;
        $this->inputFindByOrderBy = $orderBy;
        $this->inputFindByLimit = $limit;
        $this->inputFindByOffset = $offset;

        return $this->outputFindBy;
    }

    #[Override]
    public function findOneBy(array $criteria)
    {
        $this->inputFindOneByCriteria = $criteria;

        return $this->outputFindOneBy;
    }

    #[Override]
    public function getClassName()
    {
        return $this->outputGetClassName;
    }

    #[Override]
    public function getTotalAmount(string $username): float
    {
        $this->inputGetTotalAmountUsername = $username;

        return $this->outputGetTotalAmount;
    }

    #[Override]
    public function getSuccessfulTransactionsOnDays(DateTime $minDate, DateTime $maxDate, Profile $profile): array
    {
        $this->inputGetSuccessfulMinDate = $minDate;
        $this->inputGetSuccessfulMaxDate = $maxDate;
        $this->inputGetSuccessfulProfile = $profile;

        return $this->outputGetSuccessfulTransactionsOnDays;
    }

    #[Override]
    public function findUserDataAndTransactionAmountForGivenPeriod(?DateTime $minDate = null, ?DateTime $maxDate = null, ?Profile $profile = null): array
    {
        $this->inputFindUserDataMinDate = $minDate;
        $this->inputFindUserDataMaxDate = $maxDate;
        $this->inputFindUserDataProfile = $profile;

        return $this->outputFindUserDataAndTransactionAmountForGivenPeriod;
    }

    #[Override]
    public function findAllTransactionsInDateRange(DateTime $minDate, DateTime $maxDate): array
    {
        $this->inputFindAllRangeMinDate = $minDate;
        $this->inputFindAllRangeMaxDate = $maxDate;

        return $this->outputFindAllTransactionsInDateRange;
    }
}
