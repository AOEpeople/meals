<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Week\Mocks;

use App\Mealz\MealBundle\Week\WeekExistingValidatorInterface;
use DateTime;
use Override;

final class WeekExistingValidatorMock implements WeekExistingValidatorInterface
{
    public DateTime $inputDate;

    #[Override]
    public function validate(DateTime $date): void
    {
        $this->inputDate = $date;
    }
}
