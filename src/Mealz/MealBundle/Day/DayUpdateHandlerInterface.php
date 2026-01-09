<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Day;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Helper\Exceptions\PriceNotFoundException;

interface DayUpdateHandlerInterface
{
    /**
     * @throws PriceNotFoundException
     */
    public function handle(array $dayData, Day $day, int $count): void;
}
