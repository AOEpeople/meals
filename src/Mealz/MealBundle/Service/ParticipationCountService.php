<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use DateTime;

class ParticipationCountService
{
    public const PARTICIPATION_COUNT_KEY = 'countByMealIds';
    public const PARTICIPATION_TOTAL_COUNT_KEY = 'totalCountByDishSlugs';
    public const COUNT_KEY = 'count';
    public const LIMIT_KEY = 'limit';

    /**
     * @param array $participations     participations count array – Always use total counts array where combined dishes are considered
     * @param array $dishSlugs          array of dishslugs to check (for normal meals length is 1; for combined meals it is the booked combination)
     * @param float $participationCount Number of full or half participations that are expected, e.g., 1.5 for a dish that is part of combined meal and a normal meal.
     */
    public static function isParticipationPossibleForDishes(
        array $participations,
        array $dishSlugs,
        float $participationCount): bool
    {
        if (true === empty($participations)) {
            return false;
        }

        if (true === empty($dishSlugs)) {
            return false;
        }

        foreach ($dishSlugs as $dishSlug) {
            if (false === array_key_exists($dishSlug, $participations)) {
                return false;
            }

            if (false === self::isParticipationPossible($participations[$dishSlug], $participationCount)) {
                return false;
            }
        }

        return true;
    }

    private static function isParticipationPossible(array $participation, float $participationCount): bool
    {
        return 0.01 > $participation[self::LIMIT_KEY] // No no, no no no no, no no no no, no no there's no limit!
            || (0.0 < $participation[self::LIMIT_KEY]
                && $participation[self::LIMIT_KEY] >= ($participation[self::COUNT_KEY] + $participationCount));
    }

    public function getParticipationByDays(Week $week, bool $onlyFutureMeals = false): array
    {
        $now = new DateTime();
        $participationByDays = [];
        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            if (true === $onlyFutureMeals) {
                // skip past meals, provide updates just for future meals (locked included)
                if ($now >= $day->getDateTime()) {
                    continue;
                }
            }

            $participationByDay = $this->getParticipationByDay($day);
            if (false === empty($participationByDay)) {
                $participationByDays[$day->getDateTime()->format('Y-m-d')] = $participationByDay;
            }
        }

        return $participationByDays;
    }

    public function getParticipationByDay(Day $day): array
    {
        $participation = [];

        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()] = [];

            if (true === $meal->getDish()->isCombinedDish()) {
                self::setParticipationForCombinedDish($meal, $participation);
            } else {
                self::setParticipationForDish($meal, $participation);
            }

            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$meal->getDish()->getSlug()][self::COUNT_KEY] = $meal->getParticipants()->count();
            $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$meal->getDish()->getSlug()][self::LIMIT_KEY] = 0.0;
        }

        self::calculateLimits($day, $participation);

        return $participation;
    }

    public static function setParticipationForCombinedDish(Meal $meal, array &$participation): void
    {
        /** @var Participant $participant */
        foreach ($meal->getParticipants() as $participant) {
            /** @var Dish $dish */
            foreach ($participant->getCombinedDishes() as $dish) {
                if (false === array_key_exists(self::PARTICIPATION_TOTAL_COUNT_KEY, $participation)) {
                    $participation[self::PARTICIPATION_TOTAL_COUNT_KEY] = [];
                }
                if (false === array_key_exists($dish->getSlug(), $participation[self::PARTICIPATION_TOTAL_COUNT_KEY])) {
                    $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::COUNT_KEY] = 0.0;
                }

                $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::COUNT_KEY] += 0.5;

                if (false === array_key_exists($dish->getSlug(), $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()])) {
                    $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::COUNT_KEY] = 0.0;
                    $participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::LIMIT_KEY] = 0.0;
                }

                ++$participation[self::PARTICIPATION_COUNT_KEY][$meal->getId()][$dish->getSlug()][self::COUNT_KEY];
            }
        }
    }

    public static function setParticipationForDish(Meal $meal, array &$participation): void
    {
        if (false === array_key_exists(self::PARTICIPATION_TOTAL_COUNT_KEY, $participation)) {
            $participation[self::PARTICIPATION_TOTAL_COUNT_KEY] = [];
        }
        if (false === array_key_exists($meal->getDish()->getSlug(), $participation[self::PARTICIPATION_TOTAL_COUNT_KEY])) {
            $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::COUNT_KEY] = 0.0;
        }

        $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::COUNT_KEY] += $meal->getParticipants()->count();
        $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$meal->getDish()->getSlug()][self::LIMIT_KEY] = $meal->getParticipationLimit();
    }

    private static function calculateLimits(Day $day, array &$participation): void
    {
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if (true === $meal->getDish()->isCombinedDish()) {
                /** @var Participant $participant */
                foreach ($meal->getParticipants() as $participant) {
                    /** @var Dish $dish */
                    foreach ($participant->getCombinedDishes() as $dish) {
                        if (true === isset($participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::LIMIT_KEY])
                            && 0 < $participation[self::PARTICIPATION_TOTAL_COUNT_KEY][$dish->getSlug()][self::LIMIT_KEY]) {
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
