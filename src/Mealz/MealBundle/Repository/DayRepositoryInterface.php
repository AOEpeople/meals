<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Day;
use DateTime;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Day>
 */
interface DayRepositoryInterface extends ObjectRepository
{
    public function getCurrentDay(): ?Day;

    public function getDayByDate(DateTime $dateTime): ?Day;

    /**
     * Get all active meal days between $startDate and $endDate.
     *
     * An active meal day is the day that is not disabled, and has an open meal, i.e. meal that is open for participation.
     *
     * @return Day[]
     */
    public function findAllActive(DateTime $startDate, DateTime $endDate): array;
}
