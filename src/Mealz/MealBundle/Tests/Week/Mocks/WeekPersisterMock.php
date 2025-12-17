<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Week\Mocks;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Week\Model\WeekId;
use App\Mealz\MealBundle\Week\Model\WeekNotification;
use App\Mealz\MealBundle\Week\WeekPersisterInterface;
use Override;

final class WeekPersisterMock implements WeekPersisterInterface
{
    public Week $inputWeek;
    public WeekNotification $inputWeekNotification;
    public WeekId $outputWeekId;

    #[Override]
    public function persist(Week $week, WeekNotification $weekNotification): WeekId
    {
        $this->inputWeek = $week;
        $this->inputWeekNotification = $weekNotification;

        return $this->outputWeekId;
    }
}
