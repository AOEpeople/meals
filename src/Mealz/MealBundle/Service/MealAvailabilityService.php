<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;

final class MealAvailabilityService
{
    private ParticipantRepositoryInterface $participantRepo;

    public function __construct(ParticipantRepositoryInterface $participantRepo)
    {
        $this->participantRepo = $participantRepo;
    }

    /**
     * @psalm-return array<int, bool|array{available: bool, availableWith: list<string>}> Key-value pair of meal-ID and corresponding availability
     */
    public function getByDay(Day $day): array
    {
        return $this->getAvailability($day->getMeals());
    }

    /**
     * @psalm-return bool|array{available: bool, availableWith: list<string>}
     */
    public function getByMeal(Meal $meal)
    {
        $availability = $this->getAvailability($meal->getDay()->getMeals());

        return $availability[$meal->getId()] ?? false;
    }

    public function isAvailable(Meal $meal): bool
    {
        $availability = $this->getByMeal($meal);

        return is_bool($availability) ? $availability : $availability['available'];
    }

    /**
     * @return array<int, bool|array{available: bool, availableWith: list<string>}> Key-value pair of meal-ID and corresponding availability
     */
    private function getAvailability(MealCollection $meals): array
    {
        $availability = [];

        foreach ($meals as $meal) {
            $mealId = $meal->getId();
            if (null !== $mealId) {
                $availability[$mealId] = $this->getMealAvailability($meal);
            }
        }

        return $availability;
    }

    /**
     * @return bool|array{available: bool, availableWith: list<string>}
     */
    private function getMealAvailability(Meal $meal)
    {
        if (false === $this->isMealAvailable($meal)) {
            return false;
        }

        if (true === $meal->isCombinedMeal()) {
            return $this->getCombinedMealAvailability($meal);
        }

        return true;
    }

    /**
     * @return (string[]|true)[]|bool
     *
     * @psalm-return array{available: true, availableWith: list<string>}|bool
     */
    private function getCombinedMealAvailability(Meal $meal): array|bool
    {
        $simpleMeals = array_filter(
            $meal->getDay()->getMeals()->toArray(),
            static fn (Meal $meal) => !$meal->isCombinedMeal()
        );

        $dishes = $this->getCombinedMealAvailableDishes($simpleMeals);
        $dishCount = count($dishes);

        // dish count equals simple meal count, i.e. all dishes all available; unrestricted availability
        if ($dishCount === count($simpleMeals)) {
            return true;
        }

        // dish count is greater than zero, but less than simple meal count; restricted availability
        if (($dishCount > 0) && ($dishCount < count($simpleMeals))) {
            return [
                'available' => true,
                'availableWith' => $dishes,
            ];
        }

        // no availability
        return false;
    }

    /**
     * @param Meal[] $meals Array of simple meals
     *
     * @psalm-return list<string> Slug of available dishes
     */
    private function getCombinedMealAvailableDishes(array $meals): array
    {
        $dishes = [];
        $dishAvailability = $this->getCombinedMealDishAvailability($meals);

        foreach ($dishAvailability as $dishSlug => $availability) {
            if (false === $availability) {
                return [];
            }

            if (true === $availability) {
                $dishes[] = [$dishSlug];
            } elseif (true === is_array($availability)) {  // $availability contains availability of dish variations
                $availableItems = array_filter($availability);  // filter out available variations
                if (0 === count($availableItems)) {
                    return [];
                }

                $dishes[] = array_keys($availableItems);
            }
        }

        return 0 < count($dishes) ? array_merge(...$dishes) : [];
    }

    /**
     * @param Meal[] $meals Array of simple meals
     *
     * @psalm-return array<string, bool|array<string, bool>>
     */
    private function getCombinedMealDishAvailability(array $meals): array
    {
        $availability = ['simpleDishes' => [], 'dishVariants' => []];

        foreach ($meals as $meal) {
            $dish = $meal->getDish();
            $parentDish = $dish->getParent();
            $dishAvailability = $this->isMealAvailable($meal, 0.5);

            if (null !== $parentDish) {
                $availability['dishVariants'][$parentDish->getSlug()][$dish->getSlug()] = $dishAvailability;
            } else {
                $availability['simpleDishes'][$dish->getSlug()] = $dishAvailability;
            }
        }

        return array_merge($availability['simpleDishes'], $availability['dishVariants']);
    }

    /**
     * Checks if a given meal is available for booking.
     *
     * @param float $factor Calculation factor. It's 1.0 for simple meal and 0.5 for combined meal.
     */
    private function isMealAvailable(Meal $meal, float $factor = 1.0): bool
    {
        // meal already occurred
        if (false === $meal->isOpen()) {
            return false;
        }

        // meal is locked and there are offers to overtake the meal
        if (true === $meal->isLocked()) {
            return 0 < $this->participantRepo->getOfferCountByMeal($meal);
        }

        // meal is yet to take place, it's not locked and has no participation limit
        $participationLimit = $meal->getParticipationLimit();
        if (0 === $participationLimit) {
            return true;
        }

        // meal is yet to take place, it's not locked and HAS PARTICIPATION LIMIT !!!
        $participantCount = $this->getTotalParticipantCount($meal);
        if (null === $participantCount) {
            return false;
        }

        return ($participantCount + $factor) <= (float) $participationLimit;
    }

    private function getParticipantCount(Meal $meal): ?int
    {
        $participantCount = (new ParticipationCountService())->getParticipationByDay($meal->getDay());

        $dishSlug = $meal->getDish()->getSlug();
        if (false === isset($participantCount['countByMealIds'][$meal->getId()][$dishSlug])) {
            return null;
        }

        return $participantCount['countByMealIds'][$meal->getId()][$dishSlug]['count'];
    }

    private function getTotalParticipantCount(Meal $meal): ?float
    {
        if (true === $meal->isCombinedMeal()) {
            return $this->getParticipantCount($meal);
        }

        $participantCount = (new ParticipationCountService())->getParticipationByDay($meal->getDay());

        $dishSlug = $meal->getDish()->getSlug();
        if (false === isset($participantCount['totalCountByDishSlugs'][$dishSlug])) {
            return null;
        }

        return $participantCount['totalCountByDishSlugs'][$dishSlug]['count'];
    }
}
