<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\Exception\PriceNotFoundException;

interface CombinedMealServiceInterface
{
    /**
     * @throws PriceNotFoundException
     */
    public function update(Week $week): void;
}
