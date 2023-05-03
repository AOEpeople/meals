<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Meal;
use DateTime;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

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
}
