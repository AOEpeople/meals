<?php

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Week;
use DateTime;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Week>
 */
interface WeekRepositoryInterface extends ObjectRepository
{
    public function getCurrentWeek(array $options = []): ?Week;

    public function getNextWeek(?DateTime $date = null, array $options = []): ?Week;

    public function getWeeksMealCount(Week $week): int;

    public function findWeekByDate(DateTime $date, array $options = []): ?Week;
}
