<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Account;

use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\UserBundle\Entity\Profile;

final readonly class DefaultAccountOrderLockedBalanceChecker implements AccountOrderLockedBalanceChecker
{
    public function __construct(
        private ApiService $apiSrv,
        private int $debtLimit
    ) {}

    public function check(Profile $profile): bool
    {
        $dateFrom = new \DateTime()->setTimestamp(0);
        $dateTo = new \DateTime();
        $currentBalance = $this->apiSrv->getFullTransactionHistory($dateFrom, $dateTo, $profile)[0] ?? 0.0;
        if ($currentBalance >= $this->debtLimit) {
            return false;
        }

        return true;
    }
}