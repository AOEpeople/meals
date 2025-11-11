<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Account;

use App\Mealz\UserBundle\Entity\Profile;

interface AccountOrderLockedBalanceChecker
{
    public function check(Profile $profile): bool;
}