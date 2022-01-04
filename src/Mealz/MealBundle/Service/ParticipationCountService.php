<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;

class ParticipationCountService
{
    public static function getParticipationByDays(Week $week): array
    {
        $participationByDays = [];
        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $participationByDay = self::getParticipationByDay($day);
            if (!empty($participationByDay)) {
                $participationByDays[$day->getDateTime()->format('Y-m-d')] = $participationByDay;
            }
        }

        return $participationByDays;
    }

    public static function getParticipationByDay(Day $day): array
    {
        $countByMeal = [];
        $totalCountByDish = [];
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->getDish()->isCombinedDish()) {
                if (0 < $meal->getParticipants()->count()) {
                    $countByMeal[$meal->getId()] = [];
                }
                /** @var Participant $participant */
                foreach ($meal->getParticipants() as $participant) {
                    /** @var Dish $dish */
                    foreach ($participant->getCombinedDishes() as $dish) {
                        if (!array_key_exists($dish->getSlug(), $totalCountByDish)) {
                            $totalCountByDish[$dish->getSlug()]['count'] = 0.0;
                        }

                        $totalCountByDish[$dish->getSlug()]['count'] += 0.5;

                        if (!array_key_exists($dish->getSlug(), $countByMeal[$meal->getId()])) {
                            $countByMeal[$meal->getId()][$dish->getSlug()]['count'] = 0.0;
                            $countByMeal[$meal->getId()][$dish->getSlug()]['limit'] = 0.0;
                        }

                        ++$countByMeal[$meal->getId()][$dish->getSlug()]['count'];
                    }
                }
            } else {
                if (!array_key_exists($meal->getDish()->getSlug(), $totalCountByDish)) {
                    $totalCountByDish[$meal->getDish()->getSlug()]['count'] = 0.0;
                }

                $totalCountByDish[$meal->getDish()->getSlug()]['count'] += $meal->getParticipants()->count();
                $totalCountByDish[$meal->getDish()->getSlug()]['configuredLimit'] = $meal->getParticipationLimit();

                $countByMeal[$meal->getId()][$meal->getDish()->getSlug()]['count'] = $meal->getParticipants()->count();
                $countByMeal[$meal->getId()][$meal->getDish()->getSlug()]['limit'] = 0.0;
            }
        }

        self::calculateLimits($day, $totalCountByDish, $countByMeal);

        return $countByMeal;
    }

    private static function calculateLimits(Day $day, array $totalCountByDish, array &$countByMeal): void
    {
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->getDish()->isCombinedDish()) {
                /** @var Participant $participant */
                foreach ($meal->getParticipants() as $participant) {
                    /** @var Dish $dish */
                    foreach ($participant->getCombinedDishes() as $dish) {
                        if (0 < $totalCountByDish[$dish->getSlug()]['configuredLimit']) {
                            $countByMeal[$meal->getId()][$dish->getSlug()]['limit'] =
                                // limit and count are doubled, because calculate the limit with half portions (two half portions are one full portion)
                                self::calculateLimit(
                                    2 * $totalCountByDish[$dish->getSlug()]['configuredLimit'],
                                    2 * $totalCountByDish[$dish->getSlug()]['count'],
                                    $countByMeal[$meal->getId()][$dish->getSlug()]['count']
                                );
                        }
                    }
                }
            } else {
                if (0 < $totalCountByDish[$meal->getDish()->getSlug()]['configuredLimit']) {
                    $countByMeal[$meal->getId()][$meal->getDish()->getSlug()]['limit'] =
                        self::calculateLimit(
                            $totalCountByDish[$meal->getDish()->getSlug()]['configuredLimit'],
                            $totalCountByDish[$meal->getDish()->getSlug()]['count'],
                            $countByMeal[$meal->getId()][$meal->getDish()->getSlug()]['count']
                        );
                }
            }
        }
    }

    private static function calculateLimit(int $configuredLimit, float $totalCount, float $currentCount)
    {
        return $configuredLimit - ($totalCount - $currentCount);
    }
}
