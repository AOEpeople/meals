<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use DateTime;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Meal>
 * @template-extends Selectable<int, Meal>
 */
interface MealRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @param string $dish           Dish slug
     * @param array  $userSelections already selected meals for that day
     *
     * @return mixed|null
     */
    public function findOneByDateAndDish(DateTime $date, string $dish, array $userSelections = []): ?Meal;

    /**
     * @return Meal[]
     */
    public function findAllOn(DateTime $date): array;

    /**
     * @return Meal[]
     */
    public function findAllBetween(DateTime $startDate, DateTime $endDate): array;

    /**
     * Created for Test with Dish variations.
     *
     * @psalm-return list<array{id: int}>
     */
    public function getMealsOnADayWithVariationOptions(): array;

    /**
     * Returns all meals that are going to take place in the future.
     *
     * @return Meal[]
     */
    public function getFutureMeals(): array;

    /**
     * Returns all meals that are going to took place in the past.
     *
     * @return Meal[]
     */
    public function getOutdatedMeals(): array;

    /**
     * Returns all meals that are going to take place in the future but aren't available to join/leave anymore.
     *
     * @return Meal[]
     */
    public function getLockedMeals(): array;

    /**
     * Returns all Meals that are in the future and contain a specific dish.
     *
     * @return Meal[]
     */
    public function getFutureMealsForDish(Dish $dish): array;
}
