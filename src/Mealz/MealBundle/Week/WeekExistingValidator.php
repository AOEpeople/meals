<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Week;

use App\Mealz\MealBundle\Controller\Exceptions\WeekAlreadyExistsException;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use DateTime;
use Override;

final readonly class WeekExistingValidator implements WeekExistingValidatorInterface
{
    public function __construct(
        private WeekRepositoryInterface $weekRepository,
    ) {
    }

    /**
     * @throws WeekAlreadyExistsException
     */
    #[Override]
    public function check(DateTime $date): void
    {
        $week = $this->weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            throw new WeekAlreadyExistsException('Week already exists.');
        }
    }
}
