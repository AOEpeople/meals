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
    public const PARTICIPATION_COUNT_KEY = 'countByMealIds';
    public const PARTICIPATION_TOTAL_COUNT_KEY = 'totalCountByDishSlugs';
    public const COUNT_KEY = 'count';
    public const LIMIT_KEY = 'limit';

    public static function isParticipationPossibleForDishes(
        array $participations,
        array $dishSlugs,
        float $participationCount): bool
    {
        foreach ($dishSlugs as $dishSlug) {
            if (array_key_exists($dishSlug, $participations)
                && !self::isParticipationPossible($participations[$dishSlug], $participationCount)) {
                return false;
            }
        }

        return true;
    }

    private static function isParticipationPossible(array $participation, float $participationCount): bool
    {
        return 0.01 > $participation[self::LIMIT_KEY] || // No no, no no no no, no no no no, no no there's no limit!
            (0.0 < $participation[self::LIMIT_KEY]
                && $participation[self::LIMIT_KEY] >= ($participation[self::COUNT_KEY] + $participationCount));
    }

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
        $participation = [
            self::PARTICIPATION_COUNT_KEY => [],
            self::PARTICIPATION_TOTAL_COUNT_KEY => [],
        ];

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()] = [];
            if ($meal->getDish()->isCombinedDish()) {
                /** @var Participant $participant */
                foreach ($meal->getParticipants() as $participant) {
                    /** @var Dish $dish */
                    foreach ($participant->getCombinedDishes() as $dish) {
                        if (!array_key_exists($dish->getSlug(), $participation[self::PARTICIPATION_TOTAL_COUNT_KEY])) {
                            $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::COUNT_KEY] = 0.0;
                        }

                        $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::COUNT_KEY] += 0.5;

                        if (!array_key_exists($dish->getSlug(), $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()])) {
                            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::COUNT_KEY] = 0.0;
                            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::LIMIT_KEY] = 0.0;
                        }

                        ++$participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::COUNT_KEY];
                    }
                }
            } else {
                if (!array_key_exists($meal->getDish()->getSlug(), $participation[self::PARTICIPATION_TOTAL_COUNT_KEY])) {
                    $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::COUNT_KEY] = 0.0;
                }

                $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::COUNT_KEY] += $meal->getParticipants()->count();
                $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::LIMIT_KEY] = $meal->getParticipationLimit();
            }

            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$meal->getDish()->getSlug()][self::COUNT_KEY] = $meal->getParticipants()->count();
            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$meal->getDish()->getSlug()][self::LIMIT_KEY] = 0.0;
        }

        self::calculateLimits($day, $participation);

        return $participation;
    }

    private static function calculateLimits(Day $day, array &$participation): void
    {
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->getDish()->isCombinedDish()) {
                /** @var Participant $participant */
                foreach ($meal->getParticipants() as $participant) {
                    /** @var Dish $dish */
                    foreach ($participant->getCombinedDishes() as $dish) {
                        if (0 < $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::LIMIT_KEY]) {
                            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::LIMIT_KEY] =
                                // limit and count are doubled, because calculate the limit with half portions (two half portions are one full portion)
                                self::calculateLimit(
                                    2 * $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::LIMIT_KEY],
                                    2 * $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::COUNT_KEY],
                                    $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::COUNT_KEY]
                                );
                        }
                    }
                }
            } else {
                if (0 < $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::LIMIT_KEY]) {
                    $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$meal->getDish()->getSlug()][self::LIMIT_KEY] =
                        self::calculateLimit(
                            $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::LIMIT_KEY],
                            $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::COUNT_KEY],
                            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$meal->getDish()->getSlug()][self::COUNT_KEY]
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
