<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Event\Subscriber\Mocks;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\CombinedMealServiceInterface;
use App\Mealz\MealBundle\Service\Exception\PriceNotFoundException;

final class CombinedMealServiceMock implements CombinedMealServiceInterface
{
    public Week $inputWeek;
    public ?PriceNotFoundException $throwPriceNotFoundException = null;

    /**
     * @throws PriceNotFoundException
     */
    public function update(Week $week): void
    {
        $this->inputWeek = $week;
        if ($this->throwPriceNotFoundException instanceof PriceNotFoundException) {
            throw $this->throwPriceNotFoundException;
        }
    }
}