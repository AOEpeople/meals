<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Week\Model\WeekNotification;

interface WeekPersisterInterface
{
    public function persist(Week $week, WeekNotification $weekNotification): void;
}
