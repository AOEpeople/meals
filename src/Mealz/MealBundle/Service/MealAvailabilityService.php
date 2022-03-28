<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\ParticipantRepository;

class MealAvailabilityService
{
    private array $availabilityCache = [];
    private array $participantCount = [];

    private ParticipantRepository $participantRepo;

    public function __construct(ParticipantRepository $participantRepo)
    {
        $this->participantRepo = $participantRepo;
    }

    /**
     * @psalm-return array<int, bool|array{available: bool, availableWith: list<int>}> Key-value pair of meal-ID and corresponding availability
     */
    public function getByDay(Day $day): array
    {
        $dayId = $day->getId();

        if (isset($this->availabilityCache[$dayId])) {
            return $this->availabilityCache[$dayId];
        }

        $this->availabilityCache[$dayId] = $this->getAvailability($day->getMeals());

        return $this->availabilityCache[$dayId];
    }

    /**
     * @psalm-return bool|array{available: bool, availableWith: list<int>}
     */
    public function getByMeal(Meal $meal)
    {
        $mealId = $meal->getId();
        $dayId = $meal->getDay()->getId();

        if (!isset($this->availabilityCache[$dayId][$mealId])) {
            $this->availabilityCache[$dayId][$mealId] = $this->getMealAvailability($meal);
        }

        return $this->availabilityCache[$dayId][$mealId];
    }

    public function isAvailable(Meal $meal): bool
    {
        $availability = $this->getByMeal($meal);

        return is_bool($availability) ? $availability : $availability['available'];
    }

    /**
     * @return array<int, bool|array{available: bool, availableWith: list<int>}> Key-value pair of meal-ID and corresponding availability
     */
    private function getAvailability(MealCollection $meals): array
    {
        $availability = [];

        foreach ($meals as $meal) {
            $availability[$meal->getId()] = $this->getMealAvailability($meal);
        }

        return $availability;
    }

    /**
     * @return bool|array{available: bool, availableWith: list<int>}
     */
    private function getMealAvailability(Meal $meal)
    {
        if ($meal->isCombinedMeal()) {
            $simpleMeals = array_filter(
                $meal->getDay()->getMeals()->toArray(),
                static fn (Meal $meal) => !$meal->isCombinedMeal()
            );
            $availability = $this->getCombinedMealAvailability($simpleMeals);
        } else {
            $availability = $this->isMealAvailable($meal);
        }

        return $availability;
    }

    /**
     * @param Meal[] $meals Array of simple meals
     */
    private function getCombinedMealAvailability(array $meals)
    {
        $dishes = $this->getCombinedMealAvailableDishes($meals);
        $dishCount = count($dishes);

        // dish count equals simple meal count, i.e. all dishes all available; unrestricted availability
        if ($dishCount === count($meals)) {
            return true;
        }

        // dish count is greater than zero, but less than simple meal count; restricted availability
        if (($dishCount > 0) && ($dishCount < count($meals))) {
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
            } elseif (is_array($availability)) {  // $availability contains availability of dish variations
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
        $availability = [];

        foreach ($meals as $meal) {
            $dish = $meal->getDish();
            $parentDish = $dish->getParent();
            $dishAvailability = $this->isMealAvailable($meal, 0.5);

            if (null !== $parentDish) {
                $availability[$parentDish->getSlug()][$dish->getSlug()] = $dishAvailability;
            } else {
                $availability[$dish->getSlug()] = $dishAvailability;
            }
        }

        return $availability;
    }

    /**
     * Checks if a given meal is available for booking.
     *
     * @param float $factor Calculation factor. It's 1.0 for simple meal and 0.5 for combined meal.
     */
    private function isMealAvailable(Meal $meal, float $factor = 1.0): bool
    {
        // meal already occurred
        if (!$meal->isOpen()) {
            return false;
        }

        // meal is locked and there are offers to overtake the meal
        if ($meal->isLocked()) {
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
        $mealDate = $meal->getDateTime()->format('Ymd');
        if (!isset($this->participantCount[$mealDate])) {
            $this->participantCount[$mealDate] = ParticipationCountService::getParticipationByDay($meal->getDay());
        }

        $dishSlug = $meal->getDish()->getSlug();
        if (!isset($this->participantCount[$mealDate]['countByMealIds'][$meal->getId()][$dishSlug])) {
            return null;
        }

        return $this->participantCount[$mealDate]['countByMealIds'][$meal->getId()][$dishSlug]['count'];
    }

    private function getTotalParticipantCount(Meal $meal): ?float
    {
        if ($meal->isCombinedMeal()) {
            return $this->getParticipantCount($meal);
        }

        $mealDate = $meal->getDateTime()->format('Ymd');
        if (!isset($this->participantCount[$mealDate])) {
            $this->participantCount[$mealDate] = ParticipationCountService::getParticipationByDay($meal->getDay());
        }

        $dishSlug = $meal->getDish()->getSlug();
        if (!isset($this->participantCount[$mealDate]['totalCountByDishSlugs'][$dishSlug])) {
            return null;
        }

        return $this->participantCount[$mealDate]['totalCountByDishSlugs'][$dishSlug]['count'];
    }
}
