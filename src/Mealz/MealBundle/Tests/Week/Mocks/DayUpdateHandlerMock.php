<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Week\Mocks;

use App\Mealz\MealBundle\Day\DayUpdateHandlerInterface;
use App\Mealz\MealBundle\Entity\Day;
use Override;

final class DayUpdateHandlerMock implements DayUpdateHandlerInterface
{
    public array $inputDayData;
    public array $inputDay;
    public array $inputCount;

    #[Override]
    public function handle(array $dayData, Day $day, int $count): void
    {
        $this->inputDayData[] = $dayData;
        $this->inputDay[] = $day;
        $this->inputCount[] = $count;
    }
}
