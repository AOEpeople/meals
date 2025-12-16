<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week;

use App\Mealz\MealBundle\Controller\Exceptions\WeekAlreadyExistsException;
use DateTime;

interface WeekExistingValidatorInterface
{
    /**
     * @throws WeekAlreadyExistsException
     */
    public function check(DateTime $date): void;
}
