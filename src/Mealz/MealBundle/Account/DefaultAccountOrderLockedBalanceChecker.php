<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Account;

use App\Mealz\MealBundle\Account\Model\Clock;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Override;

final readonly class DefaultAccountOrderLockedBalanceChecker implements AccountOrderLockedBalanceChecker
{
    public function __construct(
        private ApiService $apiSrv,
        private int        $debtLimit,
        private Clock      $clock
    ) {
    }

    #[Override]
    public function check(Profile $profile): bool
    {
        $dateFrom = new DateTime()->setTimestamp(0);
        $now = $this->clock->now();
        $dateTo = DateTime::createFromImmutable($now);
        $currentBalance = $this->apiSrv->getFullTransactionHistory($dateFrom, $dateTo, $profile)[0] ?? 0.0;
        if ($currentBalance >= $this->debtLimit) {
            return false;
        }

        return true;
    }
}
