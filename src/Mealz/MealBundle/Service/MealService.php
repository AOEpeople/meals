<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use DateTime;

class MealService
{
    /**
     * Checks if there exists a meal on $date that one can join or accept.
     */
    public function containsOpenMeal(array $meals): bool
    {
        $now = new DateTime();
        foreach ($meals as $meal) {
            if ($meal->getDateTime() > $now) {
                return true;
            }
        }

        return false;
    }
}
